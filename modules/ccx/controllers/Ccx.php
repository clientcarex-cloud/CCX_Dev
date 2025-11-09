<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $hasAccess = is_admin()
            || staff_can('view', 'ccx_reports')
            || staff_can('view_own', 'ccx_reports')
            || staff_can('create', 'ccx_reports')
            || staff_can('edit', 'ccx_reports')
            || staff_can('delete', 'ccx_reports')
            || staff_can('view', 'ccx_templates')
            || staff_can('view_own', 'ccx_templates')
            || staff_can('create', 'ccx_templates')
            || staff_can('edit', 'ccx_templates')
            || staff_can('delete', 'ccx_templates')
            || staff_can('view', 'ccx_sections')
            || staff_can('view_own', 'ccx_sections')
            || staff_can('create', 'ccx_sections')
            || staff_can('edit', 'ccx_sections')
            || staff_can('delete', 'ccx_sections')
            || staff_can('view', 'ccx_import_export')
            || staff_can('create', 'ccx_import_export');

        if (! $hasAccess) {
            access_denied('CCX');
        }

        $this->load->model('ccx/ccx_model');
    }

    public function index()
    {
        redirect(admin_url('ccx/reports'));
    }

    public function reports()
    {
        if (! (staff_can('view', 'ccx_reports') || staff_can('view_own', 'ccx_reports') || is_admin())) {
            access_denied('ccx_reports');
        }

        $data['title']    = ccx_lang('ccx_reports_page_title', 'Reports');
        $data['sections'] = $this->ccx_model->get_sections_overview();

        $this->load->view('ccx/reports/index', $data);
    }

    public function report($id)
    {
        if (! (staff_can('view', 'ccx_reports') || staff_can('view_own', 'ccx_reports') || is_admin())) {
            access_denied('ccx_reports');
        }

        $id = (int) $id;
        if ($id <= 0) {
            show_404();
        }

        $template = $this->ccx_model->get_template($id);
        if (! $template) {
            set_alert('warning', ccx_lang('ccx_template_not_found', 'Template not found.'));
            redirect(admin_url('ccx/reports'));
        }

        if ($this->ccx_model->is_sql_template($template)) {
            $filterDefinitions = $this->decode_saved_filters($template['filters'] ?? null);
            $filterContext     = $this->build_filter_context($filterDefinitions);

            $result = [
                'columns'   => [],
                'rows'      => [],
                'row_limit' => null,
            ];
            $error = null;

            if ($filterContext['has_errors']) {
                $error = ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.');
            } else {
                [$result, $error] = $this->ccx_model->run_sql_template_query($template, $filterContext['values']);
                if ($error !== null) {
                    set_alert('danger', $error);
                }
            }

            $data['title']               = ccx_lang('ccx_reports_sql_view_title', 'View Report');
            $data['template']            = $template;
            $data['columns']             = $result['columns'] ?? [];
            $data['rows']                = $result['rows'] ?? [];
            $data['row_limit']           = $result['row_limit'] ?? null;
            $data['query_error']         = $error;
            $data['filters']             = $filterContext['filters'];
            $data['filters_applied']     = $filterContext['values'];
            $data['filters_has_errors']  = $filterContext['has_errors'];
            $data['filters_submitted']   = $filterContext['submitted'];
            $data['exportParams']        = $this->input->get() ?? [];

            $this->load->view('ccx/reports/sql', $data);

            return;
        }

        if ($this->ccx_model->is_dynamic_template($template)) {
            $pageLabels = [
                'main' => ccx_lang('ccx_template_dynamic_page_main', 'Main Page'),
                'sub'  => ccx_lang('ccx_template_dynamic_page_sub', 'Sub Page'),
            ];

            $pagesData   = $this->ccx_model->get_dynamic_template_pages($template['id']);
            $activePage  = strtolower((string) $this->input->get('page'));
            if (! in_array($activePage, array_keys($pageLabels), true)) {
                $activePage = 'main';
            }

            $pagePayloads = [];
            foreach ($pageLabels as $pageKey => $label) {
                $definition = $pagesData[$pageKey] ?? [
                    'sql_query'    => '',
                    'html_content' => '',
                    'filters'      => null,
                ];

                $filtersDefined = $this->decode_saved_filters($definition['filters'] ?? null);
                $context        = $this->build_filter_context($filtersDefined, null, null, 'filters_' . $pageKey);

                $resultData = [
                    'columns'   => [],
                    'rows'      => [],
                    'row_limit' => null,
                ];
                $queryError = null;

                if ($context['has_errors']) {
                    $queryError = ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.');
                } elseif (trim((string) ($definition['sql_query'] ?? '')) !== '') {
                    [$result, $error] = $this->ccx_model->run_sql_template_query(
                        ['sql_query' => $definition['sql_query']],
                        $context['values']
                    );
                    $resultData['columns']   = $result['columns'] ?? [];
                    $resultData['rows']      = $result['rows'] ?? [];
                    $resultData['row_limit'] = $result['row_limit'] ?? null;
                    $queryError              = $error;
                }

                $pagePayloads[$pageKey] = [
                    'label'              => $label,
                    'sql_query'          => $definition['sql_query'] ?? '',
                    'html_content'       => $definition['html_content'] ?? '',
                    'filters'            => $context['filters'],
                    'filters_applied'    => $context['values'],
                    'filters_has_errors' => $context['has_errors'],
                    'filters_submitted'  => $context['submitted'],
                    'result'             => $resultData,
                    'query_error'        => $queryError,
                ];
            }

            $data['title']       = ccx_lang('ccx_reports_view_page_title', 'View Report');
            $data['template']    = $template;
            $data['pages']       = $pagePayloads;
            $data['pageLabels']  = $pageLabels;
            $data['activePage']  = $activePage;
            $data['exportParams'] = $this->input->get() ?? [];

            if (! empty($pagePayloads[$activePage]['query_error'])) {
                set_alert('danger', $pagePayloads[$activePage]['query_error']);
            }

            $this->load->view('ccx/reports/dynamic', $data);

            return;
        }

        $filterDefinitions = $this->decode_saved_filters($template['filters'] ?? null);
        $filterContext     = $this->build_filter_context($filterDefinitions);

        if (! empty($filterContext['values'])) {
            $_GET['filters'] = $filterContext['values'];
        }

        $columns    = $this->ccx_model->get_template_columns($template['id']);
        $tableSlug  = 'ccx-template-' . $template['id'];
        $table      = $this->ccx_model->ensure_template_table($template, $columns);

        $data['title']      = ccx_lang('ccx_reports_view_page_title', 'View Report');
        $data['template']   = $template;
        $data['columns']    = $columns;
        $data['tableSlug']  = $tableSlug;
        $data['table']      = $table;
        $data['filters']    = $filterContext['filters'];
        $data['filters_applied'] = $filterContext['values'];
        $data['filters_has_errors'] = $filterContext['has_errors'];
        $data['filters_submitted']  = $filterContext['submitted'];
        $data['exportParams'] = $this->input->get() ?? [];

        $this->load->view('ccx/reports/detail', $data);
    }

    public function report_export($id)
    {
        if (! (staff_can('view', 'ccx_reports') || staff_can('view_own', 'ccx_reports') || is_admin())) {
            access_denied('ccx_reports');
        }

        if (! (is_admin() || staff_can('export', 'ccx_reports'))) {
            access_denied('ccx_reports_export');
        }

        $id = (int) $id;
        if ($id <= 0) {
            show_404();
        }

        $template = $this->ccx_model->get_template($id);
        if (! $template) {
            set_alert('warning', ccx_lang('ccx_template_not_found', 'Template not found.'));
            redirect(admin_url('ccx/reports'));
        }

        if ($this->ccx_model->is_sql_template($template)) {
            $this->export_sql_template($template);

            return;
        }

        if ($this->ccx_model->is_dynamic_template($template)) {
            $this->export_dynamic_template($template);

            return;
        }

        $this->export_smart_template($template);
    }

    public function report_table($id)
    {
        if (! $this->input->is_ajax_request()) {
            show_error('No direct script access allowed');
        }

        if (! (staff_can('view', 'ccx_reports') || staff_can('view_own', 'ccx_reports') || is_admin())) {
            access_denied('ccx_reports');
        }

        $id = (int) $id;
        if ($id <= 0) {
            show_404();
        }

        $template = $this->ccx_model->get_template($id);
        if (! $template) {
            show_404();
        }

        if ($this->ccx_model->is_sql_template($template) || $this->ccx_model->is_dynamic_template($template)) {
            show_404();
        }

        $options = [
            'draw'    => (int) ($this->input->post('draw') ?? 0),
            'search'  => trim($this->input->post('search')['value'] ?? ''),
            'filters' => $this->input->post('filters') ?? [],
            'start'   => (int) ($this->input->post('start') ?? 0),
            'length'  => $this->input->post('length') !== null ? (int) $this->input->post('length') : null,
            'date_from' => $this->input->post('date_from'),
            'date_to'   => $this->input->post('date_to'),
            'runtime_filters' => $this->input->post('runtime_filters') ?? [],
        ];

        $filterDefinitions = $this->decode_saved_filters($template['filters'] ?? null);
        $runtimeFiltersInput = $options['runtime_filters'];
        if (! is_array($runtimeFiltersInput)) {
            $runtimeFiltersInput = [];
        }

        $runtimeContext = $this->build_filter_context($filterDefinitions, $runtimeFiltersInput, true);
        if ($runtimeContext['has_errors']) {
            $empty = [
                'draw'                 => $options['draw'],
                'recordsTotal'         => 0,
                'recordsFiltered'      => 0,
                'iTotalRecords'        => 0,
                'iTotalDisplayRecords' => 0,
                'aaData'               => [],
            ];
            echo json_encode($empty);
            return;
        }

        $options['runtime_filters'] = $runtimeContext['values'];
        $_POST['runtime_filters'] = $runtimeContext['values'];

        try {
            $response = $this->ccx_model->get_template_table_data($id, $options);
            $json     = json_encode($response);
            if ($json === false) {
                log_message('error', 'CCX report_table JSON encoding failed: ' . json_last_error_msg());
                $json = json_encode([
                    'draw'                 => (int) ($options['draw'] ?? 0),
                    'recordsTotal'         => 0,
                    'recordsFiltered'      => 0,
                    'iTotalRecords'        => 0,
                    'iTotalDisplayRecords' => 0,
                    'aaData'               => [],
                    'error'                => 'Encoding failure',
                ]);
            }
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        } catch (Throwable $e) {
            log_message('error', 'CCX report_table failure: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if (method_exists($this, 'output')) {
                $this->output->set_status_header(500);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            }
            echo json_encode([
                'error' => 'Unable to render report dataset.',
            ]);
        }
        exit;
    }

    public function templates()
    {
        if (! (staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin())) {
            access_denied('ccx_templates');
        }

        $data['title']     = ccx_lang('ccx_templates_page_title', 'Report Templates');
        $data['templates'] = $this->ccx_model->get_templates();

        $this->load->view('ccx/templates/list', $data);
    }

    public function template($id = null)
    {
        $id = $id !== null ? (int) $id : null;
        $isPost = $this->input->method() === 'post';
        $canViewTemplates = staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin();

        if ($isPost) {
            if ($id) {
                if (! (staff_can('edit', 'ccx_templates') || is_admin())) {
                    access_denied('ccx_templates');
                }
            } else {
                if (! (staff_can('create', 'ccx_templates') || is_admin())) {
                    access_denied('ccx_templates');
                }
            }
        } else {
            if ($id) {
                if (! $canViewTemplates) {
                    access_denied('ccx_templates');
                }
            } else {
                if (! ($canViewTemplates || staff_can('create', 'ccx_templates') || is_admin())) {
                    access_denied('ccx_templates');
                }
            }
        }

        if ($isPost) {
            $template   = $this->input->post('template') ?? [];
            $type       = strtolower(trim((string) ($template['type'] ?? 'smart')));
            if (! in_array($type, ['smart', 'sql', 'dynamic'], true)) {
                $type = 'smart';
            }
            $template['type'] = $type;

            $filtersJsonInput = (string) $this->input->post('filters_json');
            $filtersJsonInput  = '';
            $filtersJsonStored = null;
            $filterErrors      = [];

            if ($type !== 'dynamic') {
                $filtersJsonInput = (string) $this->input->post('filters_json');
                [$filters, $filterErrors] = $this->parse_filters_json($filtersJsonInput);
                $filtersJsonStored = ! empty($filters) ? json_encode($filters, JSON_UNESCAPED_UNICODE) : null;
            }

            $template['filters'] = $type !== 'dynamic' ? $filtersJsonStored : null;

            $normalizedQuery = '';
            if ($type === 'sql') {
                $rawQuery = (string) $this->input->post('sql_query');
                $normalizedQuery = html_entity_decode($rawQuery, ENT_QUOTES | ENT_HTML5);
                $normalizedQuery = str_replace(["\r\n", "\r"], "\n", $normalizedQuery);
            }

            if ($type !== 'dynamic' && ! empty($filterErrors)) {
                set_alert('danger', implode('<br>', $filterErrors));
                $this->session->set_flashdata('ccx_template_filters_json', $filtersJsonInput);
                $this->session->set_flashdata('ccx_template_selected_type', $type);
                if ($type === 'sql') {
                    $this->session->set_flashdata('ccx_template_sql_query', $normalizedQuery);
                }
                redirect(admin_url('ccx/template' . ($id ? '/' . $id : '')));
            }

            if ($type === 'sql') {
                if (trim((string) ($template['name'] ?? '')) === '' || trim($normalizedQuery) === '') {
                    set_alert('danger', ccx_lang('ccx_template_sql_required', 'Template name and SQL query are required.'));
                    $this->session->set_flashdata('ccx_template_filters_json', $filtersJsonInput);
                    $this->session->set_flashdata('ccx_template_selected_type', 'sql');
                    $this->session->set_flashdata('ccx_template_sql_query', $normalizedQuery);
                    redirect(admin_url('ccx/template' . ($id ? '/' . $id : '')));
                }

                if (! $this->ccx_model->is_safe_sql_query($normalizedQuery)) {
                    set_alert('danger', ccx_lang('ccx_template_sql_invalid', 'Only read-only SQL statements are allowed.'));
                    $this->session->set_flashdata('ccx_template_filters_json', $filtersJsonInput);
                    $this->session->set_flashdata('ccx_template_selected_type', 'sql');
                    $this->session->set_flashdata('ccx_template_sql_query', $normalizedQuery);
                    redirect(admin_url('ccx/template' . ($id ? '/' . $id : '')));
                }

                $columns    = [];
                $sqlPayload = [
                    'sql_query' => $normalizedQuery,
                    'filters'   => $filtersJsonStored,
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                ];

                $templateId = $this->ccx_model->save_template($id, $template, $columns, $sqlPayload);
            } elseif ($type === 'dynamic') {
                $dynamicInput = $this->input->post('dynamic');
                if (! is_array($dynamicInput)) {
                    $dynamicInput = [];
                }

                $pageLabels = [
                    'main' => ccx_lang('ccx_template_dynamic_page_main', 'Main Page'),
                    'sub'  => ccx_lang('ccx_template_dynamic_page_sub', 'Sub Page'),
                ];

                $dynamicPayload = [];
                $dynamicErrors  = [];
                $dynamicPrefill = [];

                foreach ($pageLabels as $pageKey => $labelText) {
                    $pageData = $dynamicInput[$pageKey] ?? [];
                    if (! is_array($pageData)) {
                        $pageData = [];
                    }

                    $sqlRaw        = (string) ($pageData['sql_query'] ?? '');
                    $sqlNormalized = html_entity_decode($sqlRaw, ENT_QUOTES | ENT_HTML5);
                    $sqlNormalized = str_replace(["\r\n", "\r"], "\n", $sqlNormalized);

                    if (trim($sqlNormalized) !== '' && ! $this->ccx_model->is_safe_sql_query($sqlNormalized)) {
                        $dynamicErrors[] = sprintf(ccx_lang('ccx_template_dynamic_sql_invalid', '%s SQL query must be read-only.'), $labelText);
                    }

                    $htmlContent = (string) ($pageData['html_content'] ?? $pageData['html'] ?? '');
                    $filtersJson = (string) ($pageData['filters'] ?? '');

                    [$pageFilters, $pageFilterErrors] = $this->parse_filters_json($filtersJson);
                    if (! empty($pageFilterErrors)) {
                        foreach ($pageFilterErrors as $error) {
                            $dynamicErrors[] = sprintf('%s: %s', $labelText, $error);
                        }
                    }

                    $dynamicPayload[$pageKey] = [
                        'sql_query'    => $sqlNormalized,
                        'html_content' => $htmlContent,
                        'filters'      => $pageFilters,
                    ];

                    $dynamicPrefill[$pageKey] = [
                        'sql_query'    => $sqlNormalized,
                        'html_content' => $htmlContent,
                        'filters'      => $filtersJson,
                    ];
                }

                if (! empty($dynamicErrors)) {
                    set_alert('danger', implode('<br>', $dynamicErrors));
                    $this->session->set_flashdata('ccx_template_dynamic_input', $dynamicPrefill);
                    $this->session->set_flashdata('ccx_template_selected_type', 'dynamic');
                    redirect(admin_url('ccx/template' . ($id ? '/' . $id : '')));
                }

                $templateId = $this->ccx_model->save_template($id, $template, [], [], $dynamicPayload);
            } else {
                $columns    = $this->input->post('columns') ?? [];
                $templateId = $this->ccx_model->save_template($id, $template, $columns);
            }

            if ($templateId) {
                set_alert('success', ccx_lang('ccx_template_saved', 'Template saved successfully.'));
                redirect(admin_url('ccx/templates'));
            }

            set_alert('danger', ccx_lang('ccx_template_save_failed', 'Unable to save the template, please review the input.'));
            redirect(admin_url('ccx/template' . ($id ? '/' . $id : '')));
        }

        $tablesMeta            = $this->ccx_model->get_available_tables();
        $tableOptions          = [];
        foreach ($tablesMeta as $short => $info) {
            $tableOptions[$short] = $info['label'] ?? $short;
        }

        $templateRecord = $id ? $this->ccx_model->get_template($id) : null;
        if ($id && ! $templateRecord) {
            set_alert('warning', ccx_lang('ccx_template_not_found', 'Template not found.'));
            redirect(admin_url('ccx/templates'));
        }

        $selectedType = $templateRecord ? strtolower((string) ($templateRecord['type'] ?? 'smart')) : 'smart';
        if (! in_array($selectedType, ['smart', 'sql', 'dynamic'], true)) {
            $selectedType = 'smart';
        }

        $flashSelectedType = $this->session->flashdata('ccx_template_selected_type');
        if ($flashSelectedType !== null) {
            $flashSelectedType = strtolower(trim((string) $flashSelectedType));
            if (in_array($flashSelectedType, ['smart', 'sql', 'dynamic'], true)) {
                $selectedType = $flashSelectedType;
            }
        }

        $columns = [];
        if ($selectedType === 'smart' && $id) {
            $columns = $this->ccx_model->get_template_columns($id);
        }

        $dynamicPages = [
            'main' => [
                'sql_query'    => '',
                'html_content' => '',
                'filters_json' => '',
            ],
            'sub' => [
                'sql_query'    => '',
                'html_content' => '',
                'filters_json' => '',
            ],
        ];

        if ($selectedType === 'dynamic' && $templateRecord) {
            $storedPages = $this->ccx_model->get_dynamic_template_pages((int) $templateRecord['id']);

            foreach ($storedPages as $pageKey => $pageData) {
                if (! isset($dynamicPages[$pageKey])) {
                    continue;
                }
                $decodedFilters = $this->decode_saved_filters($pageData['filters'] ?? null);
                $dynamicPages[$pageKey] = [
                    'sql_query'    => $pageData['sql_query'] ?? '',
                    'html_content' => $pageData['html_content'] ?? '',
                    'filters_json' => ! empty($decodedFilters)
                        ? json_encode($decodedFilters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : '',
                ];
            }
        }

        $flashDynamic = $this->session->flashdata('ccx_template_dynamic_input');
        if (is_array($flashDynamic)) {
            foreach ($flashDynamic as $pageKey => $pageData) {
                if (! isset($dynamicPages[$pageKey]) || ! is_array($pageData)) {
                    continue;
                }
                $dynamicPages[$pageKey]['sql_query']    = (string) ($pageData['sql_query'] ?? $dynamicPages[$pageKey]['sql_query']);
                $dynamicPages[$pageKey]['html_content'] = (string) ($pageData['html_content'] ?? $dynamicPages[$pageKey]['html_content']);
                $dynamicPages[$pageKey]['filters_json'] = (string) ($pageData['filters'] ?? $dynamicPages[$pageKey]['filters_json']);
            }
        }

        $sqlTemplate = [
            'sql_query' => $templateRecord ? (string) ($templateRecord['sql_query'] ?? '') : '',
            'is_active' => $templateRecord ? (int) ($templateRecord['is_active'] ?? 1) : 1,
        ];

        $flashSqlQuery = $this->session->flashdata('ccx_template_sql_query');
        if ($flashSqlQuery !== null) {
            $sqlTemplate['sql_query'] = (string) $flashSqlQuery;
        }

        $storedFilters   = $templateRecord['filters'] ?? null;
        $templateFilters = $this->decode_saved_filters($storedFilters);
        $prefillFiltersJson = $this->session->flashdata('ccx_template_filters_json');
        if ($prefillFiltersJson !== null) {
            $filtersJson = $prefillFiltersJson;
        } elseif (! empty($templateFilters)) {
            $filtersJson = json_encode($templateFilters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $filtersJson = '';
        }

        $data['title']         = $id ? ccx_lang('ccx_template_edit_title', 'Edit Report Template') : ccx_lang('ccx_template_create_title', 'Create Report Template');
        $data['template']      = $templateRecord;
        $data['templateType']  = $selectedType;
        $data['columns']       = $columns;
        $data['aggregateMap'] = $this->ccx_model->get_aggregate_options();
        $data['tableOptions'] = $tableOptions;
        $data['columnsMap']   = $this->ccx_model->get_columns_map($tablesMeta);
        $data['sqlTemplate']   = $sqlTemplate;
        $data['templateFilters'] = $templateFilters;
        $data['filtersJson']   = $filtersJson;
        $data['dynamicPages']  = $dynamicPages;
        $data['selectedType']  = $selectedType;

        $this->load->view('ccx/templates/form', $data);
    }

    public function template_preview()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $columnInput = $this->input->post('column', false);
        if ($columnInput !== null) {
            if (is_string($columnInput)) {
                $decoded = json_decode($columnInput, true);
                $columnInput = is_array($decoded) ? $decoded : null;
            } elseif (! is_array($columnInput)) {
                $columnInput = null;
            }
        } else {
            $payload = json_decode($this->input->raw_input_stream, true);
            if (! is_array($payload)) {
                $payload = [];
            }
            $columnInput = $payload['column'] ?? null;
        }
        if (! is_array($columnInput)) {
            $response = [
                'success' => false,
                'state'   => 'invalid',
                'message' => ccx_lang('ccx_template_preview_invalid', 'Unable to build a preview with the current inputs.'),
            ];
        } else {
            $response = $this->ccx_model->preview_column($columnInput);
        }

        $status = 200;
        if (empty($response['success'])) {
            $status = ($response['state'] ?? '') === 'error' ? 500 : 422;
        }

        $json = json_encode($response);
        if ($json === false) {
            log_message('error', 'CCX template_preview JSON encoding failed: ' . json_last_error_msg());
            $json = json_encode([
                'success' => false,
                'state'   => 'error',
                'message' => 'Encoding failure',
            ]);
            $status = 500;
        }

        if (method_exists($this, 'output')) {
            $this->output->set_content_type('application/json; charset=utf-8');
            $this->output->set_status_header($status);
            $this->output->set_output($json);
        } else {
            $statusHeader = $_SERVER['SERVER_PROTOCOL'] . ' ' . $status;
            header('Content-Type: application/json; charset=utf-8');
            header($statusHeader);
            echo $json;
        }
        exit;
    }

    public function delete_template($id)
    {
        if (! (staff_can('delete', 'ccx_templates') || is_admin())) {
            access_denied('ccx_templates');
        }

        $id = (int) $id;
        if ($id <= 0) {
            show_404();
        }

        if ($this->ccx_model->delete_template($id)) {
            set_alert('success', ccx_lang('ccx_template_deleted', 'Template deleted.'));
        } else {
            set_alert('danger', ccx_lang('ccx_template_delete_failed', 'Unable to delete template.'));
        }

        redirect(admin_url('ccx/templates'));
    }

    public function sections()
    {
        if (! (staff_can('view', 'ccx_sections') || staff_can('view_own', 'ccx_sections') || is_admin())) {
            access_denied('ccx_sections');
        }

        $data['title']    = ccx_lang('ccx_sections_page_title', 'Report Sections');
        $data['sections'] = $this->ccx_model->get_sections();

        $this->load->view('ccx/sections/list', $data);
    }

    public function section($id = null)
    {
        $id = $id !== null ? (int) $id : null;
        $isPost = $this->input->method() === 'post';
        $canViewSections = staff_can('view', 'ccx_sections') || staff_can('view_own', 'ccx_sections') || is_admin();

        if ($isPost) {
            if ($id) {
                if (! (staff_can('edit', 'ccx_sections') || is_admin())) {
                    access_denied('ccx_sections');
                }
            } else {
                if (! (staff_can('create', 'ccx_sections') || is_admin())) {
                    access_denied('ccx_sections');
                }
            }
        } else {
            if ($id) {
                if (! $canViewSections) {
                    access_denied('ccx_sections');
                }
            } else {
                if (! ($canViewSections || staff_can('create', 'ccx_sections') || is_admin())) {
                    access_denied('ccx_sections');
                }
            }
        }

        if ($isPost) {
            $section     = $this->input->post('section') ?? [];
            $templateIds = $this->input->post('template_ids') ?? [];

            $sectionId = $this->ccx_model->save_section($id, $section, $templateIds);

            if ($sectionId) {
                set_alert('success', ccx_lang('ccx_section_saved', 'Section saved successfully.'));
                redirect(admin_url('ccx/sections'));
            }

            set_alert('danger', ccx_lang('ccx_section_save_failed', 'Unable to save the section, please review the input.'));
            redirect(admin_url('ccx/section' . ($id ? '/' . $id : '')));
        }

        $data['title']       = $id ? ccx_lang('ccx_section_edit_title', 'Edit Report Section') : ccx_lang('ccx_section_create_title', 'Create Report Section');
        $data['section']     = $id ? $this->ccx_model->get_section($id) : null;
        $data['templates']   = $this->ccx_model->get_templates();
        $data['selectedIds'] = $id ? $this->ccx_model->get_section_template_ids($id) : [];

        if ($id && ! $data['section']) {
            set_alert('warning', ccx_lang('ccx_section_not_found', 'Section not found.'));
            redirect(admin_url('ccx/sections'));
        }

        $this->load->view('ccx/sections/form', $data);
    }

    public function import_export()
    {
        if (! (staff_can('view', 'ccx_import_export') || is_admin())) {
            access_denied('ccx_import_export');
        }

        $data['title']         = ccx_lang('ccx_import_export_page_title', 'Import & Export');
        $data['exportUrl']     = admin_url('ccx/export_bundle');
        $data['importAction']  = admin_url('ccx/import_bundle');
        $data['templatesList'] = $this->ccx_model->get_templates();

        $this->load->view('ccx/import_export/index', $data);
    }

    public function export_bundle()
    {
        if ($this->input->method() !== 'get') {
            show_404();
        }

        if (! (staff_can('view', 'ccx_import_export') || is_admin())) {
            access_denied('ccx_import_export');
        }

        $templateId = (int) ($this->input->get('template_id') ?? 0);
        $templateIds = null;

        if ($templateId > 0) {
            $template = $this->ccx_model->get_template($templateId);
            if (! $template) {
                set_alert('danger', ccx_lang('ccx_template_not_found', 'Template not found.'));
                redirect(admin_url('ccx/import_export'));
            }

            $templateIds = [$templateId];
        }

        $bundle = $this->ccx_model->get_export_bundle($templateIds);
        $json   = json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            log_message('error', 'CCX export_bundle json_encode failed: ' . json_last_error_msg());
            set_alert('danger', ccx_lang('problem_exporting_json', 'Failed to generate the export bundle.'));
            redirect(admin_url('ccx/import_export'));
        }

        $filename = 'ccx-export-' . date('Ymd-His') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $json;
        exit;
    }

    public function import_bundle()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (! (staff_can('create', 'ccx_import_export') || is_admin())) {
            access_denied('ccx_import_export');
        }

        $file       = $_FILES['import_file'] ?? null;
        $contents   = '';
        $pastedJson = $this->input->post('import_json', false);

        if (is_string($pastedJson)) {
            $contents = trim($pastedJson);
        }

        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $fileContents = (string) file_get_contents($file['tmp_name']);
            if (trim($fileContents) !== '') {
                $contents = $fileContents;
            }
        }

        if (trim($contents) === '') {
            set_alert('danger', ccx_lang('ccx_import_export_no_input', 'Provide a JSON file or paste JSON to import.'));
            redirect(admin_url('ccx/import_export'));
        }

        $payload = json_decode($contents, true);
        if (! is_array($payload)) {
            log_message('error', 'CCX import_bundle json_decode failed: ' . json_last_error_msg());
            set_alert('danger', ccx_lang('ccx_import_export_decode_failed', 'Unable to decode the uploaded JSON file.'));
            redirect(admin_url('ccx/import_export'));
        }

        $result = $this->ccx_model->import_bundle($payload);

        if (empty($result['success'])) {
            set_alert('danger', ccx_lang('ccx_import_export_invalid_bundle', 'The uploaded file is not a valid CCX export bundle.'));
        } else {
            $templates = isset($result['templates']) ? (int) $result['templates'] : 0;
            $sections  = isset($result['sections']) ? (int) $result['sections'] : 0;
            set_alert('success', sprintf(ccx_lang('ccx_import_export_import_success', 'Imported %d templates and %d sections.'), $templates, $sections));
        }

        redirect(admin_url('ccx/import_export'));
    }

    public function delete_section($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            show_404();
        }

        if ($this->ccx_model->delete_section($id)) {
            set_alert('success', ccx_lang('ccx_section_deleted', 'Section deleted.'));
        } else {
            set_alert('danger', ccx_lang('ccx_section_delete_failed', 'Unable to delete section.'));
        }

        redirect(admin_url('ccx/sections'));
    }

    /**
     * @param string $filtersJson
     *
     * @return array{0: array<int,array<string,mixed>>, 1: array<int,string>}
     */
    private function parse_filters_json(string $filtersJson): array
    {
        $filtersJson = trim($filtersJson);
        if ($filtersJson === '') {
            return [[], []];
        }

        $decoded = json_decode($filtersJson, true);
        if (! is_array($decoded)) {
            return [[], [ccx_lang('ccx_template_sql_filter_invalid_json', 'Filters JSON must decode to an array.')]];
        }

        $normalized = [];
        $errors     = [];
        $seenKeys   = [];

        foreach ($decoded as $index => $rawFilter) {
            $position = $index + 1;

            if (! is_array($rawFilter)) {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_invalid_object', 'Filter definition #%d must be an object.'), $position);
                continue;
            }

            $key = isset($rawFilter['key']) ? trim((string) $rawFilter['key']) : '';
            if ($key === '') {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_key_required', 'Filter definition #%d requires a "key" value.'), $position);
                continue;
            }

            if (! preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_key_invalid', 'Filter definition #%d has an invalid key "%s". Use letters, numbers or underscores.'), $position, $key);
                continue;
            }

            if (isset($seenKeys[$key])) {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_key_duplicate', 'Filter definition #%d reuses the key "%s". Keys must be unique.'), $position, $key);
                continue;
            }

            $label = isset($rawFilter['label']) ? trim((string) $rawFilter['label']) : '';
            if ($label === '') {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_label_required', 'Filter definition #%d requires a label.'), $position);
                continue;
            }

            $typeRaw = isset($rawFilter['type']) ? strtolower(trim((string) $rawFilter['type'])) : 'text';
            $typeMap = [
                'dropdown' => 'select',
            ];
            $type = $typeMap[$typeRaw] ?? $typeRaw;

            $allowedTypes = ['text', 'number', 'date', 'datetime', 'select'];
            if (! in_array($type, $allowedTypes, true)) {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_type_invalid', 'Filter definition #%d uses an unsupported type "%s".'), $position, $type);
                continue;
            }

            $filter = [
                'key'         => $key,
                'label'       => $label,
                'type'        => $type,
                'required'    => ! empty($rawFilter['required']),
                'placeholder' => isset($rawFilter['placeholder']) ? trim((string) $rawFilter['placeholder']) : '',
                'description' => isset($rawFilter['description']) ? trim((string) $rawFilter['description']) : '',
            ];

            if (array_key_exists('default', $rawFilter)) {
                $filter['default'] = $rawFilter['default'];
            }

            if ($type === 'number' && isset($filter['default']) && $filter['default'] !== '' && ! is_numeric($filter['default'])) {
                $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_default_number', 'Filter definition #%d has a default value that is not numeric.'), $position);
                continue;
            }

            if ($type === 'select') {
                $optionsRaw = isset($rawFilter['options']) ? $rawFilter['options'] : [];
                if (! is_array($optionsRaw) || empty($optionsRaw)) {
                    $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_options_required', 'Filter definition #%d requires at least one select option.'), $position);
                    continue;
                }

                $options = [];
                foreach ($optionsRaw as $optionIndex => $optionValue) {
                    $value = null;
                    $label = null;

                    if (is_array($optionValue)) {
                        if (isset($optionValue['value']) && isset($optionValue['label'])) {
                            $value = (string) $optionValue['value'];
                            $label = (string) $optionValue['label'];
                        }
                    } elseif (is_scalar($optionValue)) {
                        if (! is_int($optionIndex)) {
                            $value = (string) $optionIndex;
                            $label = (string) $optionValue;
                        } else {
                            $value = (string) $optionValue;
                            $label = (string) $optionValue;
                        }
                    }

                    if ($value === null || $label === null || $value === '' || $label === '') {
                        $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_option_invalid', 'Filter definition #%d contains an invalid option at position #%d.'), $position, $optionIndex + 1);
                        $options = [];
                        break;
                    }

                    $options[] = [
                        'value' => $value,
                        'label' => $label,
                    ];
                }

                if (empty($options)) {
                    $errors[] = sprintf(ccx_lang('ccx_template_sql_filter_options_required', 'Filter definition #%d requires at least one select option.'), $position);
                    continue;
                }

                $filter['options'] = $options;
                if (isset($filter['default']) && $filter['default'] !== '') {
                    $filter['default'] = (string) $filter['default'];
                }
            }

            $seenKeys[$key] = true;
            $normalized[]   = $filter;
        }

        return [$normalized, $errors];
    }

    private function export_smart_template(array $template): void
    {
        $filterDefinitions = $this->decode_saved_filters($template['filters'] ?? null);
        $filterContext     = $this->build_filter_context($filterDefinitions);

        if ($filterContext['has_errors']) {
            set_alert('danger', ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.'));
            redirect($this->report_redirect_url((int) $template['id']));

            return;
        }

        $columns = $this->ccx_model->get_template_columns($template['id']);
        if (empty($columns)) {
            set_alert('warning', ccx_lang('ccx_reports_export_no_columns', 'There are no columns to export for this report.'));
            redirect($this->report_redirect_url((int) $template['id']));

            return;
        }

        $headers = [];
        foreach ($columns as $index => $column) {
            $label = trim((string) ($column['label'] ?? ''));
            if ($label === '') {
                $label = ccx_lang('ccx_reports_column_header', 'Column') . ' ' . ($index + 1);
            }
            $headers[] = $label;
        }

        $runtimeFilters = $filterContext['values'];
        if (! is_array($runtimeFilters)) {
            $runtimeFilters = [];
        }

        $options = [
            'draw'            => 1,
            'start'           => 0,
            'length'          => null,
            'search'          => '',
            'filters'         => [],
            'runtime_filters' => $runtimeFilters,
        ];

        $dateFrom = $this->input->get('ccx_date_from', true);
        if ($dateFrom !== null && $dateFrom !== '') {
            $options['date_from'] = $dateFrom;
            if (! array_key_exists('date_from', $options['runtime_filters'])) {
                $options['runtime_filters']['date_from'] = $dateFrom;
            }
        }

        $dateTo = $this->input->get('ccx_date_to', true);
        if ($dateTo !== null && $dateTo !== '') {
            $options['date_to'] = $dateTo;
            if (! array_key_exists('date_to', $options['runtime_filters'])) {
                $options['runtime_filters']['date_to'] = $dateTo;
            }
        }

        $dataset = $this->ccx_model->get_template_table_data((int) $template['id'], $options, $columns);
        $rows    = $dataset['aaData'] ?? [];

        $exportRows = $this->prepare_export_rows($rows, count($headers));
        $redirectParams = $this->input->get(null, true);
        $redirectParams = is_array($redirectParams) ? $redirectParams : [];

        $this->stream_excel_download(
            $template['name'],
            $headers,
            $exportRows,
            $this->report_redirect_url((int) $template['id'], $redirectParams)
        );
    }

    private function export_sql_template(array $template): void
    {
        $filterDefinitions = $this->decode_saved_filters($template['filters'] ?? null);
        $filterContext     = $this->build_filter_context($filterDefinitions);

        if ($filterContext['has_errors']) {
            set_alert('danger', ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.'));
            redirect($this->report_redirect_url((int) $template['id']));

            return;
        }

        [$result, $error] = $this->ccx_model->run_sql_template_query($template, $filterContext['values']);
        if ($error !== null) {
            set_alert('danger', $error);
            redirect($this->report_redirect_url((int) $template['id']));

            return;
        }

        $columns = $result['columns'] ?? [];
        $rows    = $result['rows'] ?? [];

        if (empty($columns)) {
            set_alert('warning', ccx_lang('ccx_reports_export_no_columns', 'There are no columns to export for this report.'));
            redirect($this->report_redirect_url((int) $template['id']));

            return;
        }

        $orderedRows = [];
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row[$column] ?? '';
            }
            $orderedRows[] = $line;
        }

        $exportRows = $this->prepare_export_rows($orderedRows, count($columns));

        if (! empty($result['row_limit'])) {
            $notice = sprintf(ccx_lang('ccx_reports_export_row_limit_notice', 'Only the first %d rows are included in this export.'), (int) $result['row_limit']);
            $exportRows[] = $this->pad_export_row([$notice], count($columns));
        }

        $redirectParams = $this->input->get(null, true);
        $redirectParams = is_array($redirectParams) ? $redirectParams : [];

        $this->stream_excel_download(
            $template['name'],
            $columns,
            $exportRows,
            $this->report_redirect_url((int) $template['id'], $redirectParams)
        );
    }

    private function export_dynamic_template(array $template): void
    {
        $pageKey = strtolower((string) $this->input->get('page', true));
        if (! in_array($pageKey, ['main', 'sub'], true)) {
            $pageKey = 'main';
        }

        $pagesData  = $this->ccx_model->get_dynamic_template_pages($template['id']);
        $definition = $pagesData[$pageKey] ?? [
            'sql_query'    => '',
            'filters'      => null,
        ];

        $filterDefinitions = $this->decode_saved_filters($definition['filters'] ?? null);
        $filterContext     = $this->build_filter_context($filterDefinitions, null, null, 'filters_' . $pageKey);

        if ($filterContext['has_errors']) {
            set_alert('danger', ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.'));
            redirect($this->report_redirect_url((int) $template['id'], ['page' => $pageKey]));

            return;
        }

        $query = (string) ($definition['sql_query'] ?? '');
        if (trim($query) === '') {
            set_alert('warning', ccx_lang('ccx_reports_export_no_data', 'This report page does not produce any tabular data to export.'));
            redirect($this->report_redirect_url((int) $template['id'], ['page' => $pageKey]));

            return;
        }

        [$result, $error] = $this->ccx_model->run_sql_template_query(['sql_query' => $query], $filterContext['values']);
        if ($error !== null) {
            set_alert('danger', $error);
            redirect($this->report_redirect_url((int) $template['id'], ['page' => $pageKey]));

            return;
        }

        $columns = $result['columns'] ?? [];
        $rows    = $result['rows'] ?? [];

        if (empty($columns)) {
            set_alert('warning', ccx_lang('ccx_reports_export_no_columns', 'There are no columns to export for this report.'));
            redirect($this->report_redirect_url((int) $template['id'], ['page' => $pageKey]));

            return;
        }

        $orderedRows = [];
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row[$column] ?? '';
            }
            $orderedRows[] = $line;
        }

        $exportRows = $this->prepare_export_rows($orderedRows, count($columns));

        if (! empty($result['row_limit'])) {
            $notice = sprintf(ccx_lang('ccx_reports_export_row_limit_notice', 'Only the first %d rows are included in this export.'), (int) $result['row_limit']);
            $exportRows[] = $this->pad_export_row([$notice], count($columns));
        }

        $filename = $template['name'] . '-' . ($pageKey === 'main' ? 'main' : $pageKey);
        $redirectParams = $this->input->get(null, true);
        $redirectParams = is_array($redirectParams) ? $redirectParams : [];

        $this->stream_excel_download(
            $filename,
            $columns,
            $exportRows,
            $this->report_redirect_url((int) $template['id'], $redirectParams)
        );
    }

    private function report_redirect_url(int $templateId, array $params = []): string
    {
        $url = admin_url('ccx/report/' . $templateId);
        if (! empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    private function normalize_export_cell($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $stripped = strip_tags($decoded);

            return trim($stripped);
        }

        if (is_array($value)) {
            $flattened = [];
            foreach ($value as $item) {
                $flattened[] = $this->normalize_export_cell($item);
            }

            return implode(', ', array_filter($flattened, static function ($item) {
                return $item !== '';
            }));
        }

        if (is_object($value)) {
            return $this->normalize_export_cell((array) $value);
        }

        return trim((string) $value);
    }

    private function sanitize_export_filename(string $name): string
    {
        $decoded    = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sanitized  = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $decoded);
        $sanitized  = trim((string) $sanitized, '_');

        return $sanitized !== '' ? strtolower($sanitized) : 'ccx-report';
    }

    private function stream_excel_download(string $baseFilename, array $headers, array $rows, ?string $redirectOnFailure = null): void
    {
        $filename = $this->sanitize_export_filename($baseFilename) . '-' . date('Ymd-His') . '.xlsx';
        $workbook = $this->build_xlsx_workbook($headers, $rows);

        if ($workbook === null) {
            $this->stream_html_excel_download($baseFilename, $headers, $rows);
            return;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Length: ' . strlen($workbook));

        echo $workbook;
        exit;
    }

    private function stream_html_excel_download(string $baseFilename, array $headers, array $rows): void
    {
        $filename = $this->sanitize_export_filename($baseFilename) . '-' . date('Ymd-His') . '.xls';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1"><thead><tr>';

        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        echo '</tr></thead><tbody>';

        if (empty($rows)) {
            $colspan = max(1, count($headers));
            echo '<tr><td colspan="' . $colspan . '">';
            echo htmlspecialchars(ccx_lang('ccx_reports_export_no_rows', 'No records found.'), ENT_QUOTES, 'UTF-8');
            echo '</td></tr>';
        } else {
            foreach ($rows as $row) {
                echo '<tr>';
                $preparedRow = is_array($row) ? $row : [];
                foreach ($preparedRow as $cell) {
                    echo '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '</tr>';
            }
        }

        echo '</tbody></table></body></html>';
        exit;
    }

    private function prepare_export_rows(array $rows, int $targetColumns): array
    {
        $prepared = [];

        foreach ($rows as $row) {
            $prepared[] = $this->pad_export_row($row, $targetColumns);
        }

        return $prepared;
    }

    private function pad_export_row($row, int $targetColumns): array
    {
        if (! is_array($row)) {
            $row = [$row];
        }

        $normalized = array_map([$this, 'normalize_export_cell'], array_values($row));

        if ($targetColumns > 0) {
            $count = count($normalized);
            if ($count < $targetColumns) {
                $normalized = array_merge($normalized, array_fill(0, $targetColumns - $count, ''));
            } elseif ($count > $targetColumns) {
                $normalized = array_slice($normalized, 0, $targetColumns);
            }
        }

        return $normalized;
    }

    private function build_xlsx_workbook(array $headers, array $rows): ?string
    {
        if (! class_exists('ZipArchive')) {
            return null;
        }

        $columnCount = count($headers);

        if ($columnCount === 0) {
            return null;
        }

        $sheetRows = [];
        $sheetRows[] = $this->pad_export_row($headers, $columnCount);
        foreach ($rows as $row) {
            $sheetRows[] = $this->pad_export_row($row, $columnCount);
        }

        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'ccx_xlsx_');

        if ($tempFile === false) {
            return null;
        }

        $openResult = $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            @unlink($tempFile);

            return null;
        }

        $zip->addFromString('[Content_Types].xml', $this->build_content_types_xml());
        $zip->addFromString('_rels/.rels', $this->build_root_rels_xml());
        $zip->addFromString('docProps/core.xml', $this->build_core_props_xml());
        $zip->addFromString('docProps/app.xml', $this->build_app_props_xml());
        $zip->addFromString('xl/workbook.xml', $this->build_workbook_xml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->build_workbook_rels_xml());
        $zip->addFromString('xl/styles.xml', $this->build_styles_xml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->build_sheet_xml($sheetRows));

        $zip->close();

        $binary = file_get_contents($tempFile);
        @unlink($tempFile);

        return $binary !== false ? $binary : null;
    }

    private function build_sheet_xml(array $rows): string
    {
        $rowCount = count($rows);
        $columnCount = $rowCount > 0 ? count($rows[0]) : 0;
        $lastColumn = $this->excel_column_letter(max(1, $columnCount));
        $lastRow = max(1, $rowCount);
        $dimension = 'A1:' . $lastColumn . $lastRow;

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<dimension ref="' . $dimension . '"/>';
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';
        $xml .= '<sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="' . $excelRow . '">';

            foreach ($row as $columnIndex => $value) {
                $cellRef = $this->excel_column_letter($columnIndex + 1) . $excelRow;

                if ($value === '') {
                    $xml .= '<c r="' . $cellRef . '"/>';
                    continue;
                }

                if ($this->is_numeric_cell($value)) {
                    $xml .= '<c r="' . $cellRef . '"><v>' . $value . '</v></c>';
                } else {
                    $xml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $this->xml_escape($value) . '</t></is></c>';
                }
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        $xml .= '</worksheet>';

        return $xml;
    }

    private function build_workbook_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="28800" windowHeight="17600"/></bookViews>'
            . '<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function build_workbook_rels_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function build_content_types_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private function build_styles_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function build_core_props_xml(): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>CCX Report Export</dc:title>'
            . '<dc:creator>Perfex CCX</dc:creator>'
            . '<cp:lastModifiedBy>Perfex CCX</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function build_app_props_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Perfex CCX</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Report</vt:lpstr></vt:vector></TitlesOfParts>'
            . '<Company>Perfex</Company>'
            . '<LinksUpToDate>false</LinksUpToDate>'
            . '<SharedDoc>false</SharedDoc>'
            . '<HyperlinksChanged>false</HyperlinksChanged>'
            . '<AppVersion>16.0300</AppVersion>'
            . '</Properties>';
    }

    private function build_root_rels_xml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function excel_column_letter(int $index): string
    {
        if ($index <= 0) {
            return 'A';
        }

        $letter = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $index = (int) (($index - $remainder - 1) / 26);
        }

        return $letter;
    }

    private function xml_escape(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return str_replace("\n", '&#10;', $escaped);
    }

    private function is_numeric_cell(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if (! is_numeric($value)) {
            return false;
        }

        return ! preg_match('/^0\d+$/', $value);
    }

    /**
     * @param string|null $filtersJson
     *
     * @return array<int,array<string,mixed>>
     */
    private function decode_saved_filters(?string $filtersJson): array
    {
        if ($filtersJson === null || trim($filtersJson) === '') {
            return [];
        }

        $decoded = json_decode((string) $filtersJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<int,array<string,mixed>> $definitions
     * @param array<string,mixed>|null       $input
     * @param bool|null                      $submittedOverride
     * @param string                         $inputKey
     *
     * @return array{
     *     filters: array<int,array<string,mixed>>,
     *     values: array<string,mixed>,
     *     has_errors: bool,
     *     submitted: bool
     * }
     */
    private function build_filter_context(array $definitions, ?array $input = null, ?bool $submittedOverride = null, string $inputKey = 'filters'): array
    {
        if ($input === null) {
            $rawInput  = $this->input->get($inputKey);
            $submitted = $submittedOverride ?? isset($_GET[$inputKey]);
        } else {
            $rawInput  = $input;
            $submitted = $submittedOverride ?? true;
        }

        if (! is_array($rawInput)) {
            $rawInput = [];
        }

        $filtersForView = [];
        $values         = [];
        $hasErrors      = false;

        foreach ($definitions as $definition) {
            if (! is_array($definition) || empty($definition['key'])) {
                continue;
            }

            $key         = $definition['key'];
            $type        = isset($definition['type']) ? strtolower((string) $definition['type']) : 'text';
            $required    = ! empty($definition['required']);
            $placeholder = isset($definition['placeholder']) ? (string) $definition['placeholder'] : '';
            $description = isset($definition['description']) ? (string) $definition['description'] : '';
            $options     = is_array($definition['options'] ?? null) ? $definition['options'] : [];
            $default     = isset($definition['default']) ? (string) $definition['default'] : '';

            $inputValue = $submitted ? ($rawInput[$key] ?? null) : $default;

            if (is_array($inputValue)) {
                $inputValue = reset($inputValue);
            }

            $inputValue = $inputValue === null ? '' : (string) $inputValue;
            $inputValue = trim($inputValue);

            $bindingValue = null;
            $error        = null;
            $valueForForm = $inputValue;

            switch ($type) {
                case 'number':
                    if ($inputValue === '') {
                        if ($required) {
                            $error = ccx_lang('ccx_template_sql_filter_error_required', 'This field is required.');
                        } else {
                            $bindingValue = null;
                        }
                    } elseif (! is_numeric($inputValue)) {
                        $error = ccx_lang('ccx_template_sql_filter_error_number', 'Enter a valid number.');
                    } else {
                        $bindingValue = strpos($inputValue, '.') !== false ? (float) $inputValue : (int) $inputValue;
                    }
                    break;

                case 'date':
                    if ($inputValue === '') {
                        if ($required) {
                            $error = ccx_lang('ccx_template_sql_filter_error_required', 'This field is required.');
                        } else {
                            $bindingValue = null;
                        }
                    } else {
                        $date = \DateTime::createFromFormat('Y-m-d', $inputValue);
                        if (! $date || $date->format('Y-m-d') !== $inputValue) {
                            $error = ccx_lang('ccx_template_sql_filter_error_date', 'Enter a valid date (YYYY-MM-DD).');
                        } else {
                            $bindingValue = $date->format('Y-m-d');
                        }
                    }
                    break;

                case 'datetime':
                    if ($inputValue === '') {
                        if ($required) {
                            $error = ccx_lang('ccx_template_sql_filter_error_required', 'This field is required.');
                        } else {
                            $bindingValue = null;
                        }
                    } else {
                        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $inputValue)
                            ?: \DateTime::createFromFormat('Y-m-d\TH:i:s', $inputValue)
                            ?: \DateTime::createFromFormat('Y-m-d\TH:i', $inputValue)
                            ?: \DateTime::createFromFormat('Y-m-d H:i', $inputValue);

                        if (! $date) {
                            $error = ccx_lang('ccx_template_sql_filter_error_datetime', 'Enter a valid date & time.');
                        } else {
                            $bindingValue = $date->format('Y-m-d H:i:s');
                            $valueForForm = $date->format('Y-m-d\TH:i');
                        }
                    }
                    break;

                case 'select':
                    $allowed = [];
                    foreach ($options as $option) {
                        if (is_array($option) && isset($option['value'])) {
                            $allowed[(string) $option['value']] = $option['label'] ?? $option['value'];
                        }
                    }

                    if ($inputValue === '') {
                        if ($required) {
                            $error = ccx_lang('ccx_template_sql_filter_error_required', 'This field is required.');
                        } else {
                            $bindingValue = null;
                        }
                    } elseif (! array_key_exists($inputValue, $allowed)) {
                        $error = ccx_lang('ccx_template_sql_filter_error_choice', 'Select a valid option.');
                    } else {
                        $bindingValue = $inputValue;
                    }
                    break;

                case 'text':
                default:
                    if ($inputValue === '') {
                        if ($required) {
                            $error = ccx_lang('ccx_template_sql_filter_error_required', 'This field is required.');
                        } else {
                            $bindingValue = null;
                        }
                    } else {
                        $bindingValue = $inputValue;
                        if ($bindingValue !== '') {
                            if (function_exists('mb_substr')) {
                                $bindingValue = mb_substr($bindingValue, 0, 1000);
                            } else {
                                $bindingValue = substr($bindingValue, 0, 1000);
                            }
                        }
                    }
                    break;
            }

            if ($error !== null) {
                $hasErrors = true;
            }

            $filtersForView[] = [
                'key'         => $key,
                'label'       => $definition['label'],
                'type'        => $type,
                'required'    => $required,
                'placeholder' => $placeholder,
                'description' => $description,
                'options'     => $options,
                'value'       => $valueForForm,
                'error'       => $error,
            ];

            $values[$key] = $bindingValue;
        }

        return [
            'filters'   => $filtersForView,
            'values'    => $values,
            'has_errors'=> $hasErrors,
            'submitted' => $submitted,
        ];
    }
}
