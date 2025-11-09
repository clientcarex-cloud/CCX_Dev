<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-hero {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 50%, #2563EB 100%);
        border-radius: 22px;
        padding: 36px 42px;
        color: #F8FAFC;
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
    }
    .ccx-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 10px;
    }
    .ccx-hero p {
        margin: 0;
        font-size: 15px;
        max-width: 560px;
        opacity: 0.85;
        line-height: 1.6;
    }
    .ccx-hero .btn-primary {
        background: #F8FAFC;
        color: #0F172A;
        border: none;
        padding: 12px 20px;
        border-radius: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.25);
    }
    .ccx-surface {
        background: #FFFFFF;
        border-radius: 22px;
        padding: 24px 26px 30px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
    }
    .ccx-surface-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 18px;
    }
    .ccx-search {
        position: relative;
        max-width: 360px;
        flex: 1 1 220px;
    }
    .ccx-search input {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        padding: 12px 44px;
        font-size: 14px;
        background: #F8FAFC;
        transition: border 0.2s ease, box-shadow 0.2s ease;
    }
    .ccx-search input:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.55);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
    }
    .ccx-search i {
        position: absolute;
        top: 50%;
        left: 18px;
        transform: translateY(-50%);
        color: #64748B;
        font-size: 14px;
    }
    .ccx-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
    }
    .ccx-template-card {
        background: radial-gradient(circle at top left, rgba(148, 163, 184, 0.18), transparent 55%), #FFFFFF;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: 220px;
        height: 100%;
        box-sizing: border-box;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .ccx-template-card > div:first-of-type {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-grow: 1;
    }
    .ccx-template-card:hover {
        transform: translateY(-3px);
        border-color: rgba(37, 99, 235, 0.45);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
    }
    .ccx-template-card h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
        overflow-wrap: anywhere;
    }
    .ccx-template-card p {
        margin: 0;
        font-size: 13px;
        color: #475569;
        flex: 1;
        line-height: 1.55;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .ccx-template-card > div:first-of-type p {
        flex-grow: 1;
    }
    .ccx-template-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        row-gap: 10px;
        font-size: 12px;
        color: #0F172A;
        font-weight: 600;
    }
    .ccx-template-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1D4ED8;
    }
    .ccx-template-actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: auto;
    }
    .ccx-template-actions .btn {
        flex: 1 1 120px;
        border-radius: 12px;
    }
    .ccx-empty {
        border: 1px dashed rgba(37, 99, 235, 0.45);
        border-radius: 18px;
        padding: 34px;
        text-align: center;
        color: #334155;
        background: #F8FAFC;
    }
    .ccx-empty h4 {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 600;
    }
    .ccx-empty p {
        margin: 0 0 18px;
        font-size: 14px;
        color: #64748B;
    }
    @media (max-width: 768px) {
        .ccx-hero {
            flex-direction: column;
            padding: 30px 28px;
        }
        .ccx-hero h1 {
            font-size: 24px;
        }
        .ccx-hero .btn-primary {
            align-self: stretch;
            justify-content: center;
        }
    }
    @media (max-width: 600px) {
        .ccx-card-grid {
            grid-template-columns: 1fr;
        }
        .ccx-template-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .ccx-template-actions .btn {
            flex: 1 1 auto;
            width: 100%;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-hero">
            <div>
                <h1><?= html_escape($title); ?></h1>
                <p><?= html_escape(ccx_lang('ccx_templates_intro', 'Design reusable report blueprints and manage their column logic without touching code.')); ?></p>
            </div>
            <?php if (staff_can('create', 'ccx_templates') || is_admin()) { ?>
                <a href="<?= admin_url('ccx/template'); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i>
                    <?= html_escape(ccx_lang('ccx_template_add_new', 'New Template')); ?>
                </a>
            <?php } ?>
        </div>

        <div class="ccx-surface">
            <div class="ccx-surface-header">
                <div class="ccx-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="ccx-template-search" placeholder="<?= html_escape(ccx_lang('ccx_reports_search_placeholder', 'Search reports by name or description…')); ?>">
                </div>
            </div>

            <?php if (empty($templates)) { ?>
                <div class="ccx-empty">
                    <h4><?= html_escape(ccx_lang('ccx_templates_empty', 'No templates have been created yet.')); ?></h4>
                    <p><?= html_escape(ccx_lang('ccx_templates_empty_cta', 'Start by creating your first report template.')); ?></p>
                    <?php if (staff_can('create', 'ccx_templates') || is_admin()) { ?>
                        <a href="<?= admin_url('ccx/template'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_template_add_new', 'New Template')); ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="ccx-card-grid" id="ccx-template-grid">
                    <?php foreach ($templates as $template) {
                        $canViewTemplate   = staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates') || is_admin();
                        $canDeleteTemplate = staff_can('delete', 'ccx_templates') || is_admin();
                        $updated = ! empty($template['updated_at']) ? _d($template['updated_at']) : _d($template['created_at']);
                        $searchIndex = strtolower(trim(($template['name'] ?? '') . ' ' . ($template['description'] ?? '')));
                        $templateType = strtolower((string) ($template['type'] ?? 'smart'));
                        if (! in_array($templateType, ['smart', 'sql', 'dynamic'], true)) {
                            $templateType = 'smart';
                        }
                        if ($templateType === 'sql') {
                            $typeLabel = ccx_lang('ccx_template_type_sql', 'Custom SQL Query');
                        } elseif ($templateType === 'dynamic') {
                            $typeLabel = ccx_lang('ccx_template_type_dynamic', 'Dynamic High Level');
                        } else {
                            $typeLabel = ccx_lang('ccx_template_type_smart', 'Smart Report');
                        }
                        $isActive = (int) ($template['is_active'] ?? 1) === 1;
                    ?>
                        <div class="ccx-template-card" data-search="<?= html_escape($searchIndex); ?>">
                            <div>
                                <h3><?= html_escape($template['name']); ?></h3>
                                <p><?= html_escape($template['description'] ?: ccx_lang('ccx_reports_card_placeholder', 'Launch detailed insights in seconds.')); ?></p>
                            </div>
                            <div class="ccx-template-meta">
                                <span><i class="fa fa-tag"></i><?= html_escape($typeLabel); ?></span>
                                <?php if ($templateType === 'sql') { ?>
                                    <span>
                                        <i class="fa <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                        <?= html_escape($isActive ? ccx_lang('ccx_template_sql_is_active', 'Active') : ccx_lang('ccx_template_sql_inactive', 'Inactive')); ?>
                                    </span>
                                <?php } elseif ($templateType === 'dynamic') { ?>
                                    <span><i class="fa fa-window-maximize"></i><?= html_escape(ccx_lang('ccx_templates_card_dynamic', 'Main & Sub Pages')); ?></span>
                                <?php } else { ?>
                                    <span><i class="fa fa-columns"></i><?= (int) $template['column_count']; ?> <?= html_escape(ccx_lang('ccx_templates_card_columns', 'Columns')); ?></span>
                                <?php } ?>
                                <span><i class="fa fa-clock-o"></i><?= html_escape(ccx_lang('ccx_templates_card_updated', 'Updated')); ?> <?= html_escape($updated); ?></span>
                            </div>
                            <div class="ccx-template-actions">
                                <?php if ($canViewTemplate) { ?>
                                    <a href="<?= admin_url('ccx/template/' . (int) $template['id']); ?>" class="btn btn-outline-primary">
                                        <i class="fa fa-pencil"></i> <?= html_escape(ccx_lang('ccx_templates_card_edit', 'Configure')); ?>
                                    </a>
                                <?php } ?>
                                <?php if ($canDeleteTemplate) { ?>
                                    <a href="<?= admin_url('ccx/delete_template/' . (int) $template['id']); ?>" class="btn btn-danger" onclick="return confirm('<?= html_escape(ccx_lang('delete_confirm', 'Are you sure you want to delete this item?')); ?>');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function($) {
    "use strict";
    $('#ccx-template-search').on('keyup', function() {
        const query = $.trim($(this).val().toLowerCase());
        $('#ccx-template-grid .ccx-template-card').each(function() {
            const haystack = $(this).data('search') || '';
            const matches = !query || (haystack.indexOf(query) !== -1);
            $(this).toggle(matches);
        });
    });
})(jQuery);
</script>
</body>
</html>
