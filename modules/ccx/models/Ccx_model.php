<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_model extends App_Model
{
    private static $schemaEnsured = false;

    private const TEMPLATE_TYPE_SMART   = 'smart';
    private const TEMPLATE_TYPE_SQL     = 'sql';
    private const TEMPLATE_TYPE_DYNAMIC = 'dynamic';
    private const SQL_MAX_ROWS          = 500;

    /**
     * Aggregates supported when defining template columns.
     *
     * @var array<string,string>
     */
    private $aggregateOptions = [
        'SUM'   => 'ccx_aggregate_sum',
        'COUNT' => 'ccx_aggregate_count',
        'AVG'   => 'ccx_aggregate_avg',
        'MIN'   => 'ccx_aggregate_min',
        'MAX'   => 'ccx_aggregate_max',
        'VALUE' => 'ccx_aggregate_value',
    ];

    /**
     * Cached column outputs indexed by identifier (e.g. column:1, column:invoice_id).
     *
     * @var array<string,mixed>
     */
    private $columnContext = [];

    /**
     * Tracks how many times a slug has been used to ensure uniqueness.
     *
     * @var array<string,int>
     */
    private $columnSlugCounts = [];

    /**
     * Runtime filter values supplied from controllers.
     *
     * @var array<string,mixed>
     */
    private $runtimeFilters = [];

    /**
     * Last database error encountered while building preview queries.
     *
     * @var string|null
     */
    private $lastQueryError = null;

    public function set_runtime_filters(array $filters): void
    {
        $this->runtimeFilters = $filters;
    }

    public function get_aggregate_options(): array
    {
        $resolved = [];
        foreach ($this->aggregateOptions as $key => $langKey) {
            $resolved[$key] = ccx_lang($langKey, ucfirst(strtolower($key)));
        }

        return $resolved;
    }

    private function normalize_template_type(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return in_array($type, [self::TEMPLATE_TYPE_SMART, self::TEMPLATE_TYPE_SQL, self::TEMPLATE_TYPE_DYNAMIC], true)
            ? $type
            : self::TEMPLATE_TYPE_SMART;
    }

    public function is_smart_template(array $template): bool
    {
        return $this->normalize_template_type($template['type'] ?? null) === self::TEMPLATE_TYPE_SMART;
    }

    public function is_sql_template(array $template): bool
    {
        return $this->normalize_template_type($template['type'] ?? null) === self::TEMPLATE_TYPE_SQL;
    }

    public function is_dynamic_template(array $template): bool
    {
        return $this->normalize_template_type($template['type'] ?? null) === self::TEMPLATE_TYPE_DYNAMIC;
    }

    public function get_templates(): array
    {
        $this->ensure_schema();

        $templates = $this->db
            ->select('t.*, COUNT(c.id) AS column_count', false)
            ->from(db_prefix() . 'ccx_report_templates AS t')
            ->join(db_prefix() . 'ccx_report_template_columns AS c', 'c.template_id = t.id', 'left')
            ->group_by('t.id')
            ->order_by('t.name', 'ASC')
            ->get()
            ->result_array();

        return $templates ?? [];
    }

    public function ensure_template_table(array $template, array $columns)
    {
        $this->ensure_schema();

        if (! $this->is_smart_template($template)) {
            return null;
        }

        if (! class_exists('App_table')) {
            return null;
        }

        if (empty($columns)) {
            return null;
        }

        $tableId = 'ccx-template-' . (int) $template['id'];
        $table   = App_table::find($tableId);

        if ($table) {
            return $table;
        }

        $table = App_table::new($tableId, 'ccx-template');
        $table->setDbTableName('ccx_template_' . (int) $template['id']);

        $rules = [];
        foreach ($columns as $index => $column) {
            $aggregate = strtoupper($column['aggregate_function'] ?? 'SUM');
            $label = trim((string) ($column['label'] ?? ''));
            if ($label === '') {
                $label = $this->default_column_label($aggregate, $column['column_name'] ?? '');
            }

            $ruleType = in_array($aggregate, ['SUM', 'COUNT', 'AVG', 'MIN', 'MAX', 'FORMULA'], true)
                ? 'NumberRule'
                : 'TextRule';

            $rules[] = App_table_filter::new('col_' . $index, $ruleType)
                ->label($label)
                ->raw(static function () {
                    return '';
                });
        }

        $table->setRules($rules);

        $table->outputUsing(function () use ($template, $columns) {
            $this->ci->load->model('ccx/ccx_model');

            $options = [
                'draw'    => (int) ($this->ci->input->post('draw') ?? 0),
                'search'  => trim($this->ci->input->post('search')['value'] ?? ''),
                'filters' => $this->ci->input->post('filters') ?? [],
                'start'   => (int) ($this->ci->input->post('start') ?? 0),
                'length'  => $this->ci->input->post('length') !== null ? (int) $this->ci->input->post('length') : null,
                'runtime_filters' => $this->ci->input->post('runtime_filters') ?? [],
                'date_from'       => $this->ci->input->post('date_from'),
                'date_to'         => $this->ci->input->post('date_to'),
            ];

            return $this->ci->ccx_model->get_template_table_data((int) $template['id'], $options, $columns);
        });

        App_table::register($table);

        return $table;
    }

    public function get_template_table_data(int $templateId, array $options = [], ?array $columnDefinitions = null): array
    {
        $this->ensure_schema();

        $template = $this->get_template($templateId);
        if (! $template || ! $this->is_smart_template($template)) {
            $draw = (int) ($options['draw'] ?? 0);

            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $draw   = (int) ($options['draw'] ?? 0);
        $search = strtolower(trim((string) ($options['search'] ?? '')));
        $filters = $options['filters'] ?? [];

        $columns = $columnDefinitions ?? $this->get_template_columns($templateId);

        $runtimeFiltersOption = $options['runtime_filters'] ?? [];
        if (! is_array($runtimeFiltersOption)) {
            $runtimeFiltersOption = [];
        }

        $this->set_runtime_filters($runtimeFiltersOption);

        try {
            $runtimeColumnConditions = $this->build_runtime_column_conditions($columns, $options);

            if ($this->has_group_columns($columns)) {
                return $this->build_grouped_dataset_response($columns, $options, $runtimeColumnConditions);
            }

            if ($this->is_value_dataset($columns)) {
                return $this->build_value_dataset_response($columns, $options, $runtimeColumnConditions);
            }

            $valueDatasetMeta = $this->extract_value_dataset_metadata($columns);
            if ($valueDatasetMeta !== null) {
                return $this->build_value_dataset_with_aggregates($columns, $valueDatasetMeta, $options, $runtimeColumnConditions);
            }

            $report = $this->evaluate_template($templateId, null, [
                'column_conditions' => $runtimeColumnConditions,
            ]);
            $evaluatedColumns  = $report['columns'] ?? [];

            $totalRecords = empty($evaluatedColumns) ? 0 : 1;
            $aaData       = [];
            $filtered     = $totalRecords;

            if (! empty($evaluatedColumns)) {
                $row           = [];
                $matchesSearch = ($search === '');
                $rawRow        = [];

                foreach ($evaluatedColumns as $index => $column) {
                    $alias            = 'col_' . $index;
                    $formatted        = (string) ($column['value'] ?? '');
                    $raw              = $column['raw_value'] ?? $column['value'] ?? null;
                    $row[$alias]      = html_escape($formatted);
                    $rawRow[$alias]   = $raw;

                    if ($search !== '' && stripos($formatted, $search) !== false) {
                        $matchesSearch = true;
                    }
                }

                if ($matchesSearch && $this->passes_template_filters($rawRow, $evaluatedColumns, $filters)) {
                    $aaData[] = array_values($row);
                } else {
                    $filtered = 0;
                }
            } else {
                $filtered = 0;
            }

            return [
                'draw'                 => $draw,
                'recordsTotal'         => $totalRecords,
                'recordsFiltered'      => $filtered,
                'iTotalRecords'        => $totalRecords,
                'iTotalDisplayRecords' => $filtered,
                'aaData'               => $aaData,
            ];
        } finally {
            $this->set_runtime_filters([]);
        }
    }

    private function has_group_columns(array $columns): bool
    {
        $hasGroup  = false;
        $hasMetric = false;

        foreach ($columns as $column) {
            $role = $this->sanitize_column_role($column['role'] ?? 'metric');
            if ($role === 'group') {
                $hasGroup = true;
            } else {
                $hasMetric = true;
            }

            if ($hasGroup && $hasMetric) {
                return true;
            }
        }

        return $hasGroup && $hasMetric;
    }

    private function is_value_dataset(array $columns): bool
    {
        if (empty($columns)) {
            return false;
        }

        $resolvedTable = null;

        foreach ($columns as $column) {
            if (($column['mode'] ?? 'simple') !== 'simple') {
                return false;
            }

            $aggregate = $this->normalize_aggregate($column['aggregate_function'] ?? '');
            if ($aggregate !== 'VALUE') {
                return false;
            }

            $tableName  = $this->resolve_table_name($column['table_name'] ?? '');
            $columnName = trim((string) ($column['column_name'] ?? ''));

            if (! $tableName || $columnName === '') {
                return false;
            }

            if ($resolvedTable === null) {
                $resolvedTable = $tableName;
            } elseif ($resolvedTable !== $tableName) {
                return false;
            }

            if (! $this->resolve_dataset_identifier($column, $tableName)) {
                return false;
            }
        }

        return true;
    }

    private function build_value_dataset_response(array $columns, array $options, array $runtimeColumnConditions = []): array
    {
        $draw    = (int) ($options['draw'] ?? 0);
        $search  = strtolower(trim((string) ($options['search'] ?? '')));
        $filters = $options['filters'] ?? [];
        $start   = isset($options['start']) ? max(0, (int) $options['start']) : 0;
        $length  = isset($options['length']) && $options['length'] !== null ? (int) $options['length'] : null;

        if ($length !== null && $length < 0) {
            $length = null;
        }

        $tableName = $this->resolve_table_name($columns[0]['table_name'] ?? '');
        if (! $tableName) {
            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $builder = $this->db->from($tableName);

        $allJoins = [];
        foreach ($columns as $column) {
            if (! empty($column['joins']) && is_array($column['joins'])) {
                foreach ($column['joins'] as $join) {
                    $key = md5(json_encode($join));
                    $allJoins[$key] = $join;
                }
            }
        }

        if (! empty($allJoins)) {
            $this->apply_joins($builder, array_values($allJoins));
        }

        foreach ($columns as $index => $column) {
            $identifier = $this->resolve_dataset_identifier($column, $tableName);

            if (! $identifier) {
                return [
                    'draw'                 => $draw,
                    'recordsTotal'         => 0,
                    'recordsFiltered'      => 0,
                    'iTotalRecords'        => 0,
                    'iTotalDisplayRecords' => 0,
                    'aaData'               => [],
                ];
            }

            $alias = 'col_' . $index;
            $builder->select($identifier . ' AS `' . $alias . '`', false);
        }

        $conditions = [];
        foreach ($columns as $column) {
            if (! empty($column['conditions'])) {
                $conditions = array_merge($conditions, $column['conditions']);
            }
        }

        if (! empty($runtimeColumnConditions)) {
            foreach ($runtimeColumnConditions as $conditionSet) {
                if (! empty($conditionSet)) {
                    $conditions = array_merge($conditions, $conditionSet);
                }
            }
        } else {
            $dateConditions = $this->build_date_range_conditions_for_column($columns[0], $options);
            if (! empty($dateConditions)) {
                $conditions = array_merge($conditions, $dateConditions);
            }
        }

        if (! empty($conditions)) {
            $this->apply_conditions($builder, $conditions);
        }

        $query = $builder->get();
        $error = $this->db->error();
        if (! empty($error['code'])) {
            log_message('error', 'CCX dataset query failed: ' . $error['message']);

            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $resultRows   = $query->result_array();
        $totalRecords = count($resultRows);

        $filterDefinitions = [];
        foreach ($columns as $column) {
            $filterDefinitions[] = [
                'aggregate'          => $column['aggregate_function'] ?? 'VALUE',
                'aggregate_function' => $column['aggregate_function'] ?? 'VALUE',
                'decimal_places'     => $column['decimal_places'] ?? null,
            ];
        }

        $processed = [];
        foreach ($resultRows as $row) {
            $rawRow        = [];
            $displayRow    = [];
            $matchesSearch = ($search === '');

            foreach ($columns as $index => $column) {
                $alias     = 'col_' . $index;
                $rawValue  = $row[$alias] ?? null;
                $formatted = $this->format_value($rawValue, $column);

                $rawRow[$alias]      = $rawValue;
                $displayRow[$alias]  = html_escape((string) $formatted);

                if ($search !== '' && stripos((string) $formatted, $search) !== false) {
                    $matchesSearch = true;
                }
            }

            if (! $matchesSearch) {
                continue;
            }

            if (! $this->passes_template_filters($rawRow, $filterDefinitions, $filters)) {
                continue;
            }

            $processed[] = [
                'raw'     => $rawRow,
                'display' => $displayRow,
            ];
        }

        $recordsFiltered = count($processed);

        if ($length !== null) {
            $processed = array_slice($processed, $start, $length);
        }

        $aaData = array_map(static function ($row) {
            return array_values($row['display']);
        }, $processed);

        return [
            'draw'                 => $draw,
            'recordsTotal'         => $totalRecords,
            'recordsFiltered'      => $recordsFiltered,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $recordsFiltered,
            'aaData'               => $aaData,
        ];
    }

    private function extract_value_dataset_metadata(array $columns): ?array
    {
        $baseTable   = null;
        $indices     = [];
        $identifiers = [];

        foreach ($columns as $index => $column) {
            if (($column['mode'] ?? 'simple') !== 'simple') {
                continue;
            }

            $aggregate = $this->normalize_aggregate($column['aggregate_function'] ?? '');
            if ($aggregate !== 'VALUE') {
                continue;
            }

            $tableName = $this->resolve_table_name($column['table_name'] ?? '');
            if (! $tableName) {
                return null;
            }

            if ($baseTable === null) {
                $baseTable = $tableName;
            } elseif ($baseTable !== $tableName) {
                return null;
            }

            $identifier = $this->resolve_dataset_identifier($column, $tableName);
            if (! $identifier) {
                return null;
            }

            $indices[]           = $index;
            $identifiers[$index] = $identifier;
        }

        if ($baseTable === null || empty($indices)) {
            return null;
        }

        return [
            'base_table'  => $baseTable,
            'indices'     => $indices,
            'identifiers' => $identifiers,
        ];
    }

    private function build_value_dataset_with_aggregates(array $columns, array $metadata, array $options, array $runtimeColumnConditions = []): array
    {
        $draw    = (int) ($options['draw'] ?? 0);
        $search  = strtolower(trim((string) ($options['search'] ?? '')));
        $filters = $options['filters'] ?? [];
        $start   = isset($options['start']) ? max(0, (int) $options['start']) : 0;
        $length  = isset($options['length']) && $options['length'] !== null ? (int) $options['length'] : null;

        if ($length !== null && $length < 0) {
            $length = null;
        }

        $tableName = $metadata['base_table'] ?? null;
        if (! $tableName) {
            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $builder = $this->db->from($tableName);

        $allJoins = [];
        foreach ($columns as $column) {
            if (! empty($column['joins']) && is_array($column['joins'])) {
                foreach ($column['joins'] as $join) {
                    $key = md5(json_encode($join));
                    $allJoins[$key] = $join;
                }
            }
        }

        if (! empty($allJoins)) {
            $this->apply_joins($builder, array_values($allJoins));
        }

        foreach ($metadata['indices'] as $index) {
            $identifier = $metadata['identifiers'][$index] ?? null;
            if (! $identifier) {
                return [
                    'draw'                 => $draw,
                    'recordsTotal'         => 0,
                    'recordsFiltered'      => 0,
                    'iTotalRecords'        => 0,
                    'iTotalDisplayRecords' => 0,
                    'aaData'               => [],
                ];
            }

            $alias = 'col_' . $index;
            $builder->select($identifier . ' AS `' . $alias . '`', false);
        }

        $conditions            = [];
        $hasRuntimeConditions  = false;

        foreach ($columns as $index => $column) {
            if (! empty($column['conditions'])) {
                $conditions = array_merge($conditions, $column['conditions']);
            }

            if (! empty($runtimeColumnConditions[$index])) {
                $conditions = array_merge($conditions, $runtimeColumnConditions[$index]);
                $hasRuntimeConditions = true;
            }
        }

        if (! $hasRuntimeConditions && ! empty($metadata['indices'])) {
            $firstValueIndex = $metadata['indices'][0];
            $dateConditions  = $this->build_date_range_conditions_for_column($columns[$firstValueIndex], $options);
            if (! empty($dateConditions)) {
                $conditions = array_merge($conditions, $dateConditions);
            }
        }

        if (! empty($conditions)) {
            $this->apply_conditions($builder, $conditions);
        }

        $query = $builder->get();
        $error = $this->db->error();
        if (! empty($error['code'])) {
            log_message('error', 'CCX mixed dataset query failed: ' . $error['message']);

            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $resultRows   = $query->result_array();
        $totalRecords = count($resultRows);

        $filterDefinitions = [];
        foreach ($columns as $column) {
            $filterDefinitions[] = [
                'aggregate'          => $column['aggregate_function'] ?? 'VALUE',
                'aggregate_function' => $column['aggregate_function'] ?? 'VALUE',
                'decimal_places'     => $column['decimal_places'] ?? null,
            ];
        }

        $this->reset_column_context();
        $calculatedColumns = [];
        foreach ($columns as $index => $column) {
            $rawValue = $this->calculate_column_value($column, $runtimeColumnConditions[$index] ?? []);
            $calculatedColumns[$index] = [
                'raw'     => $rawValue,
                'display' => $this->format_value($rawValue, $column),
            ];

            $this->register_column_context($column, $index, $rawValue);
        }

        $processed = [];
        foreach ($resultRows as $row) {
            $rawRow        = [];
            $displayRow    = [];
            $matchesSearch = ($search === '');

            foreach ($columns as $index => $column) {
                $alias = 'col_' . $index;

                if (in_array($index, $metadata['indices'], true)) {
                    $rawValue  = $row[$alias] ?? null;
                    $formatted = $this->format_value($rawValue, $column);
                } else {
                    $rawValue  = $calculatedColumns[$index]['raw'] ?? null;
                    $formatted = $calculatedColumns[$index]['display'] ?? '';
                }

                $rawRow[$alias]      = $rawValue;
                $displayString       = html_escape((string) $formatted);
                $displayRow[$index]  = $displayString;

                if ($search !== '' && stripos((string) $formatted, $search) !== false) {
                    $matchesSearch = true;
                }
            }

            if (! $matchesSearch) {
                continue;
            }

            if (! $this->passes_template_filters($rawRow, $filterDefinitions, $filters)) {
                continue;
            }

            $processed[] = [
                'raw'     => $rawRow,
                'display' => $displayRow,
            ];
        }

        $recordsFiltered = count($processed);

        if ($length !== null) {
            $processed = array_slice($processed, $start, $length);
        }

        $aaData = [];
        foreach ($processed as $item) {
            $ordered = [];
            foreach ($columns as $index => $_column) {
                $ordered[] = $item['display'][$index] ?? '';
            }
            $aaData[] = $ordered;
        }

        return [
            'draw'                 => $draw,
            'recordsTotal'         => $totalRecords,
            'recordsFiltered'      => $recordsFiltered,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $recordsFiltered,
            'aaData'               => $aaData,
        ];
    }

    private function build_grouped_dataset_response(array $columns, array $options, array $runtimeColumnConditions = []): array
    {
        $draw    = (int) ($options['draw'] ?? 0);
        $search  = strtolower(trim((string) ($options['search'] ?? '')));
        $filters = $options['filters'] ?? [];
        $start   = isset($options['start']) ? max(0, (int) $options['start']) : 0;
        $length  = isset($options['length']) && $options['length'] !== null ? (int) $options['length'] : null;

        if ($length !== null && $length < 0) {
            $length = null;
        }

        $groupIndices  = [];
        $metricIndices = [];

        foreach ($columns as $index => $column) {
            $role = $this->sanitize_column_role($column['role'] ?? 'metric');
            if ($role === 'group') {
                $groupIndices[] = $index;
            } else {
                $metricIndices[] = $index;
            }
        }

        if (empty($groupIndices) || empty($metricIndices)) {
            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $baseTable = null;
        foreach (array_merge($groupIndices, $metricIndices) as $index) {
            $tableName = $this->resolve_table_name($columns[$index]['table_name'] ?? '');
            if ($tableName) {
                $baseTable = $tableName;
                break;
            }
        }

        if (! $baseTable) {
            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $builder = $this->db->from($baseTable);

        $allJoins = [];
        foreach ($columns as $column) {
            if (! empty($column['joins']) && is_array($column['joins'])) {
                foreach ($column['joins'] as $join) {
                    $key = md5(json_encode($join));
                    $allJoins[$key] = $join;
                }
            }
        }

        if (! empty($allJoins)) {
            $this->apply_joins($builder, array_values($allJoins));
        }

        $groupExpressions = [];

        foreach ($groupIndices as $index) {
            $column    = $columns[$index];
            $tableName = $this->resolve_table_name($column['table_name'] ?? '') ?: $baseTable;
            $identifier = $this->resolve_dataset_identifier($column, $tableName);

            if (! $identifier) {
                return [
                    'draw'                 => $draw,
                    'recordsTotal'         => 0,
                    'recordsFiltered'      => 0,
                    'iTotalRecords'        => 0,
                    'iTotalDisplayRecords' => 0,
                    'aaData'               => [],
                ];
            }

            $alias = 'col_' . $index;
            $builder->select($identifier . ' AS `' . $alias . '`', false);
            $groupExpressions[] = $identifier;
        }

        foreach ($groupExpressions as $expression) {
            $builder->group_by($expression);
        }

        foreach ($metricIndices as $index) {
            $column    = $columns[$index];
            $mode      = $column['mode'] ?? 'simple';
            $aggregate = $this->normalize_aggregate($column['aggregate_function'] ?? 'SUM');

            if ($mode !== 'simple' || $aggregate === 'FORMULA') {
                // Formula metrics are not supported in grouped datasets; skip them.
                continue;
            }

            $tableName = $this->resolve_table_name($column['table_name'] ?? '') ?: $baseTable;
            $alias     = 'col_' . $index;

            if ($aggregate === 'COUNT' && trim((string) ($column['column_name'] ?? '')) === '') {
                $selectExpression = 'COUNT(*)';
            } else {
                $identifier = $this->resolve_dataset_identifier($column, $tableName);
                if (! $identifier) {
                    return [
                        'draw'                 => $draw,
                        'recordsTotal'         => 0,
                        'recordsFiltered'      => 0,
                        'iTotalRecords'        => 0,
                        'iTotalDisplayRecords' => 0,
                        'aaData'               => [],
                    ];
                }

                if ($aggregate === 'VALUE') {
                    $selectExpression = 'MAX(' . $identifier . ')';
                } else {
                    $selectExpression = $aggregate . '(' . $identifier . ')';
                }
            }

            $builder->select($selectExpression . ' AS `' . $alias . '`', false);
        }

        $conditions = [];
        foreach ($columns as $index => $column) {
            if (! empty($column['conditions'])) {
                $conditions = array_merge($conditions, $column['conditions']);
            }

            if (! empty($runtimeColumnConditions[$index])) {
                $conditions = array_merge($conditions, $runtimeColumnConditions[$index]);
            }
        }

        if (! empty($conditions)) {
            $this->apply_conditions($builder, $conditions);
        }

        $query = $builder->get();
        $error = $this->db->error();
        if (! empty($error['code'])) {
            log_message('error', 'CCX grouped dataset query failed: ' . $error['message']);

            return [
                'draw'                 => $draw,
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
        }

        $resultRows   = $query->result_array();
        $totalRecords = count($resultRows);

        $filterDefinitions = [];
        foreach ($columns as $column) {
            $filterDefinitions[] = [
                'aggregate'          => $column['aggregate_function'] ?? 'VALUE',
                'aggregate_function' => $column['aggregate_function'] ?? 'VALUE',
                'decimal_places'     => $column['decimal_places'] ?? null,
            ];
        }

        $processed = [];
        foreach ($resultRows as $row) {
            $rawRow        = [];
            $displayRow    = [];
            $matchesSearch = ($search === '');

            foreach ($columns as $index => $column) {
                $alias = 'col_' . $index;
                $mode  = $column['mode'] ?? 'simple';
                $role  = $this->sanitize_column_role($column['role'] ?? 'metric');

                if ($mode !== 'simple' && $role !== 'group') {
                    $rawValue  = null;
                    $formatted = '';
                } else {
                    $rawValue  = $row[$alias] ?? null;
                    $formatted = $this->format_value($rawValue, $column);
                }

                $rawRow[$alias]     = $rawValue;
                $displayRow[$index] = html_escape((string) $formatted);

                if ($search !== '' && stripos((string) $formatted, $search) !== false) {
                    $matchesSearch = true;
                }
            }

            if (! $matchesSearch) {
                continue;
            }

            if (! $this->passes_template_filters($rawRow, $filterDefinitions, $filters)) {
                continue;
            }

            $processed[] = [
                'raw'     => $rawRow,
                'display' => $displayRow,
            ];
        }

        $recordsFiltered = count($processed);

        if ($length !== null) {
            $processed = array_slice($processed, $start, $length);
        }

        $aaData = [];
        foreach ($processed as $item) {
            $ordered = [];
            foreach ($columns as $index => $_column) {
                $ordered[] = $item['display'][$index] ?? '';
            }
            $aaData[] = $ordered;
        }

        return [
            'draw'                 => $draw,
            'recordsTotal'         => $totalRecords,
            'recordsFiltered'      => $recordsFiltered,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $recordsFiltered,
            'aaData'               => $aaData,
        ];
    }

    private function resolve_dataset_identifier(array $column, string $tableName): ?string
    {
        $columnNameRaw = trim((string) ($column['column_name'] ?? ''));
        if ($columnNameRaw === '') {
            return null;
        }

        $normalized = str_replace('`', '', $columnNameRaw);
        $normalized = preg_replace('/\.+/', '.', $normalized);
        $normalized = trim($normalized, '.');

        $identifier = $this->protect_identifier($normalized);

        if (! $identifier) {
            if (strpos($normalized, '.') === false) {
                $identifier = $this->protect_identifier($tableName . '.' . $normalized);
            } else {
                $parts = array_filter(explode('.', $normalized), static function ($segment) {
                    return $segment !== '';
                });

                if (! empty($parts)) {
                    $identifier = $this->protect_identifier(implode('.', $parts));
                }

                if (! $identifier && count($parts) === 1) {
                    $identifier = $this->protect_identifier($tableName . '.' . $parts[0]);
                }
            }
        }

        return $identifier;
    }

    public function get_available_tables(): array
    {
        $this->ensure_schema();

        $result = $this->db->query('SHOW TABLES')->result_array();
        $tables = [];
        $prefix = db_prefix();

        foreach ($result as $row) {
            $tableName = array_values($row)[0] ?? '';
            if ($tableName === '') {
                continue;
            }

            $shortName = $tableName;
            if ($prefix !== '' && strpos($tableName, $prefix) === 0) {
                $shortName = substr($tableName, strlen($prefix));
            }

            $label = $shortName;
            if ($shortName !== $tableName) {
                $label .= ' (' . $tableName . ')';
            }

            $tables[$shortName] = [
                'short' => $shortName,
                'full'  => $tableName,
                'label' => $label,
            ];
        }

        ksort($tables);

        return $tables;
    }

    public function get_columns_map(array $tables): array
    {
        $this->ensure_schema();

        $columns = [];
        foreach ($tables as $table) {
            $short = is_array($table) ? ($table['short'] ?? '') : $table;
            $full  = is_array($table) ? ($table['full'] ?? $short) : $table;

            if ($short === '') {
                continue;
            }

            if (! $this->db->table_exists($full)) {
                continue;
            }

            $sanitized = str_replace('`', '', $full);
            $query = $this->db->query('SHOW COLUMNS FROM `' . $sanitized . '`');
            $rows  = $query->result_array();
            $list  = [];
            foreach ($rows as $row) {
                $field = $row['Field'] ?? '';
                if ($field !== '') {
                    $list[$field] = $field;
                }
            }

            $columns[$short] = $list;
        }

        return $columns;
    }

    public function get_template(int $id): ?array
    {
        $template = $this->db
            ->where('id', $id)
            ->get(db_prefix() . 'ccx_report_templates')
            ->row_array();

        return $template ?: null;
    }

    public function get_template_columns(int $templateId): array
    {
        $this->ensure_schema();

        $rows = $this->db
            ->where('template_id', $templateId)
            ->order_by('position', 'ASC')
            ->get(db_prefix() . 'ccx_report_template_columns')
            ->result_array();

        if (! $rows) {
            return [];
        }

        $columns = [];

        foreach ($rows as $row) {
            $row['conditions'] = $this->parse_conditions($row['conditions'] ?? null);
            $row['mode'] = $row['mode'] ?? 'simple';
            $row['role'] = $this->sanitize_column_role($row['role'] ?? 'metric');
            $row['currency_display'] = $this->sanitize_currency_display($row['currency_display'] ?? 'inherit');
            $row['date_filter_field'] = $this->sanitize_condition_field($row['date_filter_field'] ?? '') ?? '';
            $row['joins'] = $this->parse_joins($row['joins'] ?? null);

            if ($row['mode'] === 'formula') {
                $row['formula_expression'] = trim((string) ($row['formula_expression'] ?? ''));
                $decodedSources = json_decode($row['formula_sources'] ?? '[]', true);
                $sources = [];

                if (is_array($decodedSources)) {
                    $usedKeys = [];
                    foreach ($decodedSources as $source) {
                        if (! is_array($source)) {
                            continue;
                        }

                        $key = $this->sanitize_formula_key(trim((string) ($source['key'] ?? '')), $usedKeys);

                        $sourceConditions = [];
                        if (isset($source['conditions']) && is_array($source['conditions'])) {
                            $sourceConditions = $this->filter_condition_rows($source['conditions']);
                        }

                        $sources[] = [
                            'key'         => $key,
                            'label'       => trim((string) ($source['label'] ?? '')),
                            'type'        => ($source['type'] ?? 'metric') === 'number' ? 'number' : 'metric',
                            'aggregate'   => $source['aggregate'] ?? 'SUM',
                            'table_name'  => $source['table_name'] ?? '',
                            'column_name' => $source['column_name'] ?? '',
                            'constant'    => isset($source['constant']) ? (float) $source['constant'] : 0,
                            'conditions'  => $sourceConditions,
                        ];
                    }
                }

                $row['formula_sources'] = $sources;
            } else {
                $row['formula_sources'] = [];
                $row['formula_expression'] = trim((string) ($row['formula_expression'] ?? ''));
            }

            $columns[] = $row;
        }

        return $columns;
    }

    public function get_dynamic_template_pages(int $templateId): array
    {
        $this->ensure_schema();

        $rows = $this->db
            ->where('template_id', $templateId)
            ->order_by('page_key', 'ASC')
            ->get(db_prefix() . 'ccx_report_template_pages')
            ->result_array();

        $pages = [
            'main' => [
                'sql_query'    => '',
                'html_content' => '',
                'filters'      => null,
            ],
            'sub' => [
                'sql_query'    => '',
                'html_content' => '',
                'filters'      => null,
            ],
        ];

        foreach ($rows as $row) {
            $key = strtolower((string) ($row['page_key'] ?? ''));
            if (! in_array($key, ['main', 'sub'], true)) {
                continue;
            }

            $pages[$key] = [
                'sql_query'    => (string) ($row['sql_query'] ?? ''),
                'html_content' => (string) ($row['html_content'] ?? ''),
                'filters'      => $row['filters'] ?? null,
            ];
        }

        return $pages;
    }

    public function save_template(?int $id, array $templateData, array $columns = [], array $sqlData = [], array $dynamicData = [])
    {
        $type = $this->normalize_template_type($templateData['type'] ?? null);

        if ($type === self::TEMPLATE_TYPE_SQL) {
            return $this->save_sql_template($id, $templateData, $sqlData);
        }

        if ($type === self::TEMPLATE_TYPE_DYNAMIC) {
            return $this->save_dynamic_template($id, $templateData, $dynamicData);
        }

        return $this->save_smart_template($id, $templateData, $columns);
    }

    private function save_smart_template(?int $id, array $templateData, array $columns)
    {
        $this->ensure_schema();

        $payload = $this->prepare_template_payload($templateData, self::TEMPLATE_TYPE_SMART);
        if ($payload === null) {
            return false;
        }

        $preparedColumns = $this->prepare_columns_payload($columns);
        if (empty($preparedColumns)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        if ($id) {
            $payload['updated_at'] = $now;
            $this->db->where('id', $id)->update(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = $id;
        } else {
            $payload['created_at'] = $now;
            $this->db->insert(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = (int) $this->db->insert_id();
        }

        $this->db->where('template_id', $templateId)->delete(db_prefix() . 'ccx_report_template_columns');

        foreach ($preparedColumns as $column) {
            $column['template_id'] = $templateId;
            $column['created_at']  = $now;
            $this->db->insert(db_prefix() . 'ccx_report_template_columns', $column);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $templateId;
    }

    private function save_sql_template(?int $id, array $templateData, array $sqlData)
    {
        $this->ensure_schema();

        $payload = $this->prepare_template_payload($templateData, self::TEMPLATE_TYPE_SQL, $sqlData);
        if ($payload === null) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        if ($id) {
            $payload['updated_at'] = $now;
            $this->db->where('id', $id)->update(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = $id;
        } else {
            $payload['created_at'] = $now;
            $this->db->insert(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = (int) $this->db->insert_id();
        }

        // Ensure no legacy column configuration persists.
        $this->db->where('template_id', $templateId)->delete(db_prefix() . 'ccx_report_template_columns');

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $templateId;
    }

    private function save_dynamic_template(?int $id, array $templateData, array $dynamicData)
    {
        $this->ensure_schema();

        $payload = $this->prepare_template_payload($templateData, self::TEMPLATE_TYPE_DYNAMIC);
        if ($payload === null) {
            return false;
        }

        $preparedPages = $this->prepare_dynamic_pages_payload($dynamicData);
        if (empty($preparedPages)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        if ($id) {
            $payload['updated_at'] = $now;
            $this->db->where('id', $id)->update(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = $id;
        } else {
            $payload['created_at'] = $now;
            $this->db->insert(db_prefix() . 'ccx_report_templates', $payload);
            $templateId = (int) $this->db->insert_id();
        }

        $this->db->where('template_id', $templateId)->delete(db_prefix() . 'ccx_report_template_columns');
        $this->db->where('template_id', $templateId)->delete(db_prefix() . 'ccx_report_template_pages');

        foreach ($preparedPages as $page) {
            $page['template_id'] = $templateId;
            $page['created_at']  = $now;
            $page['updated_at']  = $now;
            $this->db->insert(db_prefix() . 'ccx_report_template_pages', $page);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $templateId;
    }

    public function ensure_sample_dynamic_template(): bool
    {
        $this->ensure_schema();

        $name = 'Staff Task Performance';

        $mainSql = <<<'SQL'
SELECT
    (@rownum := @rownum + 1) AS `S.No`,
    stats.staff_name AS `Staff Name`,
    stats.role_name AS `Role`,
    CONCAT(
        CHAR(60), 'a href="', CHAR(63), 'page=sub&filters_sub[staff_id]=', stats.staff_id,
        '&filters_sub[status]=active', CHAR(34), CHAR(62),
        stats.active_tasks,
        CHAR(60), '/', 'a', CHAR(62)
    ) AS `Active Tasks`,
    CONCAT(
        CHAR(60), 'a href="', CHAR(63), 'page=sub&filters_sub[staff_id]=', stats.staff_id,
        '&filters_sub[status]=pending', CHAR(34), CHAR(62),
        stats.pending_tasks,
        CHAR(60), '/', 'a', CHAR(62)
    ) AS `Pending Tasks`,
    CONCAT(
        CHAR(60), 'a href="', CHAR(63), 'page=sub&filters_sub[staff_id]=', stats.staff_id,
        '&filters_sub[status]=completed', CHAR(34), CHAR(62),
        stats.completed_tasks,
        CHAR(60), '/', 'a', CHAR(62)
    ) AS `Completed`,
    CONCAT(
        CHAR(60), 'a href="', CHAR(63), 'page=sub&filters_sub[staff_id]=', stats.staff_id,
        '&filters_sub[status]=on_time', CHAR(34), CHAR(62),
        stats.on_time_tasks,
        CHAR(60), '/', 'a', CHAR(62)
    ) AS `On-Time`,
    CONCAT(
        CHAR(60), 'a href="', CHAR(63), 'page=sub&filters_sub[staff_id]=', stats.staff_id,
        '&filters_sub[status]=delayed', CHAR(34), CHAR(62),
        stats.delayed_tasks,
        CHAR(60), '/', 'a', CHAR(62)
    ) AS `Delayed`
FROM (
    SELECT
        s.staffid AS staff_id,
        TRIM(CONCAT(IFNULL(s.firstname, ''), ' ', IFNULL(s.lastname, ''))) AS staff_name,
        IFNULL(NULLIF(r.name, ''), 'Not Assigned') AS role_name,
        SUM(CASE WHEN t.status IN (1, 2, 3, 4) THEN 1 ELSE 0 END) AS active_tasks,
        SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) AS pending_tasks,
        SUM(CASE WHEN t.status = 5 THEN 1 ELSE 0 END) AS completed_tasks,
        SUM(CASE WHEN t.status = 5 AND (t.duedate IS NULL OR (t.datefinished IS NOT NULL AND t.datefinished <= t.duedate)) THEN 1 ELSE 0 END) AS on_time_tasks,
        SUM(
            CASE
                WHEN t.status = 5 AND t.duedate IS NOT NULL AND t.datefinished IS NOT NULL AND t.datefinished > t.duedate THEN 1
                WHEN t.status <> 5 AND t.duedate IS NOT NULL AND t.duedate < CURDATE() THEN 1
                ELSE 0
            END
        ) AS delayed_tasks
    FROM {{db_prefix}}staff AS s
    INNER JOIN {{db_prefix}}task_assigned AS ta ON ta.staffid = s.staffid
    INNER JOIN {{db_prefix}}tasks AS t ON t.id = ta.taskid
    LEFT JOIN {{db_prefix}}roles AS r ON r.roleid = s.role
    WHERE s.active = 1
      AND (
            {{filter:search}} IS NULL
         OR s.firstname LIKE CONCAT('%', {{filter:search}}, '%')
         OR s.lastname LIKE CONCAT('%', {{filter:search}}, '%')
         OR TRIM(CONCAT(IFNULL(s.firstname, ''), ' ', IFNULL(s.lastname, ''))) LIKE CONCAT('%', {{filter:search}}, '%')
         OR s.email LIKE CONCAT('%', {{filter:search}}, '%')
         OR IFNULL(r.name, '') LIKE CONCAT('%', {{filter:search}}, '%')
        )
      AND (
            {{filter:date_from}} IS NULL
         OR DATE(t.dateadded) >= {{filter:date_from}}
        )
      AND (
            {{filter:date_to}} IS NULL
         OR DATE(t.dateadded) <= {{filter:date_to}}
        )
    GROUP BY s.staffid
) AS stats
CROSS JOIN (SELECT @rownum := 0) AS seq
ORDER BY stats.staff_name;
SQL;

        $subSql = <<<'SQL'
SELECT
    (@rownum := @rownum + 1) AS `S.No`,
    detail.staff_name AS `Staff Name`,
    detail.role_name AS `Role`,
    detail.status_label AS `Status`,
    detail.task_token AS `Task Name`
FROM (
    SELECT
        ta.staffid,
        TRIM(CONCAT(IFNULL(s.firstname, ''), ' ', IFNULL(s.lastname, ''))) AS staff_name,
        IFNULL(NULLIF(r.name, ''), 'Not Assigned') AS role_name,
        CASE
            WHEN {{filter:status}} = 'active' THEN
                CASE t.status
                    WHEN 4 THEN 'In Progress'
                    WHEN 3 THEN 'Testing'
                    WHEN 2 THEN 'Awaiting Feedback'
                    WHEN 1 THEN 'Not Started'
                    ELSE 'Active'
                END
            WHEN {{filter:status}} = 'pending' THEN 'Pending'
            WHEN {{filter:status}} = 'completed' THEN 'Completed'
            WHEN {{filter:status}} = 'on_time' THEN 'On-Time'
            WHEN {{filter:status}} = 'delayed' THEN
                CASE
                    WHEN t.status = 5 THEN 'Completed (Delayed)'
                    ELSE 'Overdue'
                END
            ELSE 'Status'
        END AS status_label,
        CONCAT_WS(
            '||',
            CAST(t.id AS CHAR),
            IFNULL(NULLIF(t.name, ''), CONCAT('Task #', t.id))
        ) AS task_token,
        t.duedate,
        t.datefinished,
        t.status,
        t.id
    FROM {{db_prefix}}task_assigned AS ta
    INNER JOIN {{db_prefix}}tasks AS t ON t.id = ta.taskid
    INNER JOIN {{db_prefix}}staff AS s ON s.staffid = ta.staffid
    LEFT JOIN {{db_prefix}}roles AS r ON r.roleid = s.role
    WHERE ta.staffid = {{filter:staff_id}}
      AND (
            ({{filter:status}} = 'active'   AND t.status IN (1, 2, 3, 4))
         OR ({{filter:status}} = 'pending'  AND t.status = 1)
         OR ({{filter:status}} = 'completed' AND t.status = 5)
         OR ({{filter:status}} = 'on_time' AND t.status = 5 AND (t.duedate IS NULL OR (t.datefinished IS NOT NULL AND t.datefinished <= t.duedate)))
         OR ({{filter:status}} = 'delayed' AND (
                (t.status = 5 AND t.duedate IS NOT NULL AND t.datefinished IS NOT NULL AND t.datefinished > t.duedate)
             OR (t.status <> 5 AND t.duedate IS NOT NULL AND t.duedate < CURDATE())
         ))
      )
) AS detail
CROSS JOIN (SELECT @rownum := 0) AS seq
ORDER BY detail.duedate IS NULL ASC, detail.duedate ASC, detail.id ASC;
SQL;

        $dynamicData = [
            'main' => [
                'sql_query'    => $mainSql,
                'html_content' => '',
                'filters'      => [
                    [
                        'key'         => 'search',
                        'label'       => 'Search',
                        'type'        => 'text',
                        'required'    => false,
                        'placeholder' => 'Staff or role',
                        'description' => '',
                    ],
                    [
                        'key'         => 'date_from',
                        'label'       => 'Date From',
                        'type'        => 'date',
                        'required'    => false,
                        'placeholder' => '',
                        'description' => '',
                    ],
                    [
                        'key'         => 'date_to',
                        'label'       => 'Date To',
                        'type'        => 'date',
                        'required'    => false,
                        'placeholder' => '',
                        'description' => '',
                    ],
                ],
            ],
            'sub'  => [
                'sql_query'    => $subSql,
                'html_content' => '',
                'filters'      => [
                    [
                        'key'      => 'staff_id',
                        'label'    => 'Staff ID',
                        'type'     => 'number',
                        'required' => true,
                    ],
                    [
                        'key'      => 'status',
                        'label'    => 'Status Filter',
                        'type'     => 'text',
                        'required' => true,
                    ],
                ],
            ],
        ];

        $existing = $this->db
            ->where('name', $name)
            ->where('type', self::TEMPLATE_TYPE_DYNAMIC)
            ->get(db_prefix() . 'ccx_report_templates')
            ->row_array();

        $templateData = [
            'name'        => $name,
            'description' => 'High-level productivity dashboard for assigned tasks per staff member.',
            'type'        => self::TEMPLATE_TYPE_DYNAMIC,
        ];

        if ($existing) {
            $templateData['description'] = $existing['description'] ?? $templateData['description'];

            $this->save_template((int) $existing['id'], $templateData, [], [], $dynamicData);

            return true;
        }

        return (bool) $this->save_template(null, $templateData, [], [], $dynamicData);
    }

    public function delete_template(int $id): bool
    {
        $this->ensure_schema();

        $this->db->trans_start();
        $this->db->where('template_id', $id)->delete(db_prefix() . 'ccx_report_template_columns');
        $this->db->where('template_id', $id)->delete(db_prefix() . 'ccx_report_template_pages');
        $this->db->where('template_id', $id)->delete(db_prefix() . 'ccx_report_section_templates');
        $this->db->where('id', $id)->delete(db_prefix() . 'ccx_report_templates');
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    public function get_sections(): array
    {
        $sections = $this->db
            ->select('s.*, COUNT(st.id) AS template_count', false)
            ->from(db_prefix() . 'ccx_report_sections AS s')
            ->join(db_prefix() . 'ccx_report_section_templates AS st', 'st.section_id = s.id', 'left')
            ->group_by('s.id')
            ->order_by('s.display_order', 'ASC')
            ->order_by('s.name', 'ASC')
            ->get()
            ->result_array();

        return $sections ?? [];
    }

    public function get_sections_overview(): array
    {
        $sections = $this->get_sections();
        if (empty($sections)) {
            return [];
        }

        $sectionIds = array_map('intval', array_column($sections, 'id'));

        $rows = $this->db
            ->select('st.section_id, t.id AS template_id, t.name, t.description', false)
            ->from(db_prefix() . 'ccx_report_section_templates AS st')
            ->join(db_prefix() . 'ccx_report_templates AS t', 't.id = st.template_id', 'left')
            ->where_in('st.section_id', $sectionIds)
            ->group_start()
                ->where('t.is_active', 1)
                ->or_where('t.is_active IS NULL', null, false)
            ->group_end()
            ->order_by('st.display_order', 'ASC')
            ->order_by('t.name', 'ASC')
            ->get()
            ->result_array();

        $grouped = [];
        foreach ($rows as $row) {
            $sectionId = (int) ($row['section_id'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }
            if (! isset($grouped[$sectionId])) {
                $grouped[$sectionId] = [];
            }
            if (! empty($row['template_id'])) {
                $grouped[$sectionId][] = [
                    'id'          => (int) $row['template_id'],
                    'name'        => $row['name'] ?? '',
                    'description' => $row['description'] ?? '',
                ];
            }
        }

        foreach ($sections as &$section) {
            $sectionId = (int) $section['id'];
            $section['templates'] = $grouped[$sectionId] ?? [];
        }

        return $sections;
    }

    public function get_section(int $id): ?array
    {
        $section = $this->db
            ->where('id', $id)
            ->get(db_prefix() . 'ccx_report_sections')
            ->row_array();

        return $section ?: null;
    }

    public function get_section_template_ids(int $sectionId): array
    {
        $rows = $this->db
            ->select('template_id')
            ->where('section_id', $sectionId)
            ->order_by('display_order', 'ASC')
            ->get(db_prefix() . 'ccx_report_section_templates')
            ->result_array();

        return array_map('intval', array_column($rows, 'template_id'));
    }

    public function normalize_runtime_filters($input): array
    {
        if (empty($input)) {
            return [];
        }

        return $this->prepare_conditions_payload($input);
    }

    public function save_section(?int $id, array $sectionData, array $templateIds)
    {
        $payload = $this->prepare_section_payload($sectionData);
        if ($payload === null) {
            return false;
        }

        $templateIds = $this->sanitize_template_ids($templateIds);

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        if ($id) {
            $payload['updated_at'] = $now;
            $this->db->where('id', $id)->update(db_prefix() . 'ccx_report_sections', $payload);
            $sectionId = $id;
        } else {
            $payload['created_at'] = $now;
            $this->db->insert(db_prefix() . 'ccx_report_sections', $payload);
            $sectionId = (int) $this->db->insert_id();
        }

        $this->db->where('section_id', $sectionId)->delete(db_prefix() . 'ccx_report_section_templates');

        foreach ($templateIds as $order => $templateId) {
            $this->db->insert(db_prefix() . 'ccx_report_section_templates', [
                'section_id'    => $sectionId,
                'template_id'   => $templateId,
                'display_order' => $order + 1,
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $sectionId;
    }

    public function delete_section(int $id): bool
    {
        $this->db->trans_start();
        $this->db->where('section_id', $id)->delete(db_prefix() . 'ccx_report_section_templates');
        $this->db->where('id', $id)->delete(db_prefix() . 'ccx_report_sections');
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    public function get_sections_with_templates(): array
    {
        $sections = $this->get_sections();
        if (empty($sections)) {
            return [];
        }

        $sectionIds = array_map('intval', array_column($sections, 'id'));

        $links = $this->db
            ->select('section_id, template_id, display_order')
            ->from(db_prefix() . 'ccx_report_section_templates')
            ->where_in('section_id', $sectionIds)
            ->order_by('display_order', 'ASC')
            ->get()
            ->result_array();

        $templateIds = array_unique(array_map('intval', array_column($links, 'template_id')));

        $templateMap = $this->get_templates_map($templateIds);
        $evaluated   = [];

        foreach ($templateIds as $templateId) {
            if (isset($templateMap[$templateId])) {
                $evaluated[$templateId] = $this->evaluate_template($templateId, $templateMap[$templateId]);
            }
        }

        $linked = [];
        foreach ($links as $link) {
            $sectionId = (int) $link['section_id'];
            $linked[$sectionId][] = (int) $link['template_id'];
        }

        foreach ($sections as &$section) {
            $sectionTemplates = $linked[$section['id']] ?? [];
            $section['templates'] = [];
            foreach ($sectionTemplates as $templateId) {
                if (isset($evaluated[$templateId])) {
                    $section['templates'][] = $evaluated[$templateId];
                }
            }
        }

        return $sections;
    }

    public function evaluate_template(int $templateId, ?array $template = null, array $runtime = []): ?array
    {
        $template = $template ?? $this->get_template($templateId);
        if (! $template) {
            return null;
        }

        $columns = $this->get_template_columns($template['id']);
        $results = [];

        $this->reset_column_context();
        $columnConditions = $runtime['column_conditions'] ?? [];

        foreach ($columns as $index => $column) {
            $rawValue = $this->calculate_column_value($column, $columnConditions[$index] ?? []);
            $results[] = [
                'label'        => $column['label'],
                'aggregate'    => $column['aggregate_function'],
                'value'        => $this->format_value($rawValue, $column),
                'raw_value'    => $rawValue,
                'conditions'   => $column['conditions'],
                'table_name'   => $column['table_name'],
                'column_name'  => $column['column_name'],
                'decimal'      => isset($column['decimal_places']) ? (int) $column['decimal_places'] : null,
                'currency_display' => $column['currency_display'] ?? 'inherit',
            ];

            $this->register_column_context($column, $index, $rawValue);
        }

        return [
            'template' => $template,
            'columns'  => $results,
            'runtime_conditions' => $columnConditions,
        ];
    }

    public function preview_column(array $columnInput): array
    {
        $this->ensure_schema();

        $mode = ($columnInput['mode'] ?? 'simple') === 'formula' ? 'formula' : 'simple';
        $aggregate = $this->normalize_aggregate($columnInput['aggregate_function'] ?? '');
        $tableName = trim((string) ($columnInput['table_name'] ?? ''));

        if ($mode === 'simple') {
            if ($tableName === '') {
                return [
                    'success' => false,
                    'state'   => 'incomplete',
                    'message' => ccx_lang('ccx_template_preview_select_table', 'Select a table to preview this metric.'),
                ];
            }

            if (! preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
                return [
                    'success' => false,
                    'state'   => 'invalid',
                    'message' => ccx_lang('ccx_template_preview_invalid_table', 'Table names may only include letters, numbers and underscores.'),
                ];
            }

            if ($aggregate !== 'COUNT') {
                $columnName = trim((string) ($columnInput['column_name'] ?? ''));
                if ($columnName === '') {
                    return [
                        'success' => false,
                        'state'   => 'incomplete',
                        'message' => ccx_lang('ccx_template_preview_select_column', 'Pick a column to preview this metric.'),
                    ];
                }
            }
        } else {
            $expression = trim((string) ($columnInput['formula_expression'] ?? ''));
            $sources    = $columnInput['formula_sources'] ?? [];
            if ($expression === '' || ! is_array($sources) || empty($sources)) {
                return [
                    'success' => false,
                    'state'   => 'formula',
                    'message' => ccx_lang('ccx_template_preview_formula_missing', 'Add at least one source and a formula expression to preview.'),
                ];
            }
        }

        $preparedColumns = $this->prepare_columns_payload([$columnInput]);
        if (empty($preparedColumns)) {
            return [
                'success' => false,
                'state'   => 'invalid',
                'message' => ccx_lang('ccx_template_preview_invalid', 'Unable to build a preview with the current inputs.'),
            ];
        }

        $column = $this->normalize_preview_column($preparedColumns[0]);

        $this->reset_column_context();
        $this->lastQueryError = null;

        try {
            $value = $this->calculate_column_value($column, []);
        } catch (Throwable $e) {
            $message = $this->summarise_preview_error($e->getMessage());

            log_message('error', 'CCX preview evaluation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'state'   => 'error',
                'message' => $message !== '' ? $message : ccx_lang('ccx_template_preview_exception', 'The preview failed unexpectedly. Please review your inputs.'),
            ];
        }

        if ($this->lastQueryError !== null) {
            return [
                'success' => false,
                'state'   => 'error',
                'message' => $this->summarise_preview_error($this->lastQueryError),
            ];
        }

        if ($column['mode'] === 'formula' && $value === null) {
            return [
                'success' => false,
                'state'   => 'formula',
                'message' => ccx_lang('ccx_template_preview_formula_pending', 'Preview will appear once all referenced metrics resolve.'),
            ];
        }

        $formatted = $this->format_value($value, $column);
        $warnings  = $this->build_preview_warnings($columnInput, $column);

        return [
            'success'   => true,
            'state'     => 'ok',
            'value'     => $formatted,
            'raw_value' => $value,
            'aggregate' => $column['aggregate_function'],
            'warnings'  => $warnings,
        ];
    }

    private function reset_column_context(): void
    {
        $this->columnContext    = [];
        $this->columnSlugCounts = [];
    }

    private function register_column_context(array $column, int $index, $rawValue): void
    {
        $identifiers = $this->build_column_identifiers($column, $index);

        foreach ($identifiers as $identifier) {
            $key = 'column:' . strtolower($identifier);
            $this->columnContext[$key] = $rawValue;
        }
    }

    /**
     * @return string[]
     */
    private function build_column_identifiers(array $column, int $index): array
    {
        $position = (int) $index + 1;
        $identifiers = [
            (string) $position,
            'col_' . $position,
            'column_' . $position,
        ];

        $candidates = [];

        $label = trim((string) ($column['label'] ?? ''));
        if ($label !== '') {
            $candidates[] = $label;
        }

        $columnName = trim((string) ($column['column_name'] ?? ''));
        if ($columnName !== '') {
            $candidates[] = $columnName;
        }

        foreach ($candidates as $candidate) {
            $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $candidate));
            $slug = trim($slug, '_');

            if ($slug === '') {
                continue;
            }

            $base = $slug;
            if (isset($this->columnSlugCounts[$base])) {
                $this->columnSlugCounts[$base]++;
                $slug = $base . '_' . $this->columnSlugCounts[$base];
            } else {
                $this->columnSlugCounts[$base] = 1;
            }

            $identifiers[] = $slug;
        }

        return array_values(array_unique($identifiers));
    }

    private function normalize_column_token(string $token): ?string
    {
        $token = strtolower(trim($token));

        if ($token === '') {
            return null;
        }

        $token = str_replace(['column.', 'col.'], ['column:', 'col:'], $token);

        if (strpos($token, 'column:') === 0) {
            $identifier = substr($token, 7);
        } elseif (strpos($token, 'col:') === 0) {
            $identifier = substr($token, 4);
        } elseif (preg_match('/^col_(\d+)$/', $token, $matches)) {
            $identifier = 'col_' . $matches[1];
        } elseif (preg_match('/^column_(\d+)$/', $token, $matches)) {
            $identifier = 'column_' . $matches[1];
        } elseif (ctype_digit($token)) {
            $identifier = $token;
        } else {
            $identifier = null;
        }

        if ($identifier === null || $identifier === '') {
            return null;
        }

        return 'column:' . $identifier;
    }

    /**
     * @return array{found:bool,value:mixed}
     */
    private function get_column_context_value(string $token, bool $allowArray = false): array
    {
        $normalized = $this->normalize_column_token($token);

        if ($normalized === null) {
            return ['found' => false, 'value' => null];
        }

        if (! array_key_exists($normalized, $this->columnContext)) {
            return ['found' => false, 'value' => null];
        }

        $value = $this->columnContext[$normalized];

        if (! $allowArray && is_array($value)) {
            return ['found' => false, 'value' => null];
        }

        return ['found' => true, 'value' => $value];
    }

    private function calculate_column_value(array $column, array $runtimeConditions = [])
    {
        $mode = $column['mode'] ?? 'simple';

        if ($mode === 'formula') {
            return $this->evaluate_formula_column($column, $runtimeConditions);
        }

        $tableName = $this->resolve_table_name($column['table_name'] ?? '');
        if (! $tableName) {
            return null;
        }

        $aggregate = $this->normalize_aggregate($column['aggregate_function'] ?? '');
        $columnName = $column['column_name'] ?? null;
        $conditions = $this->parse_conditions($column['conditions'] ?? null);

        if (! empty($runtimeConditions)) {
            $conditions = array_merge($conditions, $runtimeConditions);
        }

        $joins = $column['joins'] ?? [];

        return $this->run_aggregate_query($aggregate, $tableName, $columnName, $conditions, $joins);
    }

    private function prepare_template_payload(array $data, string $type, array $sqlData = []): ?array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $description = trim((string) ($data['description'] ?? ''));

        $payload = [
            'name'        => $name,
            'description' => $description !== '' ? $description : null,
            'type'        => $this->normalize_template_type($type),
        ];

        if ($payload['type'] === self::TEMPLATE_TYPE_SQL) {
            $query = (string) ($sqlData['sql_query'] ?? '');
            if (trim($query) === '') {
                return null;
            }

            $filters = $sqlData['filters'] ?? null;
            if ($filters !== null && trim((string) $filters) === '') {
                $filters = null;
            }

            $payload['sql_query'] = $query;
            $payload['filters']   = $filters;
            $payload['is_active'] = isset($sqlData['is_active']) && (int) $sqlData['is_active'] === 1 ? 1 : 0;
        } elseif ($payload['type'] === self::TEMPLATE_TYPE_DYNAMIC) {
            $payload['sql_query'] = null;
            $payload['filters']   = null;
            $payload['is_active'] = 1;
        } else {
            $filters = $data['filters'] ?? null;
            if ($filters !== null && trim((string) $filters) === '') {
                $filters = null;
            }

            $payload['sql_query'] = null;
            $payload['filters']   = $filters;
            $payload['is_active'] = 1;
        }

        return $payload;
    }

    public function run_sql_template_query(array $template, array $filters = []): array
    {
        $queryRaw = html_entity_decode((string) ($template['sql_query'] ?? ''), ENT_QUOTES | ENT_HTML5);
        $query    = $this->replace_sql_placeholders($queryRaw);
        [$query, $bindings] = $this->apply_sql_filter_bindings($query, $filters);

        if (! $this->is_safe_sql_query($query)) {
            return [[], ccx_lang('ccx_template_sql_invalid', 'Only read-only SQL statements are allowed.')];
        }

        try {
            $result = $this->db->query($query, $bindings);
        } catch (Throwable $e) {
            log_message('error', 'CCX SQL template query failed: ' . $e->getMessage());

            return [[], ccx_lang('ccx_template_sql_query_failed', 'Failed to execute the SQL query') . ': ' . $e->getMessage()];
        }

        if (! $result) {
            return [[], ccx_lang('ccx_template_sql_query_failed', 'Failed to execute the SQL query')];
        }

        $rows = $result->result_array();

        $rowLimit = null;
        if (count($rows) > self::SQL_MAX_ROWS) {
            $rows     = array_slice($rows, 0, self::SQL_MAX_ROWS);
            $rowLimit = self::SQL_MAX_ROWS;
        }

        $columns = [];
        if (! empty($rows)) {
            $columns = array_keys(reset($rows));
        } elseif (method_exists($result, 'list_fields')) {
            $columns = $result->list_fields();
        }

        return [
            [
                'columns'   => $columns,
                'rows'      => $rows,
                'row_limit' => $rowLimit,
            ],
            null,
        ];
    }

    public function is_safe_sql_query(string $query): bool
    {
        $query = trim($query);
        if ($query === '') {
            return false;
        }

        $query = ltrim($query, '(');

        return (bool) preg_match('/^(SELECT|WITH|SHOW|DESCRIBE)\b/i', $query);
    }

    private function replace_sql_placeholders(string $query): string
    {
        $prefix = db_prefix();

        return str_replace(
            ['{{db_prefix}}', '{{DB_PREFIX}}', '{{prefix}}', '{{PREFIX}}'],
            $prefix,
            $query
        );
    }

    /**
     * @param string               $query
     * @param array<string, mixed> $filters
     *
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function apply_sql_filter_bindings(string $query, array $filters): array
    {
        $bindings = [];

        $processed = preg_replace_callback('/\{\{filter(?:_[A-Za-z0-9]+)?:([A-Za-z0-9_]+)\}\}/', function ($matches) use (&$bindings, $filters) {
            $key = $matches[1];
            $bindings[] = array_key_exists($key, $filters) ? $filters[$key] : null;

            return '?';
        }, $query);

        return [$processed ?? $query, $bindings];
    }

    private function prepare_columns_payload(array $columns): array
    {
        $prepared = [];
        $position = 1;

        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $mode = ($column['mode'] ?? 'simple') === 'formula' ? 'formula' : 'simple';

            if ($mode === 'formula') {
                $formula = $this->prepare_formula_column($column, $position);
                if ($formula !== null) {
                    $prepared[] = $formula;
                    $position++;
                }
                continue;
            }

            $label = trim((string) ($column['label'] ?? ''));
            $table = trim((string) ($column['table_name'] ?? ''));
            $aggregate = $this->normalize_aggregate($column['aggregate_function'] ?? '');
            $columnName = trim((string) ($column['column_name'] ?? ''));

            if ($table === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
                continue;
            }

            if ($aggregate !== 'COUNT' && $columnName === '') {
                continue;
            }

            if ($label === '') {
                $label = $this->default_column_label($aggregate, $columnName);
            }

            $conditions = $this->prepare_conditions_payload($column['conditions'] ?? []);

            $joins = $this->prepare_joins_payload($column['joins'] ?? []);

            $prepared[] = [
                'position'           => $position++,
                'label'              => $label,
                'aggregate_function' => $aggregate,
                'table_name'         => $table,
                'column_name'        => $columnName !== '' ? $columnName : null,
                'conditions'         => ! empty($conditions) ? json_encode($conditions) : null,
                'decimal_places'     => isset($column['decimal_places']) && $column['decimal_places'] !== '' ? (int) $column['decimal_places'] : null,
                'currency_display'   => $this->sanitize_currency_display($column['currency_display'] ?? null),
                'date_filter_field'  => $this->sanitize_date_field($column['date_filter_field'] ?? null),
                'joins'              => ! empty($joins) ? json_encode($joins) : null,
                'role'               => $this->sanitize_column_role($column['role'] ?? 'metric'),
                'mode'               => 'simple',
                'formula_sources'    => null,
                'formula_expression' => null,
            ];
        }

        return $prepared;
    }

    private function prepare_dynamic_pages_payload(array $input): array
    {
        $pages    = [];
        $allowed  = ['main', 'sub'];

        foreach ($allowed as $pageKey) {
            $pageInput = $input[$pageKey] ?? [];
            if (! is_array($pageInput)) {
                $pageInput = [];
            }

            $sqlQuery = isset($pageInput['sql_query']) ? (string) $pageInput['sql_query'] : '';
            $sqlQuery = html_entity_decode($sqlQuery, ENT_QUOTES | ENT_HTML5);
            $sqlQuery = str_replace(["\r\n", "\r"], "\n", $sqlQuery);

            if ($sqlQuery !== '' && ! $this->is_safe_sql_query($sqlQuery)) {
                $sqlQuery = '';
            }

            $htmlContent = isset($pageInput['html_content'])
                ? (string) $pageInput['html_content']
                : (string) ($pageInput['html'] ?? '');

            $filtersPayload = $pageInput['filters'] ?? null;
            if (is_array($filtersPayload)) {
                $filtersPayload = json_encode($filtersPayload, JSON_UNESCAPED_UNICODE);
            } elseif (is_string($filtersPayload)) {
                $filtersPayload = trim($filtersPayload) !== '' ? $filtersPayload : null;
            } else {
                $filtersPayload = null;
            }

            $pages[] = [
                'page_key'     => $pageKey,
                'sql_query'    => $sqlQuery !== '' ? $sqlQuery : null,
                'html_content' => $htmlContent !== '' ? $htmlContent : null,
                'filters'      => $filtersPayload,
            ];
        }

        return $pages;
    }

    private function sanitize_currency_display($value): string
    {
        $value = strtolower(trim((string) $value));

        if (in_array($value, ['show', 'hide'], true)) {
            return $value;
        }

        return 'inherit';
    }

    private function sanitize_column_role($value): string
    {
        $value = strtolower(trim((string) $value));

        return $value === 'group' ? 'group' : 'metric';
    }

    private function sanitize_date_field($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $sanitized = $this->sanitize_condition_field($value);

        return $sanitized ?: null;
    }

    private function build_runtime_column_conditions(array $columns, array $options): array
    {
        $conditions = [];
        $dateRange  = $this->normalize_date_range($options);

        foreach ($columns as $index => $column) {
            $columnConditions = [];

            $dateConditions = $this->build_date_range_conditions_for_column($column, $options, $dateRange);
            if (! empty($dateConditions)) {
                $columnConditions = array_merge($columnConditions, $dateConditions);
            }

            if (! empty($columnConditions)) {
                $conditions[$index] = $columnConditions;
            }
        }

        return $conditions;
    }

    private function build_date_range_conditions_for_column(array $column, array $options, ?array $normalizedRange = null): array
    {
        $field = $this->sanitize_condition_field($column['date_filter_field'] ?? '');
        if (! $field) {
            return [];
        }

        $range = $normalizedRange ?? $this->normalize_date_range($options);
        if ($range === null) {
            return [];
        }

        [$from, $to] = $range;
        $conditions  = [];

        if ($from) {
            $conditions[] = [
                'field'    => $field,
                'operator' => '>=',
                'value'    => $from,
                'type'     => 'date',
            ];
        }

        if ($to) {
            $conditions[] = [
                'field'    => $field,
                'operator' => '<=',
                'value'    => $to,
                'type'     => 'date',
            ];
        }

        return $conditions;
    }

    private function normalize_date_range(array $options): ?array
    {
        $fromRaw = $options['date_from'] ?? '';
        $toRaw   = $options['date_to'] ?? '';

        $from = $this->parse_date_value($fromRaw);
        $to   = $this->parse_date_value($toRaw);

        if (! $from && ! $to) {
            return null;
        }

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function parse_date_value($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (function_exists('to_sql_date')) {
            $sql = to_sql_date($value);
            if ($sql !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sql)) {
                return $sql;
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function prepare_formula_column(array $input, int $position): ?array
    {
        $expression = trim((string) ($input['formula_expression'] ?? ''));
        $sourcesInput = $input['formula_sources'] ?? [];

        if ($expression === '' || ! is_array($sourcesInput) || empty($sourcesInput)) {
            return null;
        }

        $label = trim((string) ($input['label'] ?? ''));
        $decimalPlaces = isset($input['decimal_places']) && $input['decimal_places'] !== '' ? (int) $input['decimal_places'] : null;

        $sources = [];
        $usedKeys = [];

        foreach ($sourcesInput as $source) {
            if (! is_array($source)) {
                continue;
            }

            $type = ($source['type'] ?? 'metric') === 'number' ? 'number' : 'metric';
            $rawKey = trim((string) ($source['key'] ?? ''));
            $key = $this->sanitize_formula_key($rawKey, $usedKeys);

            if ($type === 'metric') {
                $tableName  = trim((string) ($source['table_name'] ?? ''));
                $aggregate  = $this->normalize_aggregate($source['aggregate'] ?? 'SUM');
                $columnName = trim((string) ($source['column_name'] ?? ''));
                $conditions = $this->prepare_conditions_payload($source['conditions'] ?? []);

                if ($tableName === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
                    continue;
                }

                if ($aggregate !== 'COUNT' && $columnName === '') {
                    continue;
                }

                $sources[] = [
                    'key'         => $key,
                    'label'       => trim((string) ($source['label'] ?? '')),
                    'type'        => 'metric',
                    'aggregate'   => $aggregate,
                    'table_name'  => $tableName,
                    'column_name' => $columnName,
                    'conditions'  => $conditions,
                ];
            } else {
                $constant = isset($source['constant']) && $source['constant'] !== '' ? (float) $source['constant'] : 0;

                $sources[] = [
                    'key'      => $key,
                    'label'    => trim((string) ($source['label'] ?? '')),
                    'type'     => 'number',
                    'constant' => $constant,
                ];
            }
        }

        if (empty($sources)) {
            return null;
        }

        if ($label === '') {
            $label = $this->default_column_label('FORMULA', null);
        }

        return [
            'position'           => $position,
            'label'              => $label,
            'aggregate_function' => 'FORMULA',
            'table_name'         => '',
            'column_name'        => null,
            'conditions'         => null,
            'decimal_places'     => $decimalPlaces,
            'currency_display'   => $this->sanitize_currency_display($input['currency_display'] ?? null),
            'date_filter_field'  => null,
            'joins'              => null,
            'role'               => 'metric',
            'mode'               => 'formula',
            'formula_sources'    => json_encode($sources),
            'formula_expression' => $expression,
        ];
    }

    private function prepare_conditions_payload($input): array
    {
        if (! is_array($input) || ! isset($input['field']) || ! is_array($input['field'])) {
            return [];
        }

        $fields    = $input['field'];
        $operators = $input['operator'] ?? [];
        $values    = $input['value'] ?? [];
        $types     = $input['type'] ?? [];

        $conditions = [];

        foreach ($fields as $index => $field) {
            $fieldName = trim((string) $field);
            $operator  = strtoupper(trim((string) ($operators[$index] ?? '')));
            $value     = $values[$index] ?? '';
            $type      = strtolower(trim((string) ($types[$index] ?? 'string')));

            if ($fieldName === '' || $operator === '' || $value === '') {
                continue;
            }

            if (! in_array($operator, ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'IN', 'NOT IN'], true)) {
                continue;
            }

            if (! in_array($type, ['string', 'number', 'date'], true)) {
                $type = 'string';
            }

            $conditions[] = [
                'field'    => $fieldName,
                'operator' => $operator,
                'value'    => $value,
                'type'     => $type,
            ];
        }

        return $conditions;
    }

    private function prepare_joins_payload($input): array
    {
        if (! is_array($input) || ! isset($input['table_name']) || ! is_array($input['table_name'])) {
            return [];
        }

        $tables  = $input['table_name'];
        $types   = $input['type'] ?? [];
        $aliases = $input['alias'] ?? [];
        $ons     = $input['on'] ?? [];

        $joins = [];

        foreach ($tables as $index => $table) {
            $tableName = $this->sanitize_join_table($table);
            $onClause  = $this->sanitize_join_on($ons[$index] ?? '');
            $type      = $this->sanitize_join_type($types[$index] ?? '');
            $alias     = $this->sanitize_join_alias($aliases[$index] ?? '');

            if (! $tableName || ! $onClause) {
                continue;
            }

            $joins[] = [
                'table_name' => $tableName,
                'alias'      => $alias,
                'type'       => $type,
                'on'         => $onClause,
            ];
        }

        return $joins;
    }

    private function sanitize_template_ids($templateIds): array
    {
        if (! is_array($templateIds)) {
            return [];
        }

        $ordered = [];
        foreach ($templateIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ordered[] = $id;
            }
        }

        if (empty($ordered)) {
            return [];
        }

        $available = $this->get_templates_map($ordered);
        $unique = [];
        foreach ($ordered as $id) {
            if (isset($available[$id]) && ! in_array($id, $unique, true)) {
                $unique[] = $id;
            }
        }

        return $unique;
    }

    private function sanitize_formula_key(string $key, array &$usedKeys): string
    {
        $key = strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $key));

        if ($key === '' || is_numeric($key[0])) {
            $key = 'src';
        }

        $base = $key;
        $suffix = 1;
        while (in_array($key, $usedKeys, true)) {
            $key = $base . '_' . $suffix++;
        }

        $usedKeys[] = $key;

        return $key;
    }

    private function prepare_section_payload(array $data): ?array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $description = trim((string) ($data['description'] ?? ''));
        $order       = isset($data['display_order']) ? (int) $data['display_order'] : 0;

        return [
            'name'          => $name,
            'description'   => $description !== '' ? $description : null,
            'display_order' => $order,
        ];
    }

    private function run_aggregate_query(string $aggregate, string $tableName, ?string $columnName, array $conditions = [], array $joins = [])
    {
        $this->lastQueryError = null;
        $builder = $this->db->from($tableName);

        if (! empty($joins)) {
            $this->apply_joins($builder, $joins);
        }

        $aggregate = $this->normalize_aggregate($aggregate);

        if ($aggregate === 'COUNT' && ($columnName === null || $columnName === '')) {
            $builder->select('COUNT(*) AS result', false);
        } elseif ($aggregate === 'VALUE') {
            $identifier = $this->protect_identifier($columnName ?? '');
            if (! $identifier) {
                return null;
            }
            $builder->select($identifier . ' AS result', false);
            $builder->limit(1);
        } else {
            $identifier = $this->protect_identifier($columnName ?? '');
            if (! $identifier) {
                return null;
            }
            $builder->select($aggregate . '(' . $identifier . ') AS result', false);
        }

        if (! empty($conditions)) {
            $this->apply_conditions($builder, $conditions);
        }

        $query = $builder->get();
        $error = $this->db->error();

        if (! empty($error['code'])) {
            $message = isset($error['message']) ? trim(preg_replace('/\s+/', ' ', (string) $error['message'])) : '';
            if ($message === '') {
                $message = 'Unknown database error';
            }
            $this->lastQueryError = $message;
            log_message('error', 'CCX aggregate query failed: ' . $message);
            return null;
        }

        $row = $query->row();

        return $row->result ?? null;
    }

    private function normalize_preview_column(array $column): array
    {
        $column['conditions'] = $this->parse_conditions($column['conditions'] ?? null);
        $column['joins']      = $this->parse_joins($column['joins'] ?? null);
        $column['mode']       = $column['mode'] ?? 'simple';
        $column['role']       = $this->sanitize_column_role($column['role'] ?? 'metric');
        $column['currency_display'] = $this->sanitize_currency_display($column['currency_display'] ?? 'inherit');
        $column['date_filter_field'] = $this->sanitize_condition_field($column['date_filter_field'] ?? '') ?? '';
        $column['formula_expression'] = trim((string) ($column['formula_expression'] ?? ''));

        if ($column['mode'] === 'formula') {
            $decoded = json_decode($column['formula_sources'] ?? '[]', true);
            $column['formula_sources'] = is_array($decoded) ? $decoded : [];
        } else {
            $column['formula_sources'] = [];
        }

        return $column;
    }

    private function summarise_preview_error(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message));
        if ($message === '') {
            return ccx_lang('ccx_template_preview_error_generic', 'The database rejected this query. Check table names, joins and filters.');
        }

        return $message;
    }

    private function build_preview_warnings(array $originalInput, array $column): array
    {
        $warnings = [];

        $inputJoinCount = 0;
        if (isset($originalInput['joins']['table_name']) && is_array($originalInput['joins']['table_name'])) {
            $tables = $originalInput['joins']['table_name'];
            $ons    = $originalInput['joins']['on'] ?? [];
            foreach ($tables as $index => $table) {
                $tableValue = trim((string) $table);
                $onValue    = trim((string) ($ons[$index] ?? ''));
                if ($tableValue === '' || $onValue === '') {
                    continue;
                }
                $inputJoinCount++;
            }
        }

        $appliedJoinCount = is_array($column['joins']) ? count($column['joins']) : 0;
        if ($inputJoinCount > $appliedJoinCount) {
            $warnings[] = ccx_lang('ccx_template_preview_ignored_joins', 'Some joins were skipped because they were incomplete or invalid.');
        }

        return $warnings;
    }

    private function evaluate_formula_column(array $column, array $runtimeConditions = [])
    {
        $sources = $column['formula_sources'] ?? [];
        if (is_string($sources)) {
            $decoded = json_decode($sources, true);
            $sources = is_array($decoded) ? $decoded : [];
        }

        if (empty($sources)) {
            return null;
        }

        $values = [];
        foreach ($sources as $source) {
            if (! is_array($source) || empty($source['key'])) {
                continue;
            }

            $value = $this->evaluate_formula_source($source, $runtimeConditions);
            if ($value === null) {
                return null;
            }
            $values[$source['key']] = $value;
        }

        if (empty($values)) {
            return null;
        }

        if (! empty($this->columnContext)) {
            foreach ($this->columnContext as $identifier => $contextValue) {
                if (! array_key_exists($identifier, $values)) {
                    $values[$identifier] = $contextValue;
                }

                if (strpos($identifier, 'column:') === 0) {
                    $slug = substr($identifier, 7);
                    if ($slug !== '') {
                        $dotKey = 'column.' . $slug;
                        if (! array_key_exists($dotKey, $values)) {
                            $values[$dotKey] = $contextValue;
                        }

                        $shortKey = 'col:' . $slug;
                        if (! array_key_exists($shortKey, $values)) {
                            $values[$shortKey] = $contextValue;
                        }

                        $shortDotKey = 'col.' . $slug;
                        if (! array_key_exists($shortDotKey, $values)) {
                            $values[$shortDotKey] = $contextValue;
                        }
                    }
                }
            }
        }

        $expression = trim((string) ($column['formula_expression'] ?? ''));
        if ($expression === '') {
            return null;
        }

        return $this->evaluate_formula_expression($expression, $values);
    }

    private function evaluate_formula_source(array $source, array $runtimeConditions = [])
    {
        $type = $source['type'] ?? 'metric';
        if ($type === 'number') {
            return (float) ($source['constant'] ?? 0);
        }

        $tableName = $this->resolve_table_name($source['table_name'] ?? '');
        if (! $tableName) {
            return null;
        }

        $aggregate  = $this->normalize_aggregate($source['aggregate'] ?? 'SUM');
        $columnName = $source['column_name'] ?? null;

        $conditions = [];
        if (! empty($runtimeConditions)) {
            $conditions = array_merge($conditions, $runtimeConditions);
        }
        if (! empty($source['conditions']) && is_array($source['conditions'])) {
            $conditions = array_merge($conditions, $source['conditions']);
        }

        return $this->run_aggregate_query($aggregate, $tableName, $columnName, $conditions);
    }

    private function evaluate_formula_expression(string $expression, array $values)
    {
        $evaluated = $expression;

        foreach ($values as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $evaluated   = str_replace($placeholder, $this->numeric_placeholder_value($value), $evaluated);
        }

        if (preg_match('/\{\{[^}]+\}\}/', $evaluated)) {
            return null;
        }

        if (! preg_match('/^[0-9\+\-\*\/\.\(\)\s]+$/', $evaluated)) {
            return null;
        }

        $result = null;

        try {
            set_error_handler(static function () {
                throw new RuntimeException('Formula evaluation error');
            });
            /** @noinspection PhpEvalInspection */
            $result = eval('return ' . $evaluated . ';');
        } catch (Throwable $e) {
            log_message('error', 'CCX formula evaluation failed: ' . $e->getMessage());
            $result = null;
        } finally {
            restore_error_handler();
        }

        return is_numeric($result) ? (float) $result : null;
    }

    private function numeric_placeholder_value($value): string
    {
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_array($value) || is_object($value)) {
            return '0';
        }

        if ($value === null || $value === '') {
            return '0';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            $numeric = $value + 0;
            if (is_float($numeric) && ! is_finite($numeric)) {
                return '0';
            }

            return (string) $numeric;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return '0';
        }

        if (is_numeric($trimmed)) {
            $numeric = $trimmed + 0;
            if (is_float($numeric) && ! is_finite($numeric)) {
                return '0';
            }

            return (string) $numeric;
        }

        return '0';
    }

    private function ensure_schema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $table = db_prefix() . 'ccx_report_template_columns';

        if ($this->db->table_exists($table)) {
            $fields = $this->db->list_fields($table);

            if (! in_array('mode', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `mode` VARCHAR(20) NOT NULL DEFAULT 'simple' AFTER `decimal_places`");
            }

            if (! in_array('formula_sources', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `formula_sources` LONGTEXT NULL AFTER `mode`");
            }

            if (! in_array('formula_expression', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `formula_expression` TEXT NULL AFTER `formula_sources`");
            }

            if (! in_array('currency_display', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `currency_display` VARCHAR(20) NULL AFTER `decimal_places`");
            }

            if (! in_array('date_filter_field', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `date_filter_field` VARCHAR(191) NULL AFTER `currency_display`");
            }

            if (! in_array('joins', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `joins` LONGTEXT NULL AFTER `date_filter_field`");
            }

            if (! in_array('role', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $table . "` ADD `role` VARCHAR(20) NOT NULL DEFAULT 'metric' AFTER `mode`");
            }
        }

        $pagesTable = db_prefix() . 'ccx_report_template_pages';
        if (! $this->db->table_exists($pagesTable)) {
            $this->db->query('CREATE TABLE `' . $pagesTable . "` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `template_id` INT(11) NOT NULL,
                `page_key` VARCHAR(50) NOT NULL,
                `sql_query` LONGTEXT NULL,
                `html_content` LONGTEXT NULL,
                `filters` LONGTEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `template_page_unique` (`template_id`, `page_key`),
                KEY `template_idx` (`template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $fields = $this->db->list_fields($pagesTable);
            if (! in_array('html_content', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $pagesTable . '` ADD `html_content` LONGTEXT NULL AFTER `sql_query`');
            }
            if (! in_array('filters', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $pagesTable . '` ADD `filters` LONGTEXT NULL AFTER `html_content`');
            }
            if (! in_array('page_key', $fields, true)) {
                $this->db->query('ALTER TABLE `' . $pagesTable . '` ADD `page_key` VARCHAR(50) NOT NULL AFTER `template_id`');
            }
        }

        self::$schemaEnsured = true;
    }

    private function resolve_table_name(string $table): ?string
    {
        $table = trim(str_replace('`', '', $table));

        if ($table === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return null;
        }

        if ($this->db->table_exists($table)) {
            return $table;
        }

        $prefix = db_prefix();

        if ($prefix !== '' && strpos($table, $prefix) === 0) {
            return $table;
        }

        if ($prefix !== '' && $this->db->table_exists($prefix . $table)) {
            return $prefix . $table;
        }

        return $table;
    }

    private function protect_identifier(string $identifier): ?string
    {
        $identifier = trim(str_replace('`', '', $identifier));

        if ($identifier === '' || ! preg_match('/^[A-Za-z0-9_$.]+$/', $identifier)) {
            return null;
        }

        $segments = explode('.', $identifier);
        $segments = array_map(static function ($segment) {
            return '`' . $segment . '`';
        }, $segments);

        return implode('.', $segments);
    }

    private function normalize_aggregate(string $aggregate): string
    {
        $aggregate = strtoupper(trim($aggregate));

        if ($aggregate === 'FORMULA') {
            return 'FORMULA';
        }

        return array_key_exists($aggregate, $this->aggregateOptions) ? $aggregate : 'SUM';
    }

    private function apply_conditions($builder, array $conditions): void
    {
        foreach ($conditions as $condition) {
            $field = $this->sanitize_condition_field($condition['field'] ?? '');
            if (! $field) {
                continue;
            }

            $operator = strtoupper($condition['operator'] ?? '=');
            $value    = $this->cast_condition_value($condition['value'] ?? '', $condition['type'] ?? 'string', $operator);

            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            switch ($operator) {
                case '!=':
                    $builder->where($field . ' !=', $value);
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $builder->where($field . ' ' . $operator, $value);
                    break;
                case 'LIKE':
                    $builder->like($field, $value);
                    break;
                case 'IN':
                    if (is_array($value)) {
                        $builder->where_in($field, $value);
                    } else {
                        $builder->where($field, $value);
                    }
                    break;
                case 'NOT IN':
                    if (is_array($value)) {
                        $builder->where_not_in($field, $value);
                    } else {
                        $builder->where($field . ' !=', $value);
                    }
                    break;
                default:
                    $builder->where($field, $value);
                    break;
            }
        }
    }

    private function sanitize_condition_field(string $field): ?string
    {
        $field = trim(str_replace('`', '', $field));

        if ($field === '' || ! preg_match('/^[A-Za-z0-9_$.]+$/', $field)) {
            return null;
        }

        return $field;
    }

    private function cast_condition_value($value, string $type, string $operator = '=')
    {
        $operator = strtoupper($operator);
        $type     = strtolower($type);
        $resolved = $this->resolve_condition_dynamic_value($value);

        if (in_array($operator, ['IN', 'NOT IN'], true)) {
            return $this->normalize_condition_value_list($resolved, $type);
        }

        return $this->cast_single_condition_value($resolved, $type);
    }

    private function resolve_condition_dynamic_value($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'resolve_condition_dynamic_value'], $value);
        }

        if (! is_string($value) || strpos($value, '{{') === false) {
            return $value;
        }

        if (! preg_match_all('/\{\{([A-Za-z0-9_:\-.]+)\}\}/', $value, $matches)) {
            return $value;
        }

        if (count($matches[0]) === 1 && trim($matches[0][0]) === trim($value)) {
            $resolved = $this->lookup_condition_tag($matches[1][0], true);
            return $resolved !== null ? $resolved : '';
        }

        $resolvedString = $value;
        foreach ($matches[0] as $index => $token) {
            $replacement = $this->lookup_condition_tag($matches[1][$index], false);
            if ($replacement === null || is_array($replacement)) {
                $replacement = '';
            }
            $resolvedString = str_replace($token, (string) $replacement, $resolvedString);
        }

        return $resolvedString;
    }

    private function lookup_condition_tag(string $token, bool $allowArray = false)
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $contextLookup = $this->get_column_context_value($token, $allowArray);
        if ($contextLookup['found']) {
            return $contextLookup['value'];
        }

        $hookValue = function_exists('hooks')
            ? hooks()->apply_filters('ccx_condition_tag_value', null, $token, $allowArray)
            : null;
        if ($hookValue !== null) {
            return $hookValue;
        }

        $prefix = null;
        $key    = $token;

        if (strpos($token, ':') !== false) {
            [$prefix, $key] = explode(':', $token, 2);
            $prefix = strtolower(trim($prefix));
            $key    = trim($key);
        }

        $CI    = function_exists('get_instance') ? get_instance() : null;
        $input = $CI && isset($CI->input) ? $CI->input : null;

        $fetchInput = static function ($input, $method, $key) {
            if (! $input || ! method_exists($input, $method)) {
                return null;
            }
            return $input->{$method}($key, true);
        };

        switch ($prefix) {
            case 'get':
                return $fetchInput($input, 'get', $key);
            case 'post':
                return $fetchInput($input, 'post', $key);
            case 'request':
                $value = $fetchInput($input, 'get', $key);
                if ($value === null) {
                    $value = $fetchInput($input, 'post', $key);
                }
                return $value;
            case 'session':
                return ($CI && isset($CI->session) && method_exists($CI->session, 'userdata'))
                    ? $CI->session->userdata($key)
                    : null;
            case 'option':
                return function_exists('get_option') ? get_option($key) : null;
            case 'env':
                $env = getenv($key);
                return $env !== false ? $env : null;
            case 'filter':
                if (array_key_exists($key, $this->runtimeFilters)) {
                    return $this->runtimeFilters[$key];
                }

                if ($input) {
                    $runtimePost = $fetchInput($input, 'post', 'runtime_filters');
                    if (is_array($runtimePost) && array_key_exists($key, $runtimePost)) {
                        return $runtimePost[$key];
                    }

                    $runtimeGet = $fetchInput($input, 'get', 'runtime_filters');
                    if (is_array($runtimeGet) && array_key_exists($key, $runtimeGet)) {
                        return $runtimeGet[$key];
                    }

                    $legacyPost = $fetchInput($input, 'post', 'filters');
                    if (is_array($legacyPost) && array_key_exists($key, $legacyPost)) {
                        return $legacyPost[$key];
                    }

                    $legacyGet = $fetchInput($input, 'get', 'filters');
                    if (is_array($legacyGet) && array_key_exists($key, $legacyGet)) {
                        return $legacyGet[$key];
                    }
                }

                if (isset($_POST['runtime_filters'][$key])) {
                    return $_POST['runtime_filters'][$key];
                }

                if (isset($_GET['runtime_filters'][$key])) {
                    return $_GET['runtime_filters'][$key];
                }

                if (isset($_REQUEST['filters'][$key])) {
                    return $_REQUEST['filters'][$key];
                }

                return array_key_exists($key, $this->runtimeFilters) ? $this->runtimeFilters[$key] : null;
        }

        if ($prefix !== null) {
            return null;
        }

        if ($input) {
            $value = $fetchInput($input, 'get', $key);
            if ($value === null) {
                $value = $fetchInput($input, 'post', $key);
            }
            if ($value !== null) {
                return $value;
            }
        }

        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }

        if ($CI && isset($CI->session) && method_exists($CI->session, 'userdata') && $CI->session->userdata($key) !== null) {
            return $CI->session->userdata($key);
        }

        if (function_exists('get_option')) {
            $option = get_option($key);
            if ($option !== null) {
                return $option;
            }
        }

        if (function_exists('getenv')) {
            $env = getenv($key);
            if ($env !== false) {
                return $env;
            }
        }

        return null;
    }

    private function normalize_condition_value_list($value, string $type): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $string = (string) $value;
            if ($string === '') {
                return [];
            }
            $items = preg_split('/[,|]/', $string);
        }

        $normalized = [];
        foreach ($items as $item) {
            if ($item === null) {
                continue;
            }

            if (is_string($item)) {
                $item = trim($item);
            }

            if ($item === '' && $item !== '0') {
                continue;
            }

            $casted = $this->cast_single_condition_value($item, $type);
            if ($casted === null) {
                continue;
            }

            $normalized[] = $casted;
        }

        return array_values(array_unique($normalized, SORT_REGULAR));
    }

    private function cast_single_condition_value($value, string $type)
    {
        if (is_array($value)) {
            return null;
        }

        switch (strtolower($type)) {
            case 'number':
                if ($value === '' || $value === null) {
                    return null;
                }
                return is_numeric($value) ? (float) $value : null;
            case 'date':
                if ($value === '' || $value === null) {
                    return null;
                }
                $timestamp = strtotime((string) $value);
                return $timestamp ? date('Y-m-d', $timestamp) : (string) $value;
            default:
                if ($value === null) {
                    return null;
                }
                return (string) $value;
        }
    }

    private function format_value($value, array $column)
    {
        $currencyDisplay = $column['currency_display'] ?? $column['currency'] ?? 'inherit';
        $aggregate       = strtoupper($column['aggregate_function'] ?? '');

        if ($aggregate === 'VALUE') {
            if ($currencyDisplay === 'show' && is_numeric($value)) {
                return $this->format_currency_value($value);
            }

            return $value === null ? '-' : (string) $value;
        }

        if ($value === null) {
            return '-';
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        if ($currencyDisplay === 'show') {
            return $this->format_currency_value($value);
        }

        $decimalPlaces = $column['decimal_places'] ?? null;
        if ($decimalPlaces === null) {
            return (intval($value) == $value) ? (string) intval($value) : number_format((float) $value, 2);
        }

        $decimalPlaces = max(0, (int) $decimalPlaces);

        return number_format((float) $value, $decimalPlaces);
    }

    private function format_currency_value($value): string
    {
        if (! is_numeric($value)) {
            return (string) $value;
        }

        if (! function_exists('app_format_money') && function_exists('get_instance')) {
            $CI = get_instance();
            if ($CI && method_exists($CI, 'load')) {
                $CI->load->helper('sales');
            }
        }

        if (function_exists('get_base_currency') && function_exists('app_format_money')) {
            $currency = get_base_currency();
            if ($currency) {
                return app_format_money((float) $value, $currency);
            }
        }

        return number_format((float) $value, 2);
    }

    private function sanitize_join_table($table): ?string
    {
        $table = trim((string) $table);

        if ($table === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return null;
        }

        return $table;
    }

    private function sanitize_join_alias($alias): ?string
    {
        $alias = trim((string) $alias);
        if ($alias === '') {
            return null;
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $alias)) {
            return null;
        }

        return $alias;
    }

    private function sanitize_join_type($type): string
    {
        $type = strtolower(trim((string) $type));

        $allowed = ['inner', 'left', 'right', 'left outer', 'right outer'];
        if (! in_array($type, $allowed, true)) {
            return 'inner';
        }

        return $type;
    }

    private function sanitize_join_on($clause): ?string
    {
        $clause = trim((string) $clause);
        if ($clause === '') {
            return null;
        }

        if (! preg_match('/^[A-Za-z0-9_\\.\\s=<>+-]+$/', $clause)) {
            return null;
        }

        return preg_replace('/\\s+/', ' ', $clause);
    }

    private function apply_joins($builder, array $joins): void
    {
        $applied = [];

        foreach ($joins as $join) {
            if (! is_array($join)) {
                continue;
            }

            $table   = $this->sanitize_join_table($join['table_name'] ?? '');
            $alias   = $this->sanitize_join_alias($join['alias'] ?? '');
            $type    = $this->sanitize_join_type($join['type'] ?? '');
            $on      = $this->sanitize_join_on($join['on'] ?? '');

            if (! $table || ! $on) {
                continue;
            }

            $reference = $this->build_join_reference($table, $alias);
            if (! $reference) {
                continue;
            }

            $key = md5($reference . '|' . $on . '|' . $type);
            if (isset($applied[$key])) {
                continue;
            }

            $builder->join($reference, $on, $type);
            $applied[$key] = true;
        }
    }

    private function build_join_reference(string $table, ?string $alias): ?string
    {
        $resolved = $this->resolve_table_name($table);
        if (! $resolved) {
            return null;
        }

        $tableIdentifier = $resolved;

        if ($alias) {
            return $tableIdentifier . ' AS ' . $alias;
        }

        return $tableIdentifier;
    }

    private function passes_template_filters(array $row, array $columns, array $filters): bool
    {
        $rules     = $filters['rules'] ?? [];
        $matchType = strtolower($filters['match_type'] ?? 'and');

        if (empty($rules)) {
            return true;
        }

        $results = [];

        foreach ($rules as $rule) {
            $columnId = $rule['id'] ?? '';
            $columnIndex = (int) str_replace('col_', '', $columnId);
            $value       = $row[$columnId] ?? null;
            $definition  = $columns[$columnIndex] ?? [];

            $results[] = $this->evaluate_filter_rule($value, $definition, $rule);
        }

        if ($matchType === 'or') {
            return in_array(true, $results, true);
        }

        return ! in_array(false, $results, true);
    }

    private function evaluate_filter_rule($value, array $definition, array $rule): bool
    {
        $operator = $rule['operator'] ?? 'equal';
        $ruleValue = $rule['value'] ?? '';
        $aggregate = strtoupper($definition['aggregate'] ?? $definition['aggregate_function'] ?? '');
        $isNumeric = in_array($aggregate, ['SUM', 'COUNT', 'AVG', 'MIN', 'MAX', 'FORMULA'], true);

        if ($value === null || $value === '') {
            $value = $isNumeric ? 0 : '';
        }

        if ($isNumeric) {
            $value = (float) $value;
        } else {
            $value = strtolower((string) $value);
            if (is_string($ruleValue)) {
                $ruleValue = strtolower((string) $ruleValue);
            }
        }

        switch ($operator) {
            case 'equal':
                return $isNumeric ? ((float) $value === (float) $ruleValue) : ($value === strtolower((string) $ruleValue));
            case 'not_equal':
                return $isNumeric ? ((float) $value !== (float) $ruleValue) : ($value !== strtolower((string) $ruleValue));
            case 'greater':
                return (float) $value > (float) $ruleValue;
            case 'greater_or_equal':
                return (float) $value >= (float) $ruleValue;
            case 'less':
                return (float) $value < (float) $ruleValue;
            case 'less_or_equal':
                return (float) $value <= (float) $ruleValue;
            case 'between':
                if (is_array($ruleValue) && count($ruleValue) === 2) {
                    return (float) $value >= (float) $ruleValue[0] && (float) $value <= (float) $ruleValue[1];
                }
                return true;
            case 'not_between':
                if (is_array($ruleValue) && count($ruleValue) === 2) {
                    return ! ((float) $value >= (float) $ruleValue[0] && (float) $value <= (float) $ruleValue[1]);
                }
                return true;
            case 'contains':
                return strpos((string) $value, strtolower((string) $ruleValue)) !== false;
            case 'not_contains':
                return strpos((string) $value, strtolower((string) $ruleValue)) === false;
            case 'begins_with':
                $needle = strtolower((string) $ruleValue);
                if ($needle === '') {
                    return true;
                }
                return strpos((string) $value, $needle) === 0;
            case 'ends_with':
                $needle = strtolower((string) $ruleValue);
                if ($needle === '') {
                    return true;
                }
                $haystack = (string) $value;
                return substr($haystack, -strlen($needle)) === $needle;
            case 'in':
                return is_array($ruleValue) ? in_array($value, $ruleValue, true) : ((string) $value === (string) $ruleValue);
            case 'not_in':
                return is_array($ruleValue) ? ! in_array($value, $ruleValue, true) : ((string) $value !== (string) $ruleValue);
            case 'is_empty':
                return $value === '' || $value === null;
            case 'is_not_empty':
                return $value !== '' && $value !== null;
            default:
                return true;
        }
    }

    private function parse_conditions($source): array
    {
        if (empty($source)) {
            return [];
        }

        if (is_string($source)) {
            $decoded = json_decode($source, true);
            if (! is_array($decoded)) {
                return [];
            }
            return $this->filter_condition_rows($decoded);
        }

        if (is_array($source) && isset($source['field']) && is_array($source['field'])) {
            return $this->prepare_conditions_payload($source);
        }

        if (is_array($source)) {
            return $this->filter_condition_rows($source);
        }

        return [];
    }

    private function parse_joins($source): array
    {
        if (empty($source)) {
            return [];
        }

        if (is_string($source)) {
            $decoded = json_decode($source, true);
            if (! is_array($decoded)) {
                return [];
            }
            return $this->filter_join_rows($decoded);
        }

        if (is_array($source) && isset($source['table_name']) && is_array($source['table_name'])) {
            return $this->prepare_joins_payload($source);
        }

        if (is_array($source)) {
            return $this->filter_join_rows($source);
        }

        return [];
    }

    private function filter_condition_rows(array $conditions): array
    {
        $filtered = [];
        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                continue;
            }
            $field = trim((string) ($condition['field'] ?? ''));
            $operator = strtoupper(trim((string) ($condition['operator'] ?? '')));
            $value = $condition['value'] ?? '';
            $type = strtolower(trim((string) ($condition['type'] ?? 'string')));

            if ($field === '' || $operator === '' || $value === '') {
                continue;
            }

            if (! in_array($operator, ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'IN', 'NOT IN'], true)) {
                continue;
            }

            if (! in_array($type, ['string', 'number', 'date'], true)) {
                $type = 'string';
            }

            $filtered[] = [
                'field'    => $field,
                'operator' => $operator,
                'value'    => $value,
                'type'     => $type,
            ];
        }

        return $filtered;
    }

    private function filter_join_rows(array $joins): array
    {
        $filtered = [];
        foreach ($joins as $join) {
            if (! is_array($join)) {
                continue;
            }

            $table = $this->sanitize_join_table($join['table_name'] ?? '');
            $on    = $this->sanitize_join_on($join['on'] ?? '');
            $type  = $this->sanitize_join_type($join['type'] ?? '');
            $alias = $this->sanitize_join_alias($join['alias'] ?? '');

            if (! $table || ! $on) {
                continue;
            }

            $filtered[] = [
                'table_name' => $table,
                'alias'      => $alias,
                'on'         => $on,
                'type'       => $type,
            ];
        }

        return $filtered;
    }

    private function default_column_label(string $aggregate, ?string $columnName): string
    {
        $aggregate = $this->normalize_aggregate($aggregate);
        if ($aggregate === 'FORMULA') {
            return ccx_lang('ccx_template_formula_label', 'Computed Metric');
        }

        $aggregateLabel = ccx_lang($this->aggregateOptions[$aggregate] ?? '', ucfirst(strtolower($aggregate)));

        $columnName = trim((string) $columnName);
        if ($aggregate === 'COUNT' || $columnName === '') {
            return $aggregateLabel;
        }

        $clean = str_replace(['`', '.', '_'], [' ', ' ', ' '], $columnName);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        if ($clean === '') {
            return $aggregateLabel;
        }

        if ($aggregate === 'VALUE') {
            return ucwords($clean);
        }

        return trim($aggregateLabel . ' ' . ucwords($clean));
    }

    public function get_export_bundle(?array $templateIds = null): array
    {
        $this->ensure_schema();

        if ($templateIds !== null) {
            $templateIds = array_values(array_unique(array_filter(array_map('intval', $templateIds), static function ($id) {
                return $id > 0;
            })));

            if (empty($templateIds)) {
                return [
                    'meta'      => [
                        'module'       => CCX_MODULE_NAME,
                        'version'      => defined('CCX_MODULE_VERSION') ? CCX_MODULE_VERSION : '1.0.0',
                        'generated_at' => date(DATE_ATOM),
                    ],
                    'templates' => [],
                    'sections'  => [],
                ];
            }
        }

        $templateQuery = $this->db
            ->order_by('id', 'ASC');

        if ($templateIds !== null) {
            $templateQuery->where_in('id', $templateIds);
        }

        $templateRows = $templateQuery
            ->get(db_prefix() . 'ccx_report_templates')
            ->result_array() ?? [];

        $templateExports = [];

        foreach ($templateRows as $row) {
            $templateId = (int) ($row['id'] ?? 0);
            if ($templateId <= 0) {
                continue;
            }

            $columnRows = $this->db
                ->where('template_id', $templateId)
                ->order_by('position', 'ASC')
                ->get(db_prefix() . 'ccx_report_template_columns')
                ->result_array() ?? [];

            $columns = [];
            foreach ($columnRows as $columnRow) {
                unset($columnRow['id'], $columnRow['template_id']);
                $columns[] = $columnRow;
            }

            $pageRows = $this->db
                ->where('template_id', $templateId)
                ->order_by('page_key', 'ASC')
                ->get(db_prefix() . 'ccx_report_template_pages')
                ->result_array() ?? [];

            $pages = [];
            foreach ($pageRows as $pageRow) {
                unset($pageRow['id'], $pageRow['template_id']);
                $pages[] = $pageRow;
            }

            $record = $row;
            unset($record['id']);

            $templateExports[] = [
                'source_id' => $templateId,
                'record'    => $record,
                'columns'   => $columns,
                'pages'     => $pages,
            ];
        }

        $exportedTemplateSourceIds = array_map(static function ($item) {
            return (int) ($item['source_id'] ?? 0);
        }, $templateExports);

        $exportedTemplateSourceIds = array_values(array_filter($exportedTemplateSourceIds, static function ($id) {
            return $id > 0;
        }));

        $sectionIds = null;

        $linkQuery = $this->db
            ->order_by('display_order', 'ASC');

        if ($templateIds !== null) {
            if (empty($templateExports)) {
                $linkRows = [];
            } else {
                if (! empty($exportedTemplateSourceIds)) {
                    $linkRows = $linkQuery
                        ->where_in('template_id', $exportedTemplateSourceIds)
                        ->get(db_prefix() . 'ccx_report_section_templates')
                        ->result_array() ?? [];

                    $sectionIds = array_unique(array_map('intval', array_column($linkRows, 'section_id')));
                } else {
                    $linkRows = [];
                    $sectionIds = [];
                }
            }
        } else {
            $linkRows = $linkQuery
                ->get(db_prefix() . 'ccx_report_section_templates')
                ->result_array() ?? [];
        }

        $sectionQuery = $this->db
            ->order_by('display_order', 'ASC')
            ->order_by('name', 'ASC');

        if ($sectionIds !== null) {
            if (empty($sectionIds)) {
                $sectionRows = [];
            } else {
                $sectionQuery->where_in('id', $sectionIds);
                $sectionRows = $sectionQuery
                    ->get(db_prefix() . 'ccx_report_sections')
                    ->result_array() ?? [];
            }
        } else {
            $sectionRows = $sectionQuery
                ->get(db_prefix() . 'ccx_report_sections')
                ->result_array() ?? [];
        }

        $linksBySection = [];
        foreach ($linkRows as $link) {
            $sectionId = (int) ($link['section_id'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }

            if (! isset($linksBySection[$sectionId])) {
                $linksBySection[$sectionId] = [];
            }

            $templateId = (int) ($link['template_id'] ?? 0);
            if ($templateIds !== null && ! in_array($templateId, $exportedTemplateSourceIds, true)) {
                continue;
            }

            $linksBySection[$sectionId][] = [
                'template_id'   => $templateId,
                'display_order' => (int) ($link['display_order'] ?? 0),
            ];
        }

        $sectionExports = [];
        foreach ($sectionRows as $sectionRow) {
            $sectionId = (int) ($sectionRow['id'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }

            $record = $sectionRow;
            unset($record['id']);

            $sectionExports[] = [
                'source_id' => $sectionId,
                'record'    => $record,
                'templates' => $linksBySection[$sectionId] ?? [],
            ];
        }

        return [
            'meta'      => [
                'module'       => CCX_MODULE_NAME,
                'version'      => defined('CCX_MODULE_VERSION') ? CCX_MODULE_VERSION : '1.0.0',
                'generated_at' => date(DATE_ATOM),
            ],
            'templates' => $templateExports,
            'sections'  => $sectionExports,
        ];
    }

    public function import_bundle(array $bundle): array
    {
        $this->ensure_schema();

        $templatesInput = $bundle['templates'] ?? [];
        $sectionsInput  = $bundle['sections'] ?? [];

        if (! is_array($templatesInput) || (! is_array($sectionsInput) && ! empty($sectionsInput))) {
            return ['success' => false];
        }

        if (! is_array($sectionsInput)) {
            $sectionsInput = [];
        }

        $templateMap       = [];
        $templatesImported = 0;
        $sectionsImported  = 0;
        $now               = date('Y-m-d H:i:s');

        $this->db->trans_start();

        foreach ($templatesInput as $templateItem) {
            if (! is_array($templateItem)) {
                continue;
            }

            $record = $templateItem['record'] ?? [];
            if (! is_array($record)) {
                continue;
            }

            $type = $this->normalize_template_type($record['type'] ?? null);
            $name = trim((string) ($record['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $sqlQuery = $type === self::TEMPLATE_TYPE_SQL ? (string) ($record['sql_query'] ?? '') : null;
            if ($type === self::TEMPLATE_TYPE_SQL && trim($sqlQuery) === '') {
                continue;
            }

            $description = trim((string) ($record['description'] ?? ''));
            $isActive    = isset($record['is_active']) && (int) $record['is_active'] === 0 ? 0 : 1;
            $rawFilters  = $record['filters'] ?? null;
            if ($rawFilters !== null && trim((string) $rawFilters) === '') {
                $rawFilters = null;
            }

            $templateRow = [
                'name'        => $name,
                'description' => $description !== '' ? $description : null,
                'type'        => $type,
                'is_active'   => $type === self::TEMPLATE_TYPE_SQL ? $isActive : 1,
                'sql_query'   => $type === self::TEMPLATE_TYPE_SQL ? $sqlQuery : null,
                'filters'     => $rawFilters,
                'created_at'  => $now,
                'updated_at'  => null,
            ];

            $this->db->insert(db_prefix() . 'ccx_report_templates', $templateRow);
            $newTemplateId = (int) $this->db->insert_id();
            if ($newTemplateId <= 0) {
                continue;
            }

            $templatesImported++;

            $sourceId = isset($templateItem['source_id']) ? (int) $templateItem['source_id'] : 0;
            if ($sourceId > 0) {
                $templateMap[$sourceId] = $newTemplateId;
            }

            if ($type === self::TEMPLATE_TYPE_SQL) {
                continue;
            }

            if ($type === self::TEMPLATE_TYPE_DYNAMIC) {
                $pagesInput = $templateItem['pages'] ?? [];
                if (is_array($pagesInput)) {
                    foreach ($pagesInput as $pageRow) {
                        if (! is_array($pageRow)) {
                            continue;
                        }

                        $pageKey = strtolower(trim((string) ($pageRow['page_key'] ?? '')));
                        if (! in_array($pageKey, ['main', 'sub'], true)) {
                            continue;
                        }

                        $sqlQuery = isset($pageRow['sql_query']) ? trim((string) $pageRow['sql_query']) : '';
                        $htmlContent = isset($pageRow['html_content']) ? (string) $pageRow['html_content'] : '';
                        $filtersValue = $pageRow['filters'] ?? null;

                        if (is_string($filtersValue) && trim($filtersValue) === '') {
                            $filtersValue = null;
                        }

                        $this->db->insert(db_prefix() . 'ccx_report_template_pages', [
                            'template_id'  => $newTemplateId,
                            'page_key'     => $pageKey,
                            'sql_query'    => $sqlQuery !== '' ? $sqlQuery : null,
                            'html_content' => $htmlContent !== '' ? $htmlContent : null,
                            'filters'      => $filtersValue,
                            'created_at'   => $now,
                            'updated_at'   => null,
                        ]);
                    }
                }

                continue;
            }

            $columns = $templateItem['columns'] ?? [];
            if (! is_array($columns) || empty($columns)) {
                continue;
            }

            $positionFallback = 1;
            foreach ($columns as $column) {
                if (! is_array($column)) {
                    $positionFallback++;
                    continue;
                }

                $sanitized = $this->sanitize_import_column($column, $positionFallback);
                if ($sanitized === null) {
                    $positionFallback++;
                    continue;
                }

                $sanitized['template_id'] = $newTemplateId;
                if (! isset($sanitized['created_at'])) {
                    $sanitized['created_at'] = $now;
                }
                if (! isset($sanitized['updated_at'])) {
                    $sanitized['updated_at'] = null;
                }

                $this->db->insert(db_prefix() . 'ccx_report_template_columns', $sanitized);
                $positionFallback = ((int) $sanitized['position']) + 1;
            }
        }

        foreach ($sectionsInput as $sectionItem) {
            if (! is_array($sectionItem)) {
                continue;
            }

            $record = $sectionItem['record'] ?? [];
            if (! is_array($record)) {
                continue;
            }

            $name = trim((string) ($record['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $description  = trim((string) ($record['description'] ?? ''));
            $displayOrder = isset($record['display_order']) ? (int) $record['display_order'] : 0;

            $sectionRow = [
                'name'          => $name,
                'description'   => $description !== '' ? $description : null,
                'display_order' => $displayOrder,
                'created_at'    => $now,
                'updated_at'    => null,
            ];

            $this->db->insert(db_prefix() . 'ccx_report_sections', $sectionRow);
            $newSectionId = (int) $this->db->insert_id();
            if ($newSectionId <= 0) {
                continue;
            }

            $sectionsImported++;

            $links = $sectionItem['templates'] ?? [];
            if (! is_array($links) || empty($links)) {
                continue;
            }

            foreach ($links as $link) {
                if (! is_array($link)) {
                    continue;
                }

                $sourceTemplateId = isset($link['template_id']) ? (int) $link['template_id'] : 0;
                if ($sourceTemplateId <= 0 || ! isset($templateMap[$sourceTemplateId])) {
                    continue;
                }

                $this->db->insert(db_prefix() . 'ccx_report_section_templates', [
                    'section_id'    => $newSectionId,
                    'template_id'   => $templateMap[$sourceTemplateId],
                    'display_order' => isset($link['display_order']) ? (int) $link['display_order'] : 0,
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false];
        }

        return [
            'success'   => true,
            'templates' => $templatesImported,
            'sections'  => $sectionsImported,
        ];
    }

    private function sanitize_import_column(array $column, int $positionFallback): ?array
    {
        $mode = ($column['mode'] ?? 'simple') === 'formula' ? 'formula' : 'simple';

        $label = trim((string) ($column['label'] ?? ''));
        if ($label === '') {
            return null;
        }

        $aggregate = strtoupper(trim((string) ($column['aggregate_function'] ?? '')));
        if ($aggregate === '') {
            $aggregate = 'SUM';
        }

        $tableName = trim((string) ($column['table_name'] ?? ''));
        if ($mode !== 'formula') {
            if ($tableName === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
                return null;
            }
        }

        $position = isset($column['position']) ? (int) $column['position'] : $positionFallback;
        if ($position <= 0) {
            $position = $positionFallback;
        }

        $allowedKeys = [
            'position',
            'label',
            'aggregate_function',
            'table_name',
            'column_name',
            'conditions',
            'decimal_places',
            'created_at',
            'updated_at',
            'mode',
            'currency_display',
            'date_filter_field',
            'joins',
            'formula_sources',
            'formula_expression',
            'role',
        ];

        $sanitized = [];
        foreach ($allowedKeys as $key) {
            if (! array_key_exists($key, $column)) {
                continue;
            }

            $value = $column[$key];

            if (in_array($key, ['conditions', 'joins', 'formula_sources', 'formula_expression', 'column_name', 'date_filter_field'], true)) {
                if ($value === null) {
                    $value = null;
                } else {
                    $value = trim((string) $value);
                    if ($value === '') {
                        $value = null;
                    }
                }
            }

            if ($key === 'decimal_places' && $value !== null && $value !== '') {
                $value = (int) $value;
            }

            if ($key === 'currency_display') {
                $value = $this->sanitize_currency_display($value);
            }

            if ($key === 'aggregate_function') {
                $value = strtoupper(trim((string) $value));
            }

            if ($key === 'mode') {
                $value = $mode;
            }

            if ($key === 'position') {
                $value = $position;
            }

            if ($key === 'table_name' && $mode === 'formula') {
                $value = null;
            }

            $sanitized[$key] = $value;
        }

        $sanitized['position'] = $position;
        $sanitized['label']    = $label;
        $sanitized['mode']     = $mode;
        $sanitized['aggregate_function'] = $sanitized['aggregate_function'] ?? $aggregate;
        $sanitized['role']     = $this->sanitize_column_role($sanitized['role'] ?? $column['role'] ?? 'metric');

        if ($mode === 'formula') {
            $sanitized['table_name']         = null;
            $sanitized['column_name']        = null;
            $sanitized['decimal_places']     = isset($sanitized['decimal_places']) ? (int) $sanitized['decimal_places'] : null;
            $sanitized['formula_expression'] = $sanitized['formula_expression'] ?? null;
        } else {
            $columnName = isset($sanitized['column_name']) ? $sanitized['column_name'] : (isset($column['column_name']) ? trim((string) $column['column_name']) : '');
            if ($aggregate !== 'COUNT' && $columnName === '') {
                return null;
            }
            $sanitized['table_name']  = $tableName;
            $sanitized['column_name'] = $columnName !== '' ? $columnName : null;
        }

        return $sanitized;
    }

    private function get_templates_map(array $templateIds): array
    {
        if (empty($templateIds)) {
            return [];
        }

        $rows = $this->db
            ->where_in('id', $templateIds)
            ->get(db_prefix() . 'ccx_report_templates')
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }
}
