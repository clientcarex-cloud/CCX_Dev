<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-hero {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 55%, #14B8A6 100%);
        border-radius: 22px;
        padding: 36px 42px;
        color: #ECFEFF;
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
    }
    .ccx-hero h1 {
        margin: 0 0 10px;
        font-size: 28px;
        font-weight: 700;
    }
    .ccx-hero p {
        margin: 0;
        font-size: 15px;
        max-width: 520px;
        opacity: 0.85;
        line-height: 1.6;
    }
    .ccx-hero .btn-primary {
        background: #ECFEFF;
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
    .ccx-search {
        position: relative;
        max-width: 340px;
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
        border-color: rgba(20, 184, 166, 0.55);
        box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
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
        gap: 18px;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    }
    .ccx-section-card {
        background: linear-gradient(160deg, rgba(20, 184, 166, 0.09), rgba(14, 165, 233, 0.04));
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: 200px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .ccx-section-card:hover {
        transform: translateY(-3px);
        border-color: rgba(20, 184, 166, 0.5);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
    }
    .ccx-section-card h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
    }
    .ccx-section-card p {
        margin: 0;
        font-size: 13px;
        color: #475569;
        flex: 1;
        line-height: 1.55;
    }
    .ccx-section-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: #0F172A;
        font-weight: 600;
    }
    .ccx-section-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border-radius: 999px;
        background: rgba(20, 184, 166, 0.12);
        color: #0F766E;
    }
    .ccx-section-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .ccx-section-actions .btn {
        flex: 1;
        border-radius: 12px;
    }
    .ccx-empty {
        border: 1px dashed rgba(20, 184, 166, 0.45);
        border-radius: 18px;
        padding: 34px;
        text-align: center;
        color: #334155;
        background: #F1F5F9;
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
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-hero">
            <div>
                <h1><?= html_escape($title); ?></h1>
                <p><?= html_escape(ccx_lang('ccx_sections_intro', 'Organise report templates into branded sections and curate what your team sees.')); ?></p>
            </div>
            <?php if (staff_can('create', 'ccx_sections') || is_admin()) { ?>
                <a href="<?= admin_url('ccx/section'); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_section_add_new', 'New Section')); ?>
                </a>
            <?php } ?>
        </div>

        <div class="ccx-surface">
            <div class="ccx-surface-header">
                <div class="ccx-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="ccx-section-search" placeholder="<?= html_escape(ccx_lang('ccx_reports_search_placeholder', 'Search reports by name or description…')); ?>">
                </div>
            </div>

            <?php if (empty($sections)) { ?>
                <div class="ccx-empty">
                    <h4><?= html_escape(ccx_lang('ccx_sections_empty', 'No sections have been created yet.')); ?></h4>
                    <p><?= html_escape(ccx_lang('ccx_sections_empty_cta', 'Create a section and assign templates to showcase in Reports.')); ?></p>
                    <?php if (staff_can('create', 'ccx_sections') || is_admin()) { ?>
                        <a href="<?= admin_url('ccx/section'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= html_escape(ccx_lang('ccx_section_add_new', 'New Section')); ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="ccx-card-grid" id="ccx-section-grid">
                    <?php foreach ($sections as $section) {
                        $templatesCount = (int) ($section['template_count'] ?? count($section['templates'] ?? []));
                        $searchIndex = strtolower(trim(($section['name'] ?? '') . ' ' . ($section['description'] ?? '')));
                    ?>
                        <div class="ccx-section-card" data-search="<?= html_escape($searchIndex); ?>">
                            <div>
                                <h3><?= html_escape($section['name']); ?></h3>
                                <p><?= html_escape($section['description'] ?: ccx_lang('ccx_reports_card_placeholder', 'Launch detailed insights in seconds.')); ?></p>
                            </div>
                            <div class="ccx-section-meta">
                                <span><i class="fa fa-layer-group"></i><?= $templatesCount; ?> <?= html_escape(ccx_lang('ccx_section_template_count', 'Templates')); ?></span>
                                <span><i class="fa fa-sort-amount-up"></i><?= (int) $section['display_order']; ?></span>
                            </div>
                            <div class="ccx-section-actions">
                                <?php if (staff_can('view', 'ccx_sections') || staff_can('view_own', 'ccx_sections') || is_admin()) { ?>
                                    <a href="<?= admin_url('ccx/section/' . (int) $section['id']); ?>" class="btn btn-outline-primary">
                                        <i class="fa fa-pencil"></i> <?= html_escape(ccx_lang('ccx_templates_card_edit', 'Configure')); ?>
                                    </a>
                                <?php } ?>
                                <?php if (staff_can('delete', 'ccx_sections') || is_admin()) { ?>
                                    <a href="<?= admin_url('ccx/delete_section/' . (int) $section['id']); ?>" class="btn btn-danger" onclick="return confirm('<?= html_escape(ccx_lang('delete_confirm', 'Are you sure you want to delete this item?')); ?>');">
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
    $('#ccx-section-search').on('keyup', function() {
        const query = $.trim($(this).val().toLowerCase());
        $('#ccx-section-grid .ccx-section-card').each(function() {
            const haystack = $(this).data('search') || '';
            const matches = !query || (haystack.indexOf(query) !== -1);
            $(this).toggle(matches);
        });
    });
})(jQuery);
</script>
</body>
</html>
