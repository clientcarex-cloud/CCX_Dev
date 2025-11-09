<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-detail-hero {
        background: linear-gradient(140deg, #1d4ed8 0%, #0f172a 55%, #020617 100%);
        border-radius: 22px;
        padding: 34px 40px;
        color: #e2e8f0;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .ccx-detail-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient( circle at 20% 20%, rgba(255,255,255,0.25), transparent 60% );
        pointer-events: none;
    }
    .ccx-detail-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 10px;
    }
    .ccx-detail-hero p {
        margin: 0;
        font-size: 15px;
        opacity: 0.85;
        max-width: 620px;
    }
    .ccx-detail-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 24px;
    }
    .ccx-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.18);
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 500;
    }
    .ccx-ghost-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 16px;
        border: 1px solid rgba(226, 232, 240, 0.55);
        background: rgba(15, 23, 42, 0.08);
        color: #e2e8f0 !important;
        text-decoration: none;
        font-weight: 500;
        font-size: 13px;
        transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;
        box-shadow: inset 0 0 1px rgba(255, 255, 255, 0.2);
    }
    .ccx-ghost-btn:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.32);
        transform: translateY(-1px);
    }
    .ccx-ghost-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(30, 64, 175, 0.25);
        color: #bfdbfe;
        font-size: 12px;
    }
    .ccx-meta-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 26px;
    }
    .ccx-meta-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 22px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }
    .ccx-meta-card h3 {
        font-size: 15px;
        font-weight: 600;
        color: #475569;
        margin: 0 0 8px;
    }
    .ccx-meta-card ul {
        margin: 0;
        padding-left: 18px;
        max-height: 144px;
        overflow-y: auto;
        font-size: 13px;
        color: #0f172a;
    }
    .ccx-table-wrapper {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 24px;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.12);
    }
    .ccx-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        gap: 16px;
    }
    .ccx-filter-card {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 24px;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.12);
        margin-bottom: 26px;
    }
    .ccx-filter-card h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
    }
</style>
<?php
$canExport = is_admin() || staff_can('export', 'ccx_reports');
$exportUrl = null;
if ($canExport) {
    $exportParams = isset($exportParams) && is_array($exportParams) ? $exportParams : [];
    $exportUrl    = admin_url('ccx/report_export/' . (int) $template['id']);
    if (! empty($exportParams)) {
        $exportUrl .= '?' . http_build_query($exportParams);
    }
}
?>
<div id="wrapper">
    <div class="content">
        <div class="ccx-detail-hero">
            <h1><?= html_escape($template['name']); ?></h1>
            <p><?= html_escape($template['description'] ?: ccx_lang('ccx_reports_card_placeholder', 'Launch detailed insights in seconds.')); ?></p>
            <div class="ccx-detail-actions">
                <div class="tw-flex tw-gap-2">
                    <a href="<?= admin_url('ccx/reports'); ?>" class="ccx-ghost-btn">
                        <span class="ccx-ghost-icon"><i class="fa fa-arrow-left"></i></span>
                        <span><?= html_escape(ccx_lang('ccx_reports_back_to_list', 'Back to Reports')); ?></span>
                    </a>
                    <?php if (staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin()) { ?>
                        <a href="<?= admin_url('ccx/template/' . (int) $template['id']); ?>" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> <?= html_escape(ccx_lang('ccx_reports_template_actions', 'Manage')); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php
        $headers = [];
        foreach ($columns as $index => $column) {
            $label = trim((string) ($column['label'] ?? ''));
            if ($label === '') {
                $label = ccx_lang('ccx_reports_column_header', 'Column') . ' ' . ($index + 1);
            }
            $headers[] = html_escape($label);
        }
        ?>

        <?php if (! empty($filters)) { ?>
            <div class="ccx-filter-card">
                <h5><i class="fa fa-filter tw-mr-2"></i><?= html_escape(ccx_lang('ccx_reports_runtime_filters', 'Runtime Filters')); ?></h5>
                <?php if (! empty($filters_has_errors)) { ?>
                    <div class="alert alert-danger tw-mt-3">
                        <?= html_escape(ccx_lang('ccx_template_sql_filters_invalid', 'Filters could not be applied. Please review the highlighted fields.')); ?>
                    </div>
                <?php } ?>
                <form method="get" class="tw-space-y-3 tw-mt-3">
                    <div class="row">
                        <?php foreach ($filters as $filter) { ?>
                            <div class="col-md-4">
                                <div class="form-group <?= $filter['error'] ? 'has-error' : ''; ?>">
                                    <label class="control-label" for="filter_<?= html_escape($filter['key']); ?>">
                                        <?= html_escape($filter['label']); ?>
                                        <?php if (! empty($filter['required'])) { ?>
                                            <span class="text-danger">*</span>
                                        <?php } ?>
                                    </label>
                                    <?php if ($filter['type'] === 'select') { ?>
                                        <select id="filter_<?= html_escape($filter['key']); ?>" name="filters[<?= html_escape($filter['key']); ?>]" class="form-control">
                                            <option value=""><?= html_escape(ccx_lang('ccx_template_sql_filters_select_placeholder', 'Select...')); ?></option>
                                            <?php foreach ($filter['options'] as $option) { ?>
                                                <option value="<?= html_escape($option['value']); ?>" <?= ($filter['value'] !== '' && (string) $filter['value'] === (string) $option['value']) ? 'selected' : ''; ?>>
                                                    <?= html_escape($option['label']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    <?php } elseif ($filter['type'] === 'number') { ?>
                                        <input type="number" step="any" id="filter_<?= html_escape($filter['key']); ?>" name="filters[<?= html_escape($filter['key']); ?>]" class="form-control" value="<?= html_escape($filter['value']); ?>" <?= $filter['placeholder'] !== '' ? 'placeholder="' . html_escape($filter['placeholder']) . '"' : ''; ?>>
                                    <?php } elseif ($filter['type'] === 'date') { ?>
                                        <input type="date" id="filter_<?= html_escape($filter['key']); ?>" name="filters[<?= html_escape($filter['key']); ?>]" class="form-control" value="<?= html_escape($filter['value']); ?>" <?= $filter['placeholder'] !== '' ? 'placeholder="' . html_escape($filter['placeholder']) . '"' : ''; ?>>
                                    <?php } elseif ($filter['type'] === 'datetime') { ?>
                                        <input type="datetime-local" id="filter_<?= html_escape($filter['key']); ?>" name="filters[<?= html_escape($filter['key']); ?>]" class="form-control" value="<?= html_escape($filter['value']); ?>" <?= $filter['placeholder'] !== '' ? 'placeholder="' . html_escape($filter['placeholder']) . '"' : ''; ?>>
                                    <?php } else { ?>
                                        <input type="text" id="filter_<?= html_escape($filter['key']); ?>" name="filters[<?= html_escape($filter['key']); ?>]" class="form-control" value="<?= html_escape($filter['value']); ?>" <?= $filter['placeholder'] !== '' ? 'placeholder="' . html_escape($filter['placeholder']) . '"' : ''; ?>>
                                    <?php } ?>

                                    <?php if ($filter['description']) { ?>
                                        <p class="help-block tw-mb-1"><?= html_escape($filter['description']); ?></p>
                                    <?php } ?>

                                    <?php if ($filter['error']) { ?>
                                        <p class="text-danger help-block tw-mt-1"><?= html_escape($filter['error']); ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="tw-flex tw-gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter mright5"></i><?= html_escape(ccx_lang('ccx_reports_sql_filters_apply', 'Apply Filters')); ?>
                        </button>
                        <a href="<?= admin_url('ccx/report/' . (int) $template['id']); ?>" class="btn btn-default">
                            <i class="fa fa-refresh mright5"></i><?= html_escape(ccx_lang('ccx_reports_sql_filters_reset', 'Reset Filters')); ?>
                        </a>
                    </div>
                </form>
            </div>
        <?php } ?>

        <div class="ccx-table-wrapper">
            <?php
            if (empty($headers)) {
                echo '<div class="alert alert-info tw-m-0">' . html_escape(ccx_lang('ccx_reports_no_columns', 'No columns have been configured for this template yet.')) . '</div>';
            } else {
                if (! empty($table)) {
                    echo '<div class="ccx-table-toolbar">';
                    echo '<h4 class="tw-text-base tw-font-semibold tw-text-slate-700 tw-m-0">' . html_escape(ccx_lang('ccx_reports_dataset_heading', 'Report Dataset')) . '</h4>';
                    echo '<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">';
                    echo '  <div class="ccx-date-range tw-flex tw-items-center tw-gap-2">';
                    echo '      <div class="input-group">';
                    echo '          <span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
                    echo '          <input type="text" name="ccx_date_from" class="form-control datepicker" autocomplete="off" placeholder="' . html_escape(ccx_lang('ccx_reports_date_from', 'From')) . '">';
                    echo '      </div>';
                    echo '      <div class="input-group">';
                    echo '          <span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
                    echo '          <input type="text" name="ccx_date_to" class="form-control datepicker" autocomplete="off" placeholder="' . html_escape(ccx_lang('ccx_reports_date_to', 'To')) . '">';
                    echo '      </div>';
                    echo '      <button type="button" class="btn btn-default" id="ccx-date-apply">' . html_escape(ccx_lang('ccx_reports_date_apply', 'Apply')) . '</button>';
                    echo '      <button type="button" class="btn btn-light" id="ccx-date-clear">' . html_escape(ccx_lang('ccx_reports_date_clear', 'Clear')) . '</button>';
                    echo '  </div>';
                    echo '  <div id="vueApp">';
                    echo '      <app-filters id="' . html_escape($table->id()) . '" view="' . html_escape($table->viewName()) . '" :saved-filters="' . $table->filtersJs() . '" :available-rules="' . $table->rulesJs() . '"></app-filters>';
                    echo '  </div>';
                    if ($canExport && $exportUrl !== null) {
                        echo '  <a href="' . html_escape($exportUrl) . '" class="btn btn-success">';
                        echo '      <i class="fa fa-file-excel-o"></i> ' . html_escape(ccx_lang('ccx_reports_export_excel', 'Export to Excel'));
                        echo '  </a>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                render_datatable(
                    $headers,
                    $tableSlug,
                    [],
                    [
                        'id'                         => $tableSlug,
                        'data-last-order-identifier' => $tableSlug,
                        'data-default-order'         => get_table_last_order($tableSlug),
                    ]
                );
            }
            ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<?php if (! empty($headers)) { ?>
<script>
(function($) {
    "use strict";
    var serverParams = {};
    serverParams['date_from'] = '[name="ccx_date_from"]';
    serverParams['date_to']   = '[name="ccx_date_to"]';
    <?php
    $filterKeys = [];
    if (! empty($filters)) {
        foreach ($filters as $filter) {
            $filterKeys[] = $filter['key'];
            ?>
    serverParams['runtime_filters[<?= html_escape($filter['key']); ?>]'] = '[name="filters[<?= html_escape($filter['key']); ?>]"]';
            <?php
        }
    }
    if (! in_array('date_from', $filterKeys, true)) {
        ?>
    serverParams['runtime_filters[date_from]'] = '[name="ccx_date_from"]';
        <?php
    }
    if (! in_array('date_to', $filterKeys, true)) {
        ?>
    serverParams['runtime_filters[date_to]'] = '[name="ccx_date_to"]';
        <?php
    }
    ?>

    var ccxTableSelector = '.table-<?= html_escape($tableSlug); ?>';

    initDataTable(
        ccxTableSelector,
        admin_url + 'ccx/report_table/<?= (int) $template['id']; ?>',
        undefined,
        undefined,
        serverParams
    );

    appDatepicker();

    $('[name="ccx_date_from"], [name="ccx_date_to"]').on('change', function() {
        $(ccxTableSelector).DataTable().ajax.reload(null, false);
    });

    $('#ccx-date-apply').on('click', function() {
        $(ccxTableSelector).DataTable().ajax.reload(null, false);
    });

    $('#ccx-date-clear').on('click', function() {
        $('[name="ccx_date_from"], [name="ccx_date_to"]').val('');
        $(ccxTableSelector).DataTable().ajax.reload(null, false);
    });
})(jQuery);
</script>
<?php } ?>
</body>
</html>
