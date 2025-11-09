<?php
if (! defined('CCX_COLUMN_STYLES_LOADED')) {
    define('CCX_COLUMN_STYLES_LOADED', true);
    ?>
    <style>
        .ccx-column {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            margin-bottom: 18px;
            overflow: hidden;
        }
        .ccx-column__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.88), rgba(37, 99, 235, 0.72));
            color: #e2e8f0;
        }
        .ccx-column__title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .ccx-column__body {
            padding: 22px 24px 28px;
        }
        .ccx-section {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 14px;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .ccx-section__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0;
            background: rgba(226, 232, 240, 0.45);
        }
        .ccx-section__description {
            font-size: 12px;
            font-weight: 400;
            color: rgba(15, 23, 42, 0.6);
            margin-left: auto;
        }
        .ccx-section__body {
            padding: 16px;
        }
        .ccx-accordion-toggle {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 14px 16px;
            background: none;
            border: none;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #1e293b;
        }
        .ccx-accordion-toggle:focus {
            outline: none;
            box-shadow: none;
        }
        .ccx-accordion-icon {
            transition: transform 180ms ease;
            color: rgba(15,23,42,0.6);
        }
        .ccx-accordion-toggle.collapsed .ccx-accordion-icon {
            transform: rotate(-90deg);
        }
        .ccx-column__header .ccx-accordion-toggle {
            color: #e2e8f0;
            padding: 0;
            text-transform: none;
            font-size: 15px;
            letter-spacing: 0.1px;
            margin-right: 14px;
        }
        .ccx-column__header .ccx-accordion-icon {
            color: rgba(226, 232, 240, 0.85);
        }
        .ccx-column__header .ccx-accordion-toggle.collapsed .ccx-accordion-icon {
            transform: rotate(-90deg);
        }
        .ccx-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .ccx-section.grid .ccx-section__body {
            padding-bottom: 6px;
        }
        .ccx-preview {
            border: 1px dashed rgba(59, 130, 246, 0.35);
            border-radius: 14px;
            padding: 16px;
            background: rgba(239, 246, 255, 0.65);
            margin-top: 18px;
        }
        .ccx-preview__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .ccx-preview__title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .ccx-preview__status {
            font-size: 11px;
            font-weight: 600;
            border-radius: 999px;
            padding: 4px 10px;
        }
        .ccx-preview__status--idle {
            background: rgba(148, 163, 184, 0.18);
            color: #1f2937;
        }
        .ccx-preview__status--loading {
            background: rgba(59, 130, 246, 0.2);
            color: #1d4ed8;
        }
        .ccx-preview__status--ready {
            background: rgba(34, 197, 94, 0.2);
            color: #166534;
        }
        .ccx-preview__status--error {
            background: rgba(248, 113, 113, 0.2);
            color: #b91c1c;
        }
        .ccx-preview--error {
            border-color: rgba(248, 113, 113, 0.55);
            background: rgba(254, 226, 226, 0.6);
        }
        .ccx-preview__value {
            font-size: 22px;
            font-weight: 700;
            color: #1d4ed8;
        }
        .ccx-preview__message {
            margin-top: 6px;
            font-size: 12px;
            color: #475569;
        }
        .ccx-preview__warnings {
            margin-top: 10px;
            font-size: 12px;
            color: #92400e;
        }
        .ccx-preview__warning-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
    <?php
}
if (! function_exists('ccx_render_formula_source_card')) {
    function ccx_render_formula_source_card(
        $columnIndex,
        array $source,
        array $aggregateMap,
        array $tableOptions,
        array $columnsMap
    ): string {
        $sourceKey   = $source['key'] ?? ('src' . uniqid());
        $label       = $source['label'] ?? '';
        $type        = ($source['type'] ?? 'metric') === 'number' ? 'number' : 'metric';
        $aggregate   = $source['aggregate'] ?? 'SUM';
        $table       = $source['table_name'] ?? '';
        $columnName  = $source['column_name'] ?? '';
        $constant    = isset($source['constant']) ? $source['constant'] : '';

        $availableColumns = $columnsMap[$table] ?? [];
        if ($columnName !== '' && ! isset($availableColumns[$columnName])) {
            $availableColumns[$columnName] = $columnName;
        }

        $conditions = isset($source['conditions']) && is_array($source['conditions']) ? $source['conditions'] : [];

        ob_start();
        ?>
        <div class="ccx-formula-source tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 tw-mb-3" data-source-key="<?= html_escape($sourceKey); ?>">
            <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                <div class="tw-flex-1">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_formula_source_label', 'Source Label')); ?></label>
                                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][label]" class="form-control" value="<?= html_escape($label); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_formula_source_key', 'Reference Key')); ?> *</label>
                                <input type="text" name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][key]" class="form-control ccx-source-key" value="<?= html_escape($sourceKey); ?>" placeholder="total_revenue">
                                <small class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_formula_source_key_hint', 'Letters, numbers and underscores only.')); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="row tw-mt-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_formula_source_type', 'Source Type')); ?></label>
                                <select name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][type]" class="form-control ccx-source-type">
                                    <option value="metric" <?= $type === 'metric' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_formula_source_type_metric', 'Metric (Aggregate)')); ?></option>
                                    <option value="number" <?= $type === 'number' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_formula_source_type_number', 'Static Number')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8 ccx-source-metric" <?= $type === 'number' ? 'style="display:none;"' : ''; ?>>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?= html_escape(ccx_lang('ccx_template_field_aggregate', 'Function')); ?></label>
                                        <select name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][aggregate]" class="form-control ccx-source-aggregate-select">
                                            <?php foreach ($aggregateMap as $key => $labelOption) { ?>
                                                <option value="<?= html_escape($key); ?>" <?= $aggregate === $key ? 'selected' : ''; ?>><?= html_escape($labelOption); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?= html_escape(ccx_lang('ccx_template_field_table', 'Table')); ?></label>
                                        <select name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][table_name]" class="form-control ccx-source-table-select">
                                            <option value=""><?= html_escape(ccx_lang('ccx_template_field_table_placeholder', 'Select a table')); ?></option>
                                            <?php foreach ($tableOptions as $tableValue => $tableLabel) { ?>
                                                <option value="<?= html_escape($tableValue); ?>" <?= $table === (string) $tableValue ? 'selected' : ''; ?>>
                                                    <?= html_escape($tableLabel); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?= html_escape(ccx_lang('ccx_template_field_column', 'Column')); ?></label>
                                        <select name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][column_name]" class="form-control ccx-source-column-select" data-selected="<?= html_escape($columnName); ?>">
                                            <option value=""><?= html_escape(ccx_lang('ccx_template_field_column_placeholder', 'Select a column')); ?></option>
                                            <?php foreach ($availableColumns as $colKey => $colLabel) { ?>
                                                <option value="<?= html_escape($colKey); ?>" <?= $columnName === (string) $colKey ? 'selected' : ''; ?>>
                                                    <?= html_escape($colLabel); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 ccx-source-number" <?= $type === 'number' ? '' : 'style="display:none;"'; ?>>
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_formula_source_constant', 'Constant Value')); ?></label>
                                <input type="number" step="0.01" class="form-control" name="columns[<?= html_escape($columnIndex); ?>][formula_sources][<?= html_escape($sourceKey); ?>][constant]" value="<?= html_escape($constant); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="ccx-formula-filters tw-mt-2">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-2">
                            <div>
                                <strong><?= html_escape(ccx_lang('ccx_template_formula_filters', 'Filters')); ?></strong>
                                <div class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_formula_filters_hint', 'Optional filters applied before calculating this source. Example: field IN {{column:invoice_ids}}.')); ?></div>
                            </div>
                            <button class="btn btn-default btn-sm ccx-add-formula-condition" type="button">
                                <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_condition_add', 'Add Filter')); ?>
                            </button>
                        </div>
                        <div class="ccx-formula-conditions">
                            <?php
                            if (! empty($conditions)) {
                                foreach ($conditions as $condition) {
                                    $this->load->view('ccx/templates/partials/formula_condition', [
                                        'columnIndex' => $columnIndex,
                                        'sourceKey'   => $sourceKey,
                                        'condition'   => $condition,
                                    ]);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <button class="btn btn-danger btn-sm ccx-remove-formula-source" type="button" title="<?= html_escape(ccx_lang('ccx_template_formula_source_remove', 'Remove Source')); ?>">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

$columnIndex      = $index;
$mode             = ($column['mode'] ?? 'simple') === 'formula' ? 'formula' : 'simple';
$label            = $column['label'] ?? '';
$tableOptions     = $tableOptions ?? [];
$columnsMap       = $columnsMap ?? [];
$aggregateMap     = $aggregateMap ?? [];
$table            = $column['table_name'] ?? '';
$columnName       = $column['column_name'] ?? '';
$aggregateValue   = $column['aggregate_function'] ?? 'SUM';
$decimalPlaces    = isset($column['decimal_places']) ? $column['decimal_places'] : '';
$currencyDisplay  = $column['currency_display'] ?? 'inherit';
$dateFilterField  = $column['date_filter_field'] ?? '';
$joins            = $column['joins'] ?? [];
$conditions    = $column['conditions'] ?? [];
$formulaExpression = $column['formula_expression'] ?? '';
$formulaSources = is_array($column['formula_sources'] ?? null) ? $column['formula_sources'] : [];
$role             = $column['role'] ?? 'metric';

$prefix        = db_prefix();
$selectedTable = $table;
if ($selectedTable !== '' && ! isset($tableOptions[$selectedTable])) {
    if ($prefix !== '' && strpos($selectedTable, $prefix) === 0) {
        $trimmed = substr($selectedTable, strlen($prefix));
        if (isset($tableOptions[$trimmed])) {
            $selectedTable = $trimmed;
        }
    }
}

$availableColumns = isset($columnsMap[$selectedTable]) ? $columnsMap[$selectedTable] : [];
if ($columnName !== '' && ! isset($availableColumns[$columnName])) {
    $availableColumns[$columnName] = $columnName;
}
if ($dateFilterField !== '' && ! isset($availableColumns[$dateFilterField])) {
    $availableColumns[$dateFilterField] = $dateFilterField;
}
?>
<?php
?>
<?php $columnCollapseId = 'ccx-column-' . $columnIndex; ?>
<div class="ccx-column" data-column-index="<?= html_escape($columnIndex); ?>">
    <div class="ccx-column__header">
        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($columnCollapseId); ?>" aria-expanded="true">
            <span><?= html_escape(ccx_lang('ccx_reports_column_label', 'Column')); ?> #<?= html_escape(is_numeric($columnIndex) ? ((int) $columnIndex + 1) : $columnIndex); ?></span>
            <i class="fa fa-chevron-down ccx-accordion-icon text-sm"></i>
        </button>
        <button class="btn btn-danger btn-xs ccx-remove-column" type="button">
            <i class="fa fa-times"></i> <?= html_escape(ccx_lang('ccx_template_remove_column', 'Remove')); ?>
        </button>
    </div>
    <div id="<?= html_escape($columnCollapseId); ?>" class="ccx-column__body collapse show">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_field_label', 'Column Label')); ?></label>
                        <input type="text" name="columns[<?= html_escape($columnIndex); ?>][label]" class="form-control" value="<?= html_escape($label); ?>">
                        <small class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_field_label_hint', 'Optional label shown in the report; leave blank to auto-generate.')); ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_mode_label', 'Metric Type')); ?></label>
                        <select name="columns[<?= html_escape($columnIndex); ?>][mode]" class="form-control ccx-mode-select">
                            <option value="simple" <?= $mode === 'simple' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_mode_simple', 'Simple Metric')); ?></option>
                            <option value="formula" <?= $mode === 'formula' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_mode_formula', 'Advanced Formula')); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="ccx-mode-section" data-mode-section="simple" <?= $mode === 'formula' ? 'style="display:none;"' : ''; ?>>
                <div class="ccx-section">
                    <?php $sectionId = 'ccx-basic-' . $columnIndex; ?>
                    <div class="ccx-section__header">
                        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                            <span><?= html_escape(ccx_lang('ccx_template_basic_metric', 'Base Metric')); ?></span>
                            <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_basic_metric_hint', 'Select table, column and aggregation.')); ?></span>
                            <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                        </button>
                    </div>
                    <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?= html_escape(ccx_lang('ccx_template_field_table', 'Table')); ?> *</label>
                                    <select name="columns[<?= html_escape($columnIndex); ?>][table_name]" class="form-control ccx-table-select" data-mode-required="simple" <?= $mode === 'simple' ? 'required' : ''; ?>>
                                        <option value=""><?= html_escape(ccx_lang('ccx_template_field_table_placeholder', 'Select a table')); ?></option>
                                        <?php foreach ($tableOptions as $tableValue => $tableLabel) { ?>
                                            <option value="<?= html_escape($tableValue); ?>" <?= $selectedTable === (string) $tableValue ? 'selected' : ''; ?>>
                                                <?= html_escape($tableLabel); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?= html_escape(ccx_lang('ccx_template_field_column', 'Column')); ?></label>
                                    <select name="columns[<?= html_escape($columnIndex); ?>][column_name]" class="form-control ccx-column-select" data-selected="<?= html_escape($columnName); ?>">
                                        <option value=""><?= html_escape(ccx_lang('ccx_template_field_column_placeholder', 'Select a column')); ?></option>
                                        <?php foreach ($availableColumns as $colKey => $colLabel) { ?>
                                            <option value="<?= html_escape($colKey); ?>" <?= $columnName === (string) $colKey ? 'selected' : ''; ?>>
                                                <?= html_escape($colLabel); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?= html_escape(ccx_lang('ccx_template_field_aggregate', 'Function')); ?></label>
                                    <select name="columns[<?= html_escape($columnIndex); ?>][aggregate_function]" class="form-control ccx-aggregate-select">
                                        <?php foreach ($aggregateMap as $key => $labelOption) { ?>
                                            <option value="<?= html_escape($key); ?>" <?= $aggregateValue === $key ? 'selected' : ''; ?>>
                                                <?= html_escape($labelOption); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?= html_escape(ccx_lang('ccx_template_field_role', 'Column Role')); ?></label>
                                    <select name="columns[<?= html_escape($columnIndex); ?>][role]" class="form-control ccx-role-select">
                                        <option value="metric" <?= $role === 'metric' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_role_metric', 'Metric')); ?></option>
                                        <option value="group" <?= $role === 'group' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_role_group', 'Group By Dimension')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ccx-section">
                    <?php $sectionId = 'ccx-filters-' . $columnIndex; ?>
                    <div class="ccx-section__header">
                        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                            <span><?= html_escape(ccx_lang('ccx_template_field_conditions', 'Filters')); ?></span>
                            <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_conditions_hint', 'Reuse runtime filters or lock values before aggregation.')); ?></span>
                            <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                        </button>
                    </div>
                    <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-2">
                            <div class="tw-text-xs tw-text-neutral-500">
                                <?= html_escape(ccx_lang('ccx_template_filter_hint', 'Define WHERE conditions that run before the aggregation.')); ?>
                            </div>
                            <button class="btn btn-default btn-sm ccx-add-condition" type="button">
                                <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_condition_add', 'Add Filter')); ?>
                            </button>
                        </div>
                        <div class="ccx-conditions">
                            <?php
                            if (! empty($conditions)) {
                                foreach ($conditions as $condition) {
                                    $this->load->view('ccx/templates/partials/condition', [
                                        'columnIndex' => $columnIndex,
                                        'condition'   => $condition,
                                    ]);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="ccx-section">
                    <?php $sectionId = 'ccx-joins-' . $columnIndex; ?>
                    <div class="ccx-section__header">
                        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                            <span><?= html_escape(ccx_lang('ccx_template_joins_heading', 'Joins')); ?></span>
                            <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_joins_hint', 'Map relationships to bring additional tables into the metric.')); ?></span>
                            <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                        </button>
                    </div>
                    <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-2">
                            <div class="tw-text-xs tw-text-neutral-500">
                                <?= html_escape(ccx_lang('ccx_template_join_hint', 'Supports INNER/LEFT/RIGHT joins with optional aliases.')); ?>
                            </div>
                            <button class="btn btn-default btn-sm ccx-add-join" type="button">
                                <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_join_add', 'Add Join')); ?>
                            </button>
                        </div>
                        <div class="ccx-joins-container">
                            <?php
                            if (! empty($joins)) {
                                foreach ($joins as $joinRow) {
                                    $this->load->view('ccx/templates/partials/join', [
                                        'columnIndex' => $columnIndex,
                                        'join'        => $joinRow,
                                        'tableOptions'=> $tableOptions,
                                    ]);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="ccx-preview ccx-preview--idle" data-preview-wrapper>
                <div class="ccx-preview__header">
                    <span class="ccx-preview__title"><?= html_escape(ccx_lang('ccx_template_preview_heading', 'Instant Preview')); ?></span>
                    <span class="ccx-preview__status ccx-preview__status--idle badge" data-preview-status><?= html_escape(ccx_lang('ccx_template_preview_badge_idle', 'Idle')); ?></span>
                </div>
                <div class="ccx-preview__body">
                    <div class="ccx-preview__value" data-preview-value>-</div>
                    <div class="ccx-preview__message" data-preview-message><?= html_escape(ccx_lang('ccx_template_preview_idle', 'Adjust the inputs to see a preview.')); ?></div>
                    <div class="ccx-preview__warnings" data-preview-warnings style="display:none;"></div>
                </div>
            </div>

            <div class="ccx-section">
                <?php $sectionId = 'ccx-runtime-' . $columnIndex; ?>
                <div class="ccx-section__header">
                    <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                        <span><?= html_escape(ccx_lang('ccx_template_runtime_display', 'Runtime & Display')); ?></span>
                        <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_runtime_hint', 'Control date filters and currency formatting.')); ?></span>
                        <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                    </button>
                </div>
                <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_date_filter_field', 'Date Column (Runtime Filter)')); ?></label>
                                <select name="columns[<?= html_escape($columnIndex); ?>][date_filter_field]" class="form-control ccx-date-field-select" data-selected="<?= html_escape($dateFilterField); ?>">
                                    <option value=""><?= html_escape(ccx_lang('ccx_template_date_filter_placeholder', 'Select a date column')); ?></option>
                                    <?php foreach ($availableColumns as $colKey => $colLabel) { ?>
                                        <option value="<?= html_escape($colKey); ?>" <?= $dateFilterField === (string) $colKey ? 'selected' : ''; ?>>
                                            <?= html_escape($colLabel); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <small class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_date_filter_hint', 'Optional column used when the report date range is applied.')); ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_field_currency', 'Currency Symbol')); ?></label>
                                <select name="columns[<?= html_escape($columnIndex); ?>][currency_display]" class="form-control">
                                    <option value="inherit" <?= $currencyDisplay === 'inherit' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_currency_inherit', 'Use default')); ?></option>
                                    <option value="show" <?= $currencyDisplay === 'show' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_currency_show', 'Show symbol')); ?></option>
                                    <option value="hide" <?= $currencyDisplay === 'hide' ? 'selected' : ''; ?>><?= html_escape(ccx_lang('ccx_template_currency_hide', 'Hide symbol')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ccx-mode-section" data-mode-section="formula" <?= $mode === 'formula' ? '' : 'style="display:none;"'; ?>>
                <div class="ccx-section">
                    <?php $sectionId = 'ccx-formula-expression-' . $columnIndex; ?>
                    <div class="ccx-section__header">
                        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                            <span><?= html_escape(ccx_lang('ccx_template_formula_expression', 'Formula Expression')); ?></span>
                            <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_formula_expression_hint', 'Use {{reference_key}} or {{column:identifier}} placeholders.')); ?></span>
                            <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                        </button>
                    </div>
                    <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                        <textarea name="columns[<?= html_escape($columnIndex); ?>][formula_expression]" class="form-control" rows="3" data-mode-required="formula" placeholder="{{metric_a}} / {{metric_b}} * 100" <?= $mode === 'formula' ? 'required' : ''; ?>><?= html_escape($formulaExpression); ?></textarea>
                        <div class="tw-text-xs tw-text-neutral-500 tw-mt-2">
                            <?= html_escape(ccx_lang('ccx_template_formula_notice', 'Add one or more metric sources, then reference their keys in the expression.')); ?>
                        </div>
                        <div class="tw-text-xs tw-text-neutral-500 tw-mt-2">
                            <?= html_escape(ccx_lang('ccx_template_formula_column_hint', 'Reference earlier columns via tags like {{column:col_1}}.')); ?>
                        </div>
                        <div class="ccx-formula-keys tw-flex tw-flex-wrap tw-gap-2 tw-mt-3">
                            <?php foreach ($formulaSources as $sourceChip) { ?>
                                <span class="badge badge-info">{{<?= html_escape($sourceChip['key']); ?>}}</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="ccx-section">
                    <?php $sectionId = 'ccx-formula-sources-' . $columnIndex; ?>
                    <div class="ccx-section__header">
                        <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                            <span><?= html_escape(ccx_lang('ccx_template_formula_sources_heading', 'Formula Sources')); ?></span>
                            <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_formula_hint', 'Reference metrics or constants and reuse them in expressions.')); ?></span>
                            <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                        </button>
                    </div>
                    <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                        <div class="ccx-formula-sources" data-column-index="<?= html_escape($columnIndex); ?>">
                            <?php
                            if (! empty($formulaSources)) {
                                foreach ($formulaSources as $sourceCard) {
                                    echo ccx_render_formula_source_card($columnIndex, $sourceCard, $aggregateMap, $tableOptions, $columnsMap);
                                }
                            }
                            ?>
                        </div>
                        <button class="btn btn-outline-primary ccx-pill-btn ccx-add-formula-source" type="button" data-column-index="<?= html_escape($columnIndex); ?>">
                            <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_formula_add_source', 'Add Source')); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="ccx-section">
                <?php $sectionId = 'ccx-display-' . $columnIndex; ?>
                <div class="ccx-section__header">
                    <button class="ccx-accordion-toggle" type="button" data-toggle="collapse" data-target="#<?= html_escape($sectionId); ?>" aria-expanded="true">
                        <span><?= html_escape(ccx_lang('ccx_template_display_settings', 'Display Settings')); ?></span>
                        <span class="ccx-section__description d-none d-sm-inline"><?= html_escape(ccx_lang('ccx_template_display_hint', 'Control rounding and formatting.')); ?></span>
                        <i class="fa fa-chevron-down ccx-accordion-icon"></i>
                    </button>
                </div>
                <div id="<?= html_escape($sectionId); ?>" class="ccx-section__body collapse show" data-parent="#<?= html_escape($columnCollapseId); ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group ccx-decimal-group">
                                <label><?= html_escape(ccx_lang('ccx_template_field_decimal', 'Decimal Places')); ?></label>
                                <input type="number" min="0" max="10" name="columns[<?= html_escape($columnIndex); ?>][decimal_places]" class="form-control" value="<?= ($decimalPlaces === '' && $decimalPlaces !== 0) ? '' : html_escape($decimalPlaces); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
