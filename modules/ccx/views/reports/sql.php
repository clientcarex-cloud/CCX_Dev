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
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.25), transparent 60%);
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
    .ccx-sql-card {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 24px;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.12);
        margin-bottom: 26px;
    }
    .ccx-sql-filters h5 {
        margin-top: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .ccx-sql-table-wrapper {
        overflow-x: auto;
    }
    @media (max-width: 768px) {
        .ccx-detail-hero {
            padding: 26px;
        }
    }
</style>
<?php
$canExport   = is_admin() || staff_can('export', 'ccx_reports');
$exportUrl   = null;
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
                <a href="<?= admin_url('ccx/reports'); ?>" class="ccx-ghost-btn">
                    <span class="ccx-ghost-icon"><i class="fa fa-arrow-left"></i></span>
                    <span><?= html_escape(ccx_lang('ccx_reports_sql_back', 'Back to Reports')); ?></span>
                </a>
                <?php if ($canExport && $exportUrl !== null) { ?>
                    <a href="<?= html_escape($exportUrl); ?>" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> <?= html_escape(ccx_lang('ccx_reports_export_excel', 'Export to Excel')); ?>
                    </a>
                <?php } ?>
               <?php if (staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin()) { ?>
                    <a href="<?= admin_url('ccx/template/' . (int) $template['id']); ?>" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> <?= html_escape(ccx_lang('ccx_reports_template_actions', 'Manage')); ?>
                    </a>
                <?php } ?>
            </div>
        </div>

        <?php if (! empty($filters)) { ?>
            <div class="ccx-sql-card ccx-sql-filters">
                <h5><i class="fa fa-filter tw-mr-2"></i><?= html_escape(ccx_lang('ccx_reports_runtime_filters', 'Runtime Filters')); ?></h5>
                <form method="get" class="tw-space-y-3 tw-mt-3">
                    <div class="row">
                        <?php foreach ($filters as $filter) { ?>
                            <div class="col-md-4">
                                <div class="form-group <?= $filter['error'] ? 'has-error' : ''; ?>">
                                    <label class="control-label" for="filter_<?= html_escape($filter['key']); ?>">
                                        <?= html_escape($filter['label']); ?>
                                        <?php if ($filter['required']) { ?>
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

        <?php if ($query_error !== null) { ?>
            <div class="ccx-sql-card">
                <div class="alert alert-danger tw-m-0">
                    <?= html_escape($query_error); ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="ccx-sql-card ccx-sql-table-wrapper">
                <?php if ($row_limit !== null) { ?>
                    <div class="alert alert-info tw-mb-3">
                        <?= sprintf(ccx_lang('ccx_reports_sql_row_limit', 'Showing the first %d rows.'), (int) $row_limit); ?>
                    </div>
                <?php } ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column) { ?>
                                <th><?= html_escape($column); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)) { ?>
                            <tr>
                                <td colspan="<?= max(1, count($columns)); ?>" class="text-center">
                                    <?= html_escape(ccx_lang('ccx_reports_sql_empty', 'No records returned.')); ?>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($rows as $row) { ?>
                                <tr>
                                    <?php foreach ($columns as $column) { ?>
                                        <td><?= html_escape($row[$column] ?? ''); ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php init_tail(); ?>
