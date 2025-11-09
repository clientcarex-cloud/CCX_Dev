<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-form-hero {
        background: radial-gradient(circle at top left, rgba(14,165,233,0.35), transparent),
                    linear-gradient(135deg, #0F172A 0%, #1D4ED8 50%, #2563EB 100%);
        border-radius: 24px;
        padding: 32px 36px;
        color: #E2E8F0;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .ccx-form-hero::after {
        content: "";
        position: absolute;
        right: -60px;
        top: -60px;
        width: 220px;
        height: 220px;
        background: rgba(14,165,233,0.15);
        border-radius: 50%;
        filter: blur(2px);
    }
    .ccx-hero-content {
        position: relative;
        z-index: 2;
    }
    .ccx-form-hero h1 {
        margin: 0 0 6px;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }
    .ccx-form-hero p {
        margin: 0;
        font-size: 15px;
        opacity: 0.85;
        max-width: 560px;
    }
    .ccx-hero-pills {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }
    .ccx-hero-pill {
        background: rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.35);
        color: #E2E8F0;
        border-radius: 999px;
        padding: 7px 14px;
        font-size: 12px;
        letter-spacing: 0.2px;
    }
    .ccx-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #E2E8F0;
        font-weight: 500;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(226, 232, 240, 0.3);
        background: rgba(15, 23, 42, 0.22);
        transition: background 160ms ease, transform 160ms ease;
    }
    .ccx-back-link:hover {
        background: rgba(226, 232, 240, 0.18);
        transform: translateY(-1px);
    }
    .ccx-layout {
        display: flex;
        flex-direction: column;
        gap: 26px;
    }
    .ccx-card {
        background: #FFFFFF;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        padding: 26px 30px;
    }
    .ccx-card h2 {
        margin: 0 0 18px;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
    }
    .ccx-card h3 {
        font-size: 14px;
        font-weight: 600;
        color: #0F172A;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 12px;
    }
    .ccx-form-grid {
        display: grid;
        gap: 18px;
    }
    .ccx-form-grid label {
        font-weight: 600;
        color: #1E293B;
        letter-spacing: 0.2px;
    }
    .ccx-columns-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 0 0 18px;
    }
    .ccx-pill-btn {
        border-radius: 999px;
        padding: 10px 18px;
        font-weight: 600;
    }
    .ccx-columns-empty {
        border: 1px dashed rgba(71, 85, 105, 0.35);
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        color: #475569;
        background: rgba(248, 250, 252, 0.9);
    }
    .ccx-guide-card {
        background: linear-gradient(145deg, rgba(30,64,175,0.08), rgba(30,64,175,0.18));
        border-radius: 18px;
        padding: 20px;
        border: 1px solid rgba(30, 64, 175, 0.12);
        margin-bottom: 20px;
    }
    .ccx-guide-card h4 {
        margin: 0 0 10px;
        font-size: 14px;
        font-weight: 600;
        color: #1E3A8A;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .ccx-step-list {
        margin: 0;
        padding-left: 18px;
        color: #0f172a;
    }
    .ccx-step-list li {
        margin-bottom: 6px;
    }
    .ccx-form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 18px;
    }
    .ccx-template-panel {
        margin-top: 26px;
    }
    .ccx-template-panel.ccx-is-hidden {
        display: none;
    }
    .ccx-dynamic-nav {
        border-bottom: none;
        display: inline-flex;
        gap: 10px;
    }
    .ccx-dynamic-nav > li > a {
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 999px;
        padding: 10px 20px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 600;
        transition: background 160ms ease, color 160ms ease, box-shadow 160ms ease;
    }
    .ccx-dynamic-nav > li.active > a,
    .ccx-dynamic-nav > li > a:focus,
    .ccx-dynamic-nav > li > a:hover {
        background: #2563eb;
        color: #fafafa;
        border-color: #1d4ed8;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }
    .ccx-dynamic-pane {
        padding: 24px 0 10px;
    }
    .ccx-dynamic-pane .form-group + .form-group {
        margin-top: 18px;
    }
    .ccx-dynamic-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }
    .ccx-is-hidden {
        display: none !important;
    }
    .ccx-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1080;
        min-width: 320px;
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.24);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.22);
        opacity: 0;
        transform: translateY(15px);
        pointer-events: none;
        transition: opacity 200ms ease, transform 200ms ease;
    }
    .ccx-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .ccx-toast-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 18px 10px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .ccx-toast-body {
        padding: 16px 18px 18px;
        color: rgba(226, 232, 240, 0.92);
        font-size: 13px;
        line-height: 1.5;
    }
    @media (max-width: 768px) {
        .ccx-form-hero {
            padding: 24px;
        }
        .ccx-card {
            padding: 22px 24px;
        }
        .ccx-toast {
            right: 12px;
            left: 12px;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-form-hero">
            <div class="ccx-hero-content">
                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
                    <a href="<?= admin_url('ccx/templates'); ?>" class="ccx-back-link">
                        <i class="fa fa-arrow-left"></i>
                        <?= html_escape(ccx_lang('ccx_reports_back_to_list', 'Back to Reports')); ?>
                    </a>
                </div>
                <h1><?= html_escape($title); ?></h1>
                <p><?= html_escape(ccx_lang('ccx_templates_intro', 'Design multi-table metrics, reusable filters and polished datasets without writing SQL by hand.')); ?></p>
            </div>
        </div>

        <div class="ccx-layout">
            <div class="ccx-card">
                <?php
                $action = 'ccx/template' . (isset($template['id']) ? '/' . (int) $template['id'] : '');
                echo form_open(admin_url($action), ['id' => 'ccx-template-form']);
                ?>
                <div class="ccx-form-grid">
                    <div class="form-group">
                        <label for="template-name"><?= html_escape(ccx_lang('ccx_template_field_name', 'Report Name')); ?> *</label>
                        <input type="text" id="template-name" name="template[name]" class="form-control" value="<?= html_escape($template['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="template-description"><?= html_escape(ccx_lang('ccx_template_field_description', 'Description')); ?></label>
                        <textarea id="template-description" name="template[description]" class="form-control" rows="3" placeholder="<?= html_escape(ccx_lang('ccx_template_intro_hint', 'Explain who should use this insight and what it answers.')); ?>"><?= html_escape($template['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="template-type"><?= html_escape(ccx_lang('ccx_template_field_type', 'Template Type')); ?></label>
                        <select id="template-type" name="template[type]" class="form-control">
                            <option value="smart" <?= ($templateType ?? 'smart') === 'smart' ? 'selected' : ''; ?>>
                                <?= html_escape(ccx_lang('ccx_template_type_smart', 'Smart Report')); ?>
                            </option>
                            <option value="sql" <?= ($templateType ?? 'smart') === 'sql' ? 'selected' : ''; ?>>
                                <?= html_escape(ccx_lang('ccx_template_type_sql', 'Custom SQL Query')); ?>
                            </option>
                            <option value="dynamic" <?= ($templateType ?? 'smart') === 'dynamic' ? 'selected' : ''; ?>>
                                <?= html_escape(ccx_lang('ccx_template_type_dynamic', 'Dynamic High Level')); ?>
                            </option>
                        </select>
                        <small class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_type_hint', 'Switch between the visual designer and a custom SQL query.')); ?></small>
                    </div>
                </div>

                <div class="ccx-template-panel <?= ($templateType ?? 'smart') === 'sql' ? 'ccx-is-hidden' : ''; ?>" data-template-panel="smart">
                    <div class="ccx-columns-header">
                        <div>
                            <h2><?= html_escape(ccx_lang('ccx_template_columns_heading', 'Columns')); ?></h2>
                            <p class="tw-text-xs tw-text-neutral-500 tw-mb-0"><?= html_escape(ccx_lang('ccx_template_column_hint', 'Each column can join extra tables, add filters and format currencies individually.')); ?></p>
                        </div>
                        <button class="btn btn-outline-primary ccx-pill-btn" id="ccx-add-column">
                            <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_add_column', 'Add Column')); ?>
                        </button>
                    </div>

                    <div id="ccx-columns-container">
                        <?php
                        $tableOptions = $tableOptions ?? [];
                        $columnsMap   = $columnsMap ?? [];
                        $existingColumns = ! empty($columns) ? $columns : [];
                        if (empty($existingColumns)) {
                            echo '<div class="ccx-columns-empty"><i class="fa fa-columns tw-mr-2"></i>' . html_escape(ccx_lang('ccx_templates_empty_hint', 'No columns yet. Start by adding a metric or formula.')) . '</div>';
                        }
                        foreach ($existingColumns as $index => $column) {
                            $this->load->view('ccx/templates/partials/column', [
                                'index'        => $index,
                                'column'       => $column,
                                'aggregateMap' => $aggregateMap,
                                'tableOptions' => $tableOptions,
                                'columnsMap'   => $columnsMap,
                            ]);
                        }
                        ?>
                    </div>
                </div>

                <div class="ccx-template-panel <?= ($templateType ?? 'smart') === 'sql' ? '' : 'ccx-is-hidden'; ?>" data-template-panel="sql">
                    <div class="form-group">
                        <label for="sql_query"><?= html_escape(ccx_lang('ccx_template_sql_query', 'SQL Query')); ?></label>
                        <textarea id="sql_query" name="sql_query" rows="12" class="form-control code-field" spellcheck="false"><?= html_escape($sqlTemplate['sql_query'] ?? ''); ?></textarea>
                        <p class="tw-text-xs tw-text-neutral-500 tw-mt-1">
                            <?= html_escape(ccx_lang('ccx_template_sql_query_hint', 'Only read-only statements such as SELECT, SHOW, DESCRIBE or WITH clauses are allowed. Use {{db_prefix}} for the CRM table prefix and {{filter:your_key}} to inject filter values.')); ?>
                        </p>
                    </div>
                    </div>
                <div class="ccx-template-panel <?= ($templateType ?? 'smart') === 'dynamic' ? '' : 'ccx-is-hidden'; ?>" data-template-panel="dynamic">
                    <?php
                    $dynamicMain = $dynamicPages['main'] ?? ['sql_query' => '', 'html_content' => '', 'filters_json' => ''];
                    $dynamicSub  = $dynamicPages['sub'] ?? ['sql_query' => '', 'html_content' => '', 'filters_json' => ''];
                    ?>
                    <div class="tw-flex tw-flex-col tw-gap-2 tw-mb-4">
                        <h2><?= html_escape(ccx_lang('ccx_template_dynamic_heading', 'Dynamic Pages')); ?></h2>
                        <p class="tw-text-xs tw-text-neutral-500 tw-mb-0"><?= html_escape(ccx_lang('ccx_template_dynamic_intro', 'Define the primary view and an optional sub page with custom SQL, HTML layouts and runtime filters.')); ?></p>
                    </div>
                    <ul class="nav nav-tabs ccx-dynamic-nav" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#ccx-dynamic-main" aria-controls="ccx-dynamic-main" role="tab" data-toggle="tab">
                                <?= html_escape(ccx_lang('ccx_template_dynamic_page_main', 'Main Page')); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#ccx-dynamic-sub" aria-controls="ccx-dynamic-sub" role="tab" data-toggle="tab">
                                <?= html_escape(ccx_lang('ccx_template_dynamic_page_sub', 'Sub Page')); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content ccx-dynamic-pane-container">
                        <div role="tabpanel" class="tab-pane active in ccx-dynamic-pane" id="ccx-dynamic-main">
                            <div class="form-group">
                                <label for="dynamic-main-sql"><?= html_escape(ccx_lang('ccx_template_dynamic_sql_label', 'SQL Query')); ?></label>
                                <textarea id="dynamic-main-sql" name="dynamic[main][sql_query]" rows="10" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicMain['sql_query']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_sql_hint', 'Use read-only SELECT statements. Reference filters with {{filter:your_key}} tokens.')); ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="dynamic-main-html"><?= html_escape(ccx_lang('ccx_template_dynamic_html_label', 'HTML / CSS Layout')); ?></label>
                                <textarea id="dynamic-main-html" name="dynamic[main][html_content]" rows="10" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicMain['html_content']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_html_hint', 'Provide the markup for this page. Include inline <style> blocks if needed.')); ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="dynamic-main-filters"><?= html_escape(ccx_lang('ccx_template_dynamic_filters_label', 'Filters (JSON definition)')); ?></label>
                                <textarea id="dynamic-main-filters" name="dynamic[main][filters]" rows="6" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicMain['filters_json']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_filters_hint', 'Define inputs with the same structure as SQL filters. Example: [{"key":"range_from","label":"Date From","type":"date"}]')); ?>
                                </p>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane ccx-dynamic-pane" id="ccx-dynamic-sub">
                            <div class="form-group">
                                <label for="dynamic-sub-sql"><?= html_escape(ccx_lang('ccx_template_dynamic_sql_label', 'SQL Query')); ?></label>
                                <textarea id="dynamic-sub-sql" name="dynamic[sub][sql_query]" rows="10" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicSub['sql_query']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_sql_hint', 'Use read-only SELECT statements. Reference filters with {{filter:your_key}} tokens.')); ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="dynamic-sub-html"><?= html_escape(ccx_lang('ccx_template_dynamic_html_label', 'HTML / CSS Layout')); ?></label>
                                <textarea id="dynamic-sub-html" name="dynamic[sub][html_content]" rows="10" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicSub['html_content']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_html_hint', 'Provide the markup for this page. Include inline <style> blocks if needed.')); ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="dynamic-sub-filters"><?= html_escape(ccx_lang('ccx_template_dynamic_filters_label', 'Filters (JSON definition)')); ?></label>
                                <textarea id="dynamic-sub-filters" name="dynamic[sub][filters]" rows="6" class="form-control code-field" spellcheck="false"><?= html_escape($dynamicSub['filters_json']); ?></textarea>
                                <p class="ccx-dynamic-hint">
                                    <?= html_escape(ccx_lang('ccx_template_dynamic_filters_hint', 'Define inputs with the same structure as SQL filters. Example: [{"key":"range_from","label":"Date From","type":"date"}]')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="checkbox checkbox-primary <?= ($templateType ?? 'smart') === 'sql' ? '' : 'ccx-is-hidden'; ?>" data-sql-only>
                        <input type="checkbox" id="ccx-sql-is-active" name="is_active" value="1" <?= (int) ($sqlTemplate['is_active'] ?? 1) === 1 ? 'checked' : ''; ?>>
                        <label for="ccx-sql-is-active"><?= html_escape(ccx_lang('ccx_template_sql_is_active', 'Active')); ?></label>
                    </div>
                </div>

                <div class="ccx-card ccx-template-filters <?= ($templateType ?? 'smart') === 'sql' ? '' : 'ccx-is-hidden'; ?>" data-template-filters>
                    <div class="form-group">
                        <label for="filters_json"><?= html_escape(ccx_lang('ccx_template_filters_json_label', 'Runtime Filters (JSON definition)')); ?></label>
                        <textarea id="filters_json" name="filters_json" rows="8" class="form-control code-field" spellcheck="false"><?= html_escape($filtersJson ?? ''); ?></textarea>
                        <p class="tw-text-xs tw-text-neutral-500 tw-mt-1 ccx-filters-hint <?= ($templateType ?? 'smart') === 'smart' ? '' : 'ccx-is-hidden'; ?>" data-filters-hint="smart">
                            <?= html_escape(ccx_lang('ccx_template_filters_hint_smart', 'Expose user inputs for smart reports. Reference them inside column filters with placeholders like {{filter:group_name}}.')); ?>
                        </p>
                        <p class="tw-text-xs tw-text-neutral-500 tw-mt-1 ccx-filters-hint <?= ($templateType ?? 'smart') === 'sql' ? '' : 'ccx-is-hidden'; ?>" data-filters-hint="sql">
                            <?= html_escape(ccx_lang('ccx_template_sql_filters_hint', 'Define filters as a JSON array. Each filter needs at least a key, label, type (text, number, date, datetime or select) and optional defaults. Example: [{"key":"date_from","label":"Date From","type":"date"}].')); ?>
                        </p>
                        <?php if (! empty($templateFilters)) { ?>
                            <div class="alert alert-info tw-mt-2">
                                <p class="tw-text-xs tw-uppercase tw-font-semibold tw-text-neutral-500 tw-mb-1">
                                    <?= html_escape(ccx_lang('ccx_template_sql_filters_preview_title', 'Current filters')); ?>
                                </p>
                                <ul class="tw-m-0 tw-pl-4 tw-text-sm">
                                    <?php foreach ($templateFilters as $filterPreview) { ?>
                                        <li>
                                            <strong><?= html_escape($filterPreview['label'] ?? $filterPreview['key']); ?></strong>
                                            (<?= html_escape($filterPreview['key']); ?>, <?= html_escape($filterPreview['type'] ?? 'text'); ?>)
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="ccx-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?= html_escape(ccx_lang('ccx_template_submit', 'Save Template')); ?>
                    </button>
                    <a href="<?= admin_url('ccx/templates'); ?>" class="btn btn-light"><?= _l('cancel'); ?></a>
                </div>

                <?= form_close(); ?>
            </div>

            

            <div class="ccx-card ccx-template-panel <?= ($templateType ?? 'smart') === 'sql' ? '' : 'ccx-is-hidden'; ?>" data-template-panel="sql">
                <div class="ccx-guide-card">
                    <h4><i class="fa fa-database tw-mr-2"></i><?= html_escape(ccx_lang('ccx_template_sql_intro_title', 'SQL Tips')); ?></h4>
                    <ol class="ccx-step-list">
                        <li><?= html_escape(ccx_lang('ccx_template_sql_intro_tip_query', 'Begin with a SELECT statement and reference tables using {{db_prefix}}invoice.')); ?></li>
                        <li><?= html_escape(ccx_lang('ccx_template_sql_intro_tip_filters', 'Insert runtime values with placeholders such as {{filter:date_from}}.')); ?></li>
                        <li><?= html_escape(ccx_lang('ccx_template_sql_intro_tip_safety', 'Avoid mutating statements; only read-only queries are allowed.')); ?></li>
                    </ol>
                </div>
                <p class="tw-text-sm tw-text-slate-600">
                    <?= html_escape(ccx_lang('ccx_template_sql_intro_hint', 'Use filters to let users refine the dataset before executing the query. Leave the JSON field empty if no inputs are needed.')); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<template id="ccx-column-template">
    <?php
    $this->load->view('ccx/templates/partials/column', [
        'index'        => '__INDEX__',
        'column'       => [],
        'aggregateMap' => $aggregateMap,
        'tableOptions' => $tableOptions,
        'columnsMap'   => $columnsMap,
    ]);
    ?>
</template>

<template id="ccx-condition-template">
    <?php $this->load->view('ccx/templates/partials/condition', ['columnIndex' => '__CINDEX__', 'condition' => []]); ?>
</template>

<template id="ccx-join-template">
    <?php
    $this->load->view('ccx/templates/partials/join', [
        'columnIndex' => '__CINDEX__',
        'join'        => [],
        'tableOptions'=> $tableOptions,
    ]);
    ?>
</template>

<template id="ccx-formula-condition-template">
    <?php
    $this->load->view('ccx/templates/partials/formula_condition', [
        'columnIndex' => '__CINDEX__',
        'sourceKey'   => '__SOURCEKEY__',
        'condition'   => [],
    ]);
    ?>
</template>

<?php
ob_start();
?>
<div class="ccx-formula-source tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 tw-mb-3" data-source-key="__SOURCEKEY__">
    <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
        <div class="tw-flex-1">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_formula_source_label', 'Source Label')); ?></label>
                        <input type="text" name="columns[__INDEX__][formula_sources][__SOURCEKEY__][label]" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_formula_source_key', 'Reference Key')); ?> *</label>
                        <input type="text" name="columns[__INDEX__][formula_sources][__SOURCEKEY__][key]" class="form-control ccx-source-key" value="__SOURCEKEY__" placeholder="total_revenue">
                        <small class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_formula_source_key_hint', 'Letters, numbers and underscores only (e.g. total_revenue).')); ?></small>
                    </div>
                </div>
            </div>
            <div class="row tw-mt-2">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_formula_source_type', 'Source Type')); ?></label>
                        <select name="columns[__INDEX__][formula_sources][__SOURCEKEY__][type]" class="form-control ccx-source-type">
                            <option value="metric" selected><?= html_escape(ccx_lang('ccx_template_formula_source_type_metric', 'Metric (Aggregate)')); ?></option>
                            <option value="number"><?= html_escape(ccx_lang('ccx_template_formula_source_type_number', 'Static Number')); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8 ccx-source-metric">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_field_aggregate', 'Function')); ?></label>
                                <select name="columns[__INDEX__][formula_sources][__SOURCEKEY__][aggregate]" class="form-control ccx-source-aggregate-select">
                                    <?php foreach ($aggregateMap as $key => $labelOption) { ?>
                                        <option value="<?= html_escape($key); ?>" <?= $key === 'SUM' ? 'selected' : ''; ?>>
                                            <?= html_escape($labelOption); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_field_table', 'Table')); ?></label>
                                <select name="columns[__INDEX__][formula_sources][__SOURCEKEY__][table_name]" class="form-control ccx-source-table-select">
                                    <option value=""><?= html_escape(ccx_lang('ccx_template_field_table_placeholder', 'Select a table')); ?></option>
                                    <?php foreach ($tableOptions as $tableValue => $tableLabel) { ?>
                                        <option value="<?= html_escape($tableValue); ?>"><?= html_escape($tableLabel); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= html_escape(ccx_lang('ccx_template_field_column', 'Column')); ?></label>
                                <select name="columns[__INDEX__][formula_sources][__SOURCEKEY__][column_name]" class="form-control ccx-source-column-select" data-selected="">
                                    <option value=""><?= html_escape(ccx_lang('ccx_template_field_column_placeholder', 'Select a column')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 ccx-source-number" style="display:none;">
                    <div class="form-group">
                        <label><?= html_escape(ccx_lang('ccx_template_formula_source_constant', 'Constant Value')); ?></label>
                        <input type="number" step="0.01" class="form-control" name="columns[__INDEX__][formula_sources][__SOURCEKEY__][constant]" value="0">
                    </div>
                </div>
            </div>
            <div class="ccx-formula-filters tw-mt-2">
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-2">
                    <div>
                        <strong><?= html_escape(ccx_lang('ccx_template_formula_filters', 'Filters')); ?></strong>
                        <div class="tw-text-xs tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_template_formula_filters_hint', 'Optional filters applied before calculating this source.')); ?></div>
                    </div>
                    <button class="btn btn-default btn-sm ccx-add-formula-condition" type="button">
                        <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_condition_add', 'Add Filter')); ?>
                    </button>
                </div>
                <div class="ccx-formula-conditions"></div>
            </div>
        </div>
        <button class="btn btn-danger btn-sm ccx-remove-formula-source" type="button" title="<?= html_escape(ccx_lang('ccx_template_formula_source_remove', 'Remove Source')); ?>">
            <i class="fa fa-times"></i>
        </button>
    </div>
</div>
<?php
$formulaSourceTemplate = ob_get_clean();
?>
<template id="ccx-formula-source-template">
    <?= $formulaSourceTemplate; ?>
</template>

<?php init_tail(); ?>
<script>
(function($) {
    "use strict";
    let nextColumnIndex = <?= (int) count($existingColumns); ?>;
    const columnsMap = <?= json_encode($columnsMap ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const datePlaceholder = <?= json_encode(ccx_lang('ccx_template_date_filter_placeholder', 'Select a date column')); ?>;
    const previewEndpoint = admin_url + 'ccx/template_preview';
    const PREVIEW_DELAY = 600;
    const previewTimers = {};
    const previewRequests = {};
    const previewStrings = {
        idleMessage: <?= json_encode(ccx_lang('ccx_template_preview_idle', 'Adjust the inputs to see a preview.')); ?>,
        loadingMessage: <?= json_encode(ccx_lang('ccx_template_preview_loading', 'Checking your query...')); ?>,
        successMessage: <?= json_encode(ccx_lang('ccx_template_preview_success', 'Preview uses live data from your database.')); ?>,
        emptyMessage: <?= json_encode(ccx_lang('ccx_template_preview_no_data', 'No matching rows were found with the current filters.')); ?>,
        genericError: <?= json_encode(ccx_lang('ccx_template_preview_error_generic', 'The database rejected this query. Check table names, joins and filters.')); ?>,
        needTable: <?= json_encode(ccx_lang('ccx_template_preview_select_table', 'Select a table to preview this metric.')); ?>,
        needColumn: <?= json_encode(ccx_lang('ccx_template_preview_select_column', 'Pick a column to preview this metric.')); ?>,
        needFormula: <?= json_encode(ccx_lang('ccx_template_preview_formula_missing', 'Add at least one source and a formula expression to preview.')); ?>,
        formulaPending: <?= json_encode(ccx_lang('ccx_template_preview_formula_pending', 'Preview will appear once all referenced metrics resolve.')); ?>,
        badgeIdle: <?= json_encode(ccx_lang('ccx_template_preview_badge_idle', 'Idle')); ?>,
        badgeLoading: <?= json_encode(ccx_lang('ccx_template_preview_badge_loading', 'Checking...')); ?>,
        badgeReady: <?= json_encode(ccx_lang('ccx_template_preview_badge_ready', 'Preview')); ?>,
        badgeError: <?= json_encode(ccx_lang('ccx_template_preview_badge_error', 'Error')); ?>,
        placeholderValue: '-'
    };

    function toggleTemplatePanels(selected) {
        $('[data-template-panel]').each(function() {
            const panel = $(this);
            if (panel.data('template-panel') === selected) {
                panel.removeClass('ccx-is-hidden');
            } else {
                panel.addClass('ccx-is-hidden');
            }
        });

        $('[data-filters-hint]').each(function() {
            const hint = $(this);
            if (hint.data('filtersHint') === selected) {
                hint.removeClass('ccx-is-hidden');
            } else {
                hint.addClass('ccx-is-hidden');
            }
        });

        $('[data-template-filters]').toggleClass('ccx-is-hidden', selected !== 'sql');
        $('[data-sql-only]').toggleClass('ccx-is-hidden', selected !== 'sql');

        if (selected === 'dynamic') {
            const $dynamicFirstTab = $('.ccx-dynamic-nav li:first-child a');
            if ($dynamicFirstTab.length) {
                $dynamicFirstTab.tab('show');
            }
        }
    }

    const $templateType = $('#template-type');
    if ($templateType.length) {
        toggleTemplatePanels($templateType.val());
        $templateType.on('change', function() {
            toggleTemplatePanels($(this).val());
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderPreview(columnWrapper, details) {
        const wrapper = columnWrapper.find('[data-preview-wrapper]');
        if (! wrapper.length) {
            return;
        }

        const statusBadge = wrapper.find('[data-preview-status]');
        const valueEl = wrapper.find('[data-preview-value]');
        const messageEl = wrapper.find('[data-preview-message]');
        const warningsEl = wrapper.find('[data-preview-warnings]');

        const state = details.state || 'idle';
        const warnings = Array.isArray(details.warnings) ? details.warnings : [];
        let message = details.message || '';
        let badgeText = previewStrings.badgeIdle;

        wrapper.removeClass('ccx-preview--error');
        statusBadge.removeClass('ccx-preview__status--idle ccx-preview__status--loading ccx-preview__status--ready ccx-preview__status--error');

        switch (state) {
            case 'loading':
                badgeText = previewStrings.badgeLoading;
                statusBadge.addClass('ccx-preview__status--loading');
                if (! message) {
                    message = previewStrings.loadingMessage;
                }
                break;
            case 'success':
                badgeText = previewStrings.badgeReady;
                statusBadge.addClass('ccx-preview__status--ready');
                if (! message) {
                    if (details.rawValue === null) {
                        message = previewStrings.emptyMessage;
                    } else {
                        message = previewStrings.successMessage;
                    }
                }
                break;
            case 'error':
                badgeText = previewStrings.badgeError;
                statusBadge.addClass('ccx-preview__status--error');
                wrapper.addClass('ccx-preview--error');
                if (! message) {
                    message = previewStrings.genericError;
                }
                break;
            default:
                badgeText = previewStrings.badgeIdle;
                statusBadge.addClass('ccx-preview__status--idle');
                if (! message) {
                    message = previewStrings.idleMessage;
                }
                break;
        }

        statusBadge.text(badgeText);
        const value = details.value !== undefined && details.value !== null ? details.value : previewStrings.placeholderValue;
        valueEl.text(value);
        messageEl.text(message);

        warningsEl.empty();
        if (warnings.length) {
            warnings.forEach(function(text) {
                const item = $('<div class="ccx-preview__warning-item"></div>').text('! ' + text);
                warningsEl.append(item);
            });
            warningsEl.show();
        } else {
            warningsEl.hide();
        }
    }

    function collectColumnPreviewPayload(columnWrapper) {
        if (! columnWrapper || ! columnWrapper.length) {
            return null;
        }

        const index = columnWrapper.data('columnIndex');
        if (index === undefined || index === null) {
            return null;
        }

        const payload = {
            mode: columnWrapper.find('.ccx-mode-select').val() || 'simple',
            label: columnWrapper.find('input[name="columns[' + index + '][label]"]').val() || '',
            table_name: columnWrapper.find('.ccx-table-select').val() || '',
            column_name: columnWrapper.find('.ccx-column-select').val() || '',
            aggregate_function: (columnWrapper.find('.ccx-aggregate-select').val() || '').toUpperCase(),
            decimal_places: columnWrapper.find('input[name="columns[' + index + '][decimal_places]"]').val(),
            currency_display: columnWrapper.find('select[name="columns[' + index + '][currency_display]"]').val() || 'inherit',
            date_filter_field: columnWrapper.find('.ccx-date-field-select').val() || '',
            role: columnWrapper.find('.ccx-role-select').val() || 'metric',
        };

        const conditions = {field: [], operator: [], value: [], type: []};
        columnWrapper.find('.ccx-conditions .ccx-condition-row').each(function() {
            const row = $(this);
            conditions.field.push(row.find('input[name$="[conditions][field][]"]').val() || '');
            conditions.operator.push(row.find('select[name$="[conditions][operator][]"]').val() || '=');
            conditions.value.push(row.find('input[name$="[conditions][value][]"]').val() || '');
            conditions.type.push(row.find('select[name$="[conditions][type][]"]').val() || 'string');
        });
        payload.conditions = conditions;

        const joins = {table_name: [], alias: [], type: [], on: []};
        columnWrapper.find('.ccx-joins-container .ccx-join-row').each(function() {
            const row = $(this);
            joins.table_name.push(row.find('select[name$="[joins][table_name][]"]').val() || '');
            joins.alias.push(row.find('input[name$="[joins][alias][]"]').val() || '');
            joins.type.push(row.find('select[name$="[joins][type][]"]').val() || 'INNER');
            joins.on.push(row.find('input[name$="[joins][on][]"]').val() || '');
        });
        payload.joins = joins;

        if (payload.mode === 'formula') {
            payload.formula_expression = columnWrapper.find('textarea[name="columns[' + index + '][formula_expression]"]').val() || '';
            const sources = [];
            columnWrapper.find('.ccx-formula-source').each(function() {
                const sourceWrapper = $(this);
                const source = {
                    label: sourceWrapper.find('input[name$="[label]"]').first().val() || '',
                    key: sourceWrapper.find('input[name$="[key]"]').val() || '',
                    type: sourceWrapper.find('.ccx-source-type').val() || 'metric',
                    aggregate: sourceWrapper.find('.ccx-source-aggregate-select').val() || 'SUM',
                    table_name: sourceWrapper.find('.ccx-source-table-select').val() || '',
                    column_name: sourceWrapper.find('.ccx-source-column-select').val() || '',
                    constant: sourceWrapper.find('input[name$="[constant]"]').val() || '',
                };

                const sourceConditions = {field: [], operator: [], value: [], type: []};
                sourceWrapper.find('.ccx-formula-conditions .ccx-condition-row').each(function() {
                    const conditionRow = $(this);
                    sourceConditions.field.push(conditionRow.find('input[name$="[conditions][field][]"]').val() || '');
                    sourceConditions.operator.push(conditionRow.find('select[name$="[conditions][operator][]"]').val() || '=');
                    sourceConditions.value.push(conditionRow.find('input[name$="[conditions][value][]"]').val() || '');
                    sourceConditions.type.push(conditionRow.find('select[name$="[conditions][type][]"]').val() || 'string');
                });

                source.conditions = sourceConditions;
                sources.push(source);
            });
            payload.formula_sources = sources;
        }

        return payload;
    }

    function scheduleColumnPreview(columnWrapper, immediate = false) {
        if (! columnWrapper || ! columnWrapper.length) {
            return;
        }

        const key = String(columnWrapper.data('columnIndex'));
        if (! key || key === 'undefined') {
            return;
        }

        if (previewTimers[key]) {
            clearTimeout(previewTimers[key]);
            previewTimers[key] = null;
        }

        previewTimers[key] = setTimeout(function() {
            runColumnPreview(columnWrapper);
            previewTimers[key] = null;
        }, immediate ? 0 : PREVIEW_DELAY);
    }

    function runColumnPreview(columnWrapper) {
        if (! columnWrapper || ! columnWrapper.length) {
            return;
        }

        const key = String(columnWrapper.data('columnIndex'));
        if (! key || key === 'undefined') {
            return;
        }

        const payload = collectColumnPreviewPayload(columnWrapper);
        if (! payload) {
            return;
        }

        const aggregate = (payload.aggregate_function || '').toUpperCase();

        const abortActive = function() {
            if (previewRequests[key]) {
                previewRequests[key].abort();
                previewRequests[key] = null;
            }
        };

        if (payload.mode === 'simple') {
            if (! payload.table_name) {
                abortActive();
                renderPreview(columnWrapper, {state: 'incomplete', message: previewStrings.needTable});
                return;
            }
            if (aggregate !== 'COUNT' && ! payload.column_name) {
                abortActive();
                renderPreview(columnWrapper, {state: 'incomplete', message: previewStrings.needColumn});
                return;
            }
        } else {
            const hasSources = Array.isArray(payload.formula_sources) && payload.formula_sources.length > 0;
            if (! payload.formula_expression || ! hasSources) {
                abortActive();
                renderPreview(columnWrapper, {state: 'incomplete', message: previewStrings.needFormula});
                return;
            }
        }

        abortActive();
        renderPreview(columnWrapper, {state: 'loading'});

        const requestData = { column: JSON.stringify(payload) };
        if (typeof csrfData !== 'undefined' && csrfData.token_name && csrfData.hash) {
            requestData[csrfData.token_name] = csrfData.hash;
        }

        previewRequests[key] = $.ajax({
            url: previewEndpoint,
            method: 'POST',
            data: requestData,
            dataType: 'json',
        }).done(function(response) {
            if (response && response.success) {
                renderPreview(columnWrapper, {
                    state: 'success',
                    value: response.value,
                    message: response.message,
                    warnings: response.warnings || [],
                    rawValue: response.raw_value,
                });
                return;
            }

            const responseState = response && response.state ? response.state : 'error';
            const mappedState = responseState === 'error' ? 'error' : 'incomplete';
            let message = response && response.message ? response.message : '';
            if (! message) {
                if (responseState === 'formula') {
                    message = previewStrings.formulaPending;
                } else {
                    message = mappedState === 'incomplete' ? previewStrings.idleMessage : previewStrings.genericError;
                }
            }

            renderPreview(columnWrapper, {
                state: mappedState,
                message: message,
                warnings: response && response.warnings ? response.warnings : [],
            });
        }).fail(function(xhr) {
            let message = previewStrings.genericError;
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            renderPreview(columnWrapper, {state: 'error', message: message});
        }).always(function() {
            previewRequests[key] = null;
        });
    }

    function refreshColumnOptions(columnWrapper, preserveSelection = true) {
        const tableSelect = columnWrapper.find('.ccx-table-select');
        if (! tableSelect.length) {
            return;
        }
        const table = tableSelect.val();
        const columnSelect = columnWrapper.find('.ccx-column-select');
        const previous = preserveSelection ? (columnSelect.val() || columnSelect.data('selected') || '') : columnSelect.val();
        const options = columnsMap[table] || {};

        let html = '<option value="">' + <?= json_encode(ccx_lang('ccx_template_field_column_placeholder', 'Select a column')); ?> + '</option>';
        const optionKeys = Object.keys(options).sort();
        optionKeys.forEach(function(key) {
            const selected = (previous && previous === key) ? ' selected' : '';
            html += '<option value="' + escapeHtml(key) + '"' + selected + '>' + escapeHtml(options[key]) + '</option>';
        });

        if (previous && !Object.prototype.hasOwnProperty.call(options, previous)) {
            html += '<option value="' + escapeHtml(previous) + '" selected>' + escapeHtml(previous) + '</option>';
        }

        columnSelect.html(html);
        columnSelect.val(previous || '');
        columnSelect.data('selected', '');

        const dateSelect = columnWrapper.find('.ccx-date-field-select');
        if (dateSelect.length) {
            const previousDate = preserveSelection ? (dateSelect.val() || dateSelect.data('selected') || '') : dateSelect.val();
            let dateHtml = '<option value="">' + datePlaceholder + '</option>';
            optionKeys.forEach(function(key) {
                const selected = (previousDate && previousDate === key) ? ' selected' : '';
                dateHtml += '<option value="' + escapeHtml(key) + '"' + selected + '>' + escapeHtml(options[key]) + '</option>';
            });
            if (previousDate && !Object.prototype.hasOwnProperty.call(options, previousDate)) {
                dateHtml += '<option value="' + escapeHtml(previousDate) + '" selected>' + escapeHtml(previousDate) + '</option>';
            }
            dateSelect.html(dateHtml);
            dateSelect.val(previousDate || '');
            dateSelect.data('selected', '');
        }
        toggleColumnRequirement(columnWrapper);
    }

    function refreshFormulaSourceColumns(sourceWrapper, preserveSelection = true) {
        const table = sourceWrapper.find('.ccx-source-table-select').val();
        const columnSelect = sourceWrapper.find('.ccx-source-column-select');
        const previous = preserveSelection ? (columnSelect.val() || columnSelect.data('selected') || '') : columnSelect.val();
        const options = columnsMap[table] || {};

        let html = '<option value="">' + <?= json_encode(ccx_lang('ccx_template_field_column_placeholder', 'Select a column')); ?> + '</option>';
        const keys = Object.keys(options).sort();
        keys.forEach(function(key) {
            const selected = (previous && previous === key) ? ' selected' : '';
            html += '<option value="' + escapeHtml(key) + '"' + selected + '>' + escapeHtml(options[key]) + '</option>';
        });

        if (previous && !Object.prototype.hasOwnProperty.call(options, previous)) {
            html += '<option value="' + escapeHtml(previous) + '" selected>' + escapeHtml(previous) + '</option>';
        }

        columnSelect.html(html);
        columnSelect.val(previous || '');
        columnSelect.data('selected', '');
    }

    function toggleColumnRequirement(columnWrapper) {
        const mode = columnWrapper.find('.ccx-mode-select').val();
        const aggregate = columnWrapper.find('.ccx-aggregate-select').val();
        const columnSelect = columnWrapper.find('.ccx-column-select');
        const decimalGroup = columnWrapper.find('.ccx-decimal-group');
        const role = columnWrapper.find('.ccx-role-select').val();

        if (role === 'group') {
            columnSelect.prop('required', true);
            if (decimalGroup.length) {
                decimalGroup.hide();
            }
            return;
        }

        if (mode === 'formula') {
            columnSelect.prop('required', false);
            decimalGroup.show();
            return;
        }

        if (aggregate === 'COUNT') {
            columnSelect.prop('required', false);
            decimalGroup.show();
        } else if (aggregate === 'VALUE') {
            columnSelect.prop('required', true);
            decimalGroup.hide();
        } else {
            columnSelect.prop('required', true);
            decimalGroup.show();
        }
    }

    function updateFormulaSourceType(sourceWrapper) {
        const type = sourceWrapper.find('.ccx-source-type').val();
        if (type === 'number') {
            sourceWrapper.find('.ccx-source-metric').hide();
            sourceWrapper.find('.ccx-source-number').show();
        } else {
            sourceWrapper.find('.ccx-source-metric').show();
            sourceWrapper.find('.ccx-source-number').hide();
        }
    }

    function initialiseFormulaSource(sourceWrapper) {
        updateFormulaSourceType(sourceWrapper);
        refreshFormulaSourceColumns(sourceWrapper, true);
    }

    function updateFormulaKeyChips(columnWrapper) {
        const container = columnWrapper.find('.ccx-formula-keys');
        if (! container.length) {
            return;
        }
        const chips = [];
        columnWrapper.find('.ccx-formula-source').each(function() {
            const key = $.trim($(this).find('.ccx-source-key').val());
            if (key !== '') {
                chips.push('<span class="badge badge-info">{{' + escapeHtml(key) + '}}</span>');
            }
        });
        container.html(chips.join(' '));
    }

    function createFormulaConditionRow(columnIndex, sourceKey, condition) {
        let templateHtml = $('#ccx-formula-condition-template').html();
        templateHtml = templateHtml.replace(/__CINDEX__/g, columnIndex).replace(/__SOURCEKEY__/g, sourceKey);
        const row = $(templateHtml);

        if (condition && typeof condition === 'object') {
            row.find('input[name*="[field][]"]').val(condition.field ?? '');
            row.find('select[name*="[operator][]"]').val((condition.operator ?? '=').toUpperCase());
            row.find('input[name*="[value][]"]').val(condition.value ?? '');
            row.find('select[name*="[type][]"]').val((condition.type ?? 'string').toLowerCase());
        }

        return row;
    }

    function addFormulaSource(columnWrapper, preset) {
        const index = columnWrapper.data('columnIndex');
        const key = (preset && preset.key) ? preset.key : ('src' + Date.now());
        let template = $('#ccx-formula-source-template').html();
        template = template.replace(/__INDEX__/g, index).replace(/__SOURCEKEY__/g, key);
        const node = $(template);
        columnWrapper.find('.ccx-formula-sources').append(node);

        if (preset) {
            node.find('.ccx-source-key').val(preset.key || key);
            node.find('input[name$="[label]"]').val(preset.label || '');
            node.find('.ccx-source-type').val(preset.type || 'metric');
            node.find('.ccx-source-aggregate-select').val(preset.aggregate || 'SUM');
            node.find('.ccx-source-table-select').val(preset.table_name || '');
            node.find('.ccx-source-column-select').attr('data-selected', preset.column_name || '');
            node.find('input[name$="[constant]"]').val(preset.constant ?? '');

            if (Array.isArray(preset.conditions) && preset.conditions.length) {
                const container = node.find('.ccx-formula-conditions');
                container.empty();
                preset.conditions.forEach(function(condition) {
                    const conditionRow = createFormulaConditionRow(index, key, condition);
                    container.append(conditionRow);
                });
            }
        }

        initialiseFormulaSource(node);
        updateFormulaKeyChips(columnWrapper);
        scheduleColumnPreview(columnWrapper);
    }

    function toggleMode(columnWrapper) {
        const mode = columnWrapper.find('.ccx-mode-select').val();
        columnWrapper.find('[data-mode-section]').hide();
        columnWrapper.find('[data-mode-section="' + mode + '"]').show();

        columnWrapper.find('[data-mode-required]').each(function() {
            const requiredMode = $(this).data('mode-required');
            $(this).prop('required', requiredMode === mode);
        });

        if (mode === 'formula' && columnWrapper.find('.ccx-formula-source').length === 0) {
            addFormulaSource(columnWrapper);
        }

        if (mode === 'formula') {
            columnWrapper.find('.ccx-role-select').val('metric');
        }

        updateRoleState(columnWrapper);
        toggleColumnRequirement(columnWrapper);
    }

    function updateRoleState(columnWrapper) {
        const role = columnWrapper.find('.ccx-role-select').val() || 'metric';
        const aggregateSelect = columnWrapper.find('.ccx-aggregate-select');

        if (role === 'group') {
            aggregateSelect.val('VALUE').prop('disabled', true);
        } else {
            aggregateSelect.prop('disabled', false);
        }

        toggleColumnRequirement(columnWrapper);
    }

    function initialiseColumn(columnWrapper) {
        refreshColumnOptions(columnWrapper, true);
        toggleMode(columnWrapper);
        updateRoleState(columnWrapper);
        columnWrapper.find('.ccx-formula-source').each(function() {
            initialiseFormulaSource($(this));
        });
        updateFormulaKeyChips(columnWrapper);
    }

    $('#ccx-columns-container .ccx-column').each(function() {
        const wrapper = $(this);
        initialiseColumn(wrapper);
        scheduleColumnPreview(wrapper, true);
    });

    appValidateForm($('#ccx-template-form'), {
        'template[name]': 'required'
    });

    $('#ccx-add-column').on('click', function(e) {
        e.preventDefault();
        $('#ccx-columns-container .ccx-columns-empty').remove();
        const templateHtml = $('#ccx-column-template').html().replace(/__INDEX__/g, nextColumnIndex);
        const newNode = $(templateHtml);
        $('#ccx-columns-container').append(newNode);
        initialiseColumn(newNode);
        scheduleColumnPreview(newNode, true);
        nextColumnIndex++;
    });

    $(document).on('click', '.ccx-remove-column', function(e) {
        e.preventDefault();
        if ($('.ccx-column').length === 1) {
            alert('<?= html_escape(ccx_lang('ccx_template_column_required', 'At least one column is required.')); ?>');
            return;
        }
        const columnWrapper = $(this).closest('.ccx-column');
        const key = columnWrapper.length ? String(columnWrapper.data('columnIndex')) : null;
        if (key && previewTimers[key]) {
            clearTimeout(previewTimers[key]);
            delete previewTimers[key];
        }
        if (key && previewRequests[key]) {
            previewRequests[key].abort();
            previewRequests[key] = null;
        }
        columnWrapper.remove();
        if ($('.ccx-column').length === 0) {
            $('#ccx-columns-container').append('<div class="ccx-columns-empty"><i class="fa fa-columns tw-mr-2"></i><?= html_escape(ccx_lang('ccx_templates_empty_hint', 'No columns yet. Start by adding a metric or formula.')); ?></div>');
        }
    });

    $(document).on('click', '.ccx-add-condition', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        const columnIndex = columnWrapper.data('columnIndex');
        const templateHtml = $('#ccx-condition-template').html().replace(/__CINDEX__/g, columnIndex);
        columnWrapper.find('.ccx-conditions').append(templateHtml);
    });

    $(document).on('click', '.ccx-add-join', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        const columnIndex = columnWrapper.data('columnIndex');
        let templateHtml = $('#ccx-join-template').html();
        templateHtml = templateHtml.replace(/__CINDEX__/g, columnIndex);
        columnWrapper.find('.ccx-joins-container').append(templateHtml);
    });

    $(document).on('input change', '.ccx-column :input', function() {
        const target = $(this);
        if (target.attr('type') === 'button' || target.is('button')) {
            return;
        }
        const columnWrapper = target.closest('.ccx-column');
        if (! columnWrapper.length) {
            return;
        }
        scheduleColumnPreview(columnWrapper);
    });

    $(document).on('click', '.ccx-add-formula-condition', function(e) {
        e.preventDefault();
        const sourceWrapper = $(this).closest('.ccx-formula-source');
        const columnIndex = sourceWrapper.closest('.ccx-column').data('columnIndex');
        const sourceKey = sourceWrapper.data('sourceKey');
        if (! sourceKey) {
            return;
        }
        const row = createFormulaConditionRow(columnIndex, sourceKey, {});
        sourceWrapper.find('.ccx-formula-conditions').append(row);
    });

    $(document).on('click', '.ccx-remove-condition', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        $(this).closest('.ccx-condition-row').remove();
        if (columnWrapper.length) {
            scheduleColumnPreview(columnWrapper);
        }
    });

    $(document).on('click', '.ccx-remove-join', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        $(this).closest('.ccx-join-row').remove();
        if (columnWrapper.length) {
            scheduleColumnPreview(columnWrapper);
        }
    });

    $(document).on('change', '.ccx-table-select', function() {
        const wrapper = $(this).closest('.ccx-column');
        refreshColumnOptions(wrapper, false);
    });

    $(document).on('change', '.ccx-aggregate-select', function() {
        const wrapper = $(this).closest('.ccx-column');
        toggleColumnRequirement(wrapper);
    });

    $(document).on('change', '.ccx-role-select', function() {
        const wrapper = $(this).closest('.ccx-column');
        updateRoleState(wrapper);
        scheduleColumnPreview(wrapper);
    });

    $(document).on('change', '.ccx-mode-select', function() {
        toggleMode($(this).closest('.ccx-column'));
    });

    $(document).on('click', '.ccx-add-formula-source', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        addFormulaSource(columnWrapper);
    });

    $(document).on('click', '.ccx-remove-formula-source', function(e) {
        e.preventDefault();
        const columnWrapper = $(this).closest('.ccx-column');
        $(this).closest('.ccx-formula-source').remove();
        if (columnWrapper.find('.ccx-mode-select').val() === 'formula' && columnWrapper.find('.ccx-formula-source').length === 0) {
            addFormulaSource(columnWrapper);
        }
        updateFormulaKeyChips(columnWrapper);
        scheduleColumnPreview(columnWrapper);
    });

    $(document).on('change', '.ccx-source-table-select', function() {
        refreshFormulaSourceColumns($(this).closest('.ccx-formula-source'), false);
    });

    $(document).on('change', '.ccx-source-type', function() {
        updateFormulaSourceType($(this).closest('.ccx-formula-source'));
    });

    $(document).on('blur', '.ccx-source-key', function() {
        let value = $.trim($(this).val());
        value = value.replace(/[^A-Za-z0-9_]/g, '_');
        if (value === '' || /^\d/.test(value)) {
            value = 'src';
        }
        $(this).val(value.toLowerCase());
        const columnWrapper = $(this).closest('.ccx-column');
        updateFormulaKeyChips(columnWrapper);
        scheduleColumnPreview(columnWrapper);
    });

})(jQuery);
</script>
</body>
</html>
