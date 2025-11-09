<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-detail-hero {
        background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 48%, #020617 100%);
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
        background: radial-gradient(circle at 18% 22%, rgba(255,255,255,0.22), transparent 60%);
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
        max-width: 640px;
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
    .ccx-dynamic-nav {
        border-bottom: none;
        display: inline-flex;
        gap: 12px;
        margin-bottom: 24px;
    }
    .ccx-dynamic-nav > li > a {
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.16);
        padding: 10px 22px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 600;
    }
    .ccx-dynamic-nav > li.active > a,
    .ccx-dynamic-nav > li > a:hover,
    .ccx-dynamic-nav > li > a:focus {
        background: #2563eb;
        color: #f8fafc;
        border-color: #1d4ed8;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.18);
    }
    .ccx-dynamic-pane {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 28px 30px;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
        margin-bottom: 28px;
    }
    .ccx-dynamic-filters {
        margin-bottom: 24px;
    }
    .ccx-dynamic-html {
        background: #f8fafc;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 24px;
        margin-bottom: 26px;
        overflow: hidden;
    }
    .ccx-dynamic-table-wrapper {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 18px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
    }
    .ccx-dynamic-alert {
        margin-bottom: 18px;
    }
    @media (max-width: 768px) {
        .ccx-detail-hero {
            padding: 24px;
        }
        .ccx-dynamic-pane {
            padding: 22px;
        }
    }
</style>
<?php
$activePage = isset($activePage) && in_array($activePage, array_keys($pages ?? []), true) ? $activePage : 'main';
$reportUrl  = admin_url('ccx/report/' . (int) $template['id']);
$canManageTemplate = staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin();
$canExport   = is_admin() || staff_can('export', 'ccx_reports');
$exportParams = isset($exportParams) && is_array($exportParams) ? $exportParams : [];
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
                    <?php if ($canManageTemplate) { ?>
                        <a href="<?= admin_url('ccx/template/' . (int) $template['id']); ?>" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> <?= html_escape(ccx_lang('ccx_reports_template_actions', 'Manage')); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs ccx-dynamic-nav" role="tablist">
            <?php foreach (($pages ?? []) as $key => $page) {
                $isActive = $key === $activePage;
                ?>
                <li role="presentation" class="<?= $isActive ? 'active' : ''; ?>">
                    <a href="#ccx-report-<?= html_escape($key); ?>" aria-controls="ccx-report-<?= html_escape($key); ?>" role="tab" data-toggle="tab">
                        <?= html_escape($page['label'] ?? ucfirst($key)); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <div class="tab-content">
            <?php foreach (($pages ?? []) as $key => $page) {
                $isActive = $key === $activePage;
                $filters  = $page['filters'] ?? [];
                $result   = $page['result'] ?? ['columns' => [], 'rows' => [], 'row_limit' => null];
                $queryError = $page['query_error'] ?? null;
                $hasQuery = trim((string) ($page['sql_query'] ?? '')) !== '';
                $pageExportUrl = null;
                if ($canExport) {
                    $exportParamsForPage = $exportParams;
                    $exportParamsForPage['page'] = $key;
                    $pageExportUrl = admin_url('ccx/report_export/' . (int) $template['id']);
                    if (! empty($exportParamsForPage)) {
                        $pageExportUrl .= '?' . http_build_query($exportParamsForPage);
                    }
                }
                ?>
                <div role="tabpanel" class="tab-pane fade <?= $isActive ? 'active in' : ''; ?>" id="ccx-report-<?= html_escape($key); ?>">
                    <div class="ccx-dynamic-pane">
                        <?php if (! empty($filters)) { ?>
                            <div class="ccx-dynamic-filters">
                                <form method="get" class="row">
                                    <input type="hidden" name="page" value="<?= html_escape($key); ?>">
                                    <?php foreach ($filters as $filter) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group <?= ! empty($filter['error']) ? 'has-error' : ''; ?>">
                                                <label class="control-label" for="filter_<?= html_escape($key . '_' . $filter['key']); ?>">
                                                    <?= html_escape($filter['label']); ?>
                                                    <?php if (! empty($filter['required'])) { ?>
                                                        <span class="text-danger">*</span>
                                                    <?php } ?>
                                                </label>
                                                <?php
                                                $fieldName = sprintf('filters_%s[%s]', $key, $filter['key']);
                                                $inputId   = 'filter_' . $key . '_' . $filter['key'];
                                                $value     = isset($filter['value']) ? (string) $filter['value'] : '';
                                                $placeholder = isset($filter['placeholder']) ? (string) $filter['placeholder'] : '';
                                                switch ($filter['type']) {
                                                    case 'select':
                                                        ?>
                                                        <select class="form-control" id="<?= html_escape($inputId); ?>" name="<?= html_escape($fieldName); ?>">
                                                            <option value=""><?= html_escape(ccx_lang('ccx_template_sql_filters_select_placeholder', 'Select...')); ?></option>
                                                            <?php foreach (($filter['options'] ?? []) as $option) { ?>
                                                                <option value="<?= html_escape($option['value']); ?>" <?= ($value !== '' && (string) $value === (string) $option['value']) ? 'selected' : ''; ?>>
                                                                    <?= html_escape($option['label']); ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                        <?php
                                                        break;
                                                    case 'number':
                                                        ?>
                                                        <input type="number" step="any" id="<?= html_escape($inputId); ?>" name="<?= html_escape($fieldName); ?>" class="form-control" value="<?= html_escape($value); ?>" <?= $placeholder !== '' ? 'placeholder="' . html_escape($placeholder) . '"' : ''; ?>>
                                                        <?php
                                                        break;
                                                    case 'date':
                                                        ?>
                                                        <input type="date" id="<?= html_escape($inputId); ?>" name="<?= html_escape($fieldName); ?>" class="form-control" value="<?= html_escape($value); ?>">
                                                        <?php
                                                        break;
                                                    case 'datetime':
                                                        ?>
                                                        <input type="datetime-local" id="<?= html_escape($inputId); ?>" name="<?= html_escape($fieldName); ?>" class="form-control" value="<?= html_escape($value); ?>">
                                                        <?php
                                                        break;
                                                    default:
                                                        ?>
                                                        <input type="text" id="<?= html_escape($inputId); ?>" name="<?= html_escape($fieldName); ?>" class="form-control" value="<?= html_escape($value); ?>" <?= $placeholder !== '' ? 'placeholder="' . html_escape($placeholder) . '"' : ''; ?>>
                                                        <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-12 tw-mt-2">
                                        <div class="btn-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> <?= html_escape(ccx_lang('ccx_reports_sql_filters_apply', 'Apply Filters')); ?>
                                            </button>
                                            <a href="<?= html_escape($reportUrl . '?page=' . $key); ?>" class="btn btn-default">
                                                <?= html_escape(ccx_lang('ccx_reports_sql_filters_reset', 'Reset Filters')); ?>
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php } ?>

                        <?php if ($queryError) { ?>
                            <div class="alert alert-danger ccx-dynamic-alert">
                                <?= html_escape($queryError); ?>
                            </div>
                        <?php } ?>

                        <?php if (! empty($page['html_content'])) { ?>
                            <div class="ccx-dynamic-html">
                                <?= $page['html_content']; ?>
                            </div>
                        <?php } ?>

                        <?php if ($hasQuery && ! empty($result['columns'])) { ?>
                            <?php if ($canExport && $pageExportUrl !== null) { ?>
                                <div class="tw-flex tw-justify-end tw-mb-3">
                                    <a href="<?= html_escape($pageExportUrl); ?>" class="btn btn-success">
                                        <i class="fa fa-file-excel-o"></i> <?= html_escape(ccx_lang('ccx_reports_export_excel', 'Export to Excel')); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <div class="ccx-dynamic-table-wrapper">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <?php foreach ($result['columns'] as $columnLabel) { ?>
                                                    <th><?= html_escape($columnLabel); ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($result['rows'] as $row) { ?>
                                            <tr>
                                                <?php foreach ($result['columns'] as $columnLabel) {
                                                    $value = $row[$columnLabel] ?? '';
                                                    $valueString = (string) $value;
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($valueString === '') {
                                                            echo '&mdash;';
                                                        } elseif ($columnLabel === 'Task Name') {
                                                            $parts = explode('||', $valueString, 2);
                                                            $taskId = isset($parts[0]) ? (int) $parts[0] : 0;
                                                            $taskTitle = isset($parts[1]) ? trim($parts[1]) : '';
                                                            if ($taskTitle === '') {
                                                                $taskTitle = $taskId > 0 ? 'Task #' . $taskId : '-';
                                                            }
                                                            if ($taskId > 0) {
                                                                echo '<a href="#" onclick="init_task_modal(' . $taskId . '); return false;">' . html_escape($taskTitle) . '</a>';
                                                            } else {
                                                                echo html_escape($taskTitle);
                                                            }
                                                        } elseif (preg_match('/<(a|button|span|div|p)\b/i', $valueString)) {
                                                            echo $valueString;
                                                        } else {
                                                            echo html_escape($valueString);
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (! empty($result['row_limit'])) { ?>
                                    <p class="tw-text-xs tw-text-neutral-500 tw-mt-2">
                                        <?= sprintf(html_escape(ccx_lang('ccx_reports_sql_row_limit', 'Showing the first %d rows.')), (int) $result['row_limit']); ?>
                                    </p>
                                <?php } ?>
                            </div>
                        <?php } elseif ($hasQuery) { ?>
                            <div class="alert alert-info ccx-dynamic-alert">
                                <?= html_escape(ccx_lang('ccx_reports_sql_empty', 'No records returned.')); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function($) {
    "use strict";
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('href') || '';
        const segments = target.split('-');
        const matched = segments.length ? segments[segments.length - 1] : '';
        if (!matched) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('page', matched);

        if (matched !== 'sub') {
            Array.from(url.searchParams.keys()).forEach(function(key) {
                if (key.indexOf('filters_sub[') === 0) {
                    url.searchParams.delete(key);
                }
            });
        }

        history.replaceState(null, '', url.toString());
    });
})(jQuery);
</script>
</body>
</html>
