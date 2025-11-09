<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 48%, #0ea5e9 100%);
        border-radius: 18px;
        padding: 32px 40px;
        color: #f8fafc;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .ccx-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient( circle at top right, rgba(255,255,255,0.24), transparent 55% );
        pointer-events: none;
    }
    .ccx-hero h1 {
        font-weight: 700;
        font-size: 26px;
        margin: 0 0 8px;
    }
    .ccx-hero p {
        margin: 0;
        opacity: 0.86;
        font-size: 15px;
        max-width: 520px;
    }
    .ccx-search {
        display: flex;
        align-items: center;
        position: relative;
        margin-top: 26px;
        max-width: 420px;
    }
    .ccx-search input {
        border-radius: 999px;
        border: none;
        padding: 14px 50px 14px 52px;
        font-size: 15px;
        width: 100%;
        box-shadow: 0 16px 28px rgba(15, 23, 42, 0.22);
    }
    .ccx-search i {
        position: absolute;
        left: 20px;
        color: #64748b;
        font-size: 16px;
    }
    .ccx-section-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        padding: 28px 30px;
        margin-bottom: 26px;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }
    .ccx-section-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.12);
    }
    .ccx-section-heading {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .ccx-section-heading h2 {
        font-size: 20px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }
    .ccx-section-heading span {
        font-size: 14px;
        color: #64748b;
    }
    .ccx-template-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
    .ccx-template-card {
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
        border-radius: 14px;
        padding: 18px 20px;
        border: 1px solid transparent;
        text-decoration: none !important;
        color: inherit !important;
        transition: border-color 160ms ease, background 160ms ease, transform 160ms ease, box-shadow 160ms ease;
        min-height: 190px;
        height: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    .ccx-template-card:hover {
        border-color: rgba(14, 165, 233, 0.55);
        background: #ecfeff;
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(14, 165, 233, 0.18);
    }
    .ccx-template-card h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #0f172a;
        overflow-wrap: anywhere;
    }
    .ccx-template-card p {
        margin: 0;
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
        flex-grow: 1;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .ccx-template-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: auto;
        font-size: 12px;
        color: #0369a1;
        font-weight: 600;
    }
    .ccx-template-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ccx-empty-state {
        background: #f8fafc;
        border: 1px dashed rgba(14, 165, 233, 0.45);
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        color: #334155;
        margin-top: 20px;
    }
    @media (max-width: 600px) {
        .ccx-template-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-hero">
            <h1><?= html_escape($title); ?></h1>
            <p><?= html_escape(ccx_lang('ccx_reports_intro', 'Browse, search and launch interactive finance and operations reports tailored to your workflow.')); ?></p>
            <div class="ccx-search">
                <i class="fa fa-search"></i>
                <input type="text" id="ccx-report-search" placeholder="<?= html_escape(ccx_lang('ccx_reports_search_placeholder', 'Search reports by name or description…')); ?>">
            </div>
        </div>

        <?php if (empty($sections)) { ?>
            <div class="ccx-empty-state">
                <h4 class="tw-text-lg tw-font-semibold tw-m-0"><?= html_escape(ccx_lang('ccx_reports_empty', 'No reports have been configured yet.')); ?></h4>
                <p class="tw-mt-2 tw-text-sm tw-text-neutral-500"><?= html_escape(ccx_lang('ccx_reports_empty_hint', 'Create a template and assign it to a section to see it listed here.')); ?></p>
            </div>
        <?php } ?>

        <?php foreach (($sections ?? []) as $section) { ?>
            <div class="ccx-section-card ccx-report-section">
                <div class="ccx-section-heading">
                    <div>
                        <h2><?= html_escape($section['name']); ?></h2>
                        <?php if (! empty($section['description'])) { ?>
                            <span><?= html_escape($section['description']); ?></span>
                        <?php } ?>
                    </div>
                    <?php if (! empty($section['templates'])) { ?>
                        <span><?= count($section['templates']); ?> <?= html_escape(ccx_lang('ccx_reports_template_list', 'Available Reports')); ?></span>
                    <?php } ?>
                </div>

                <?php if (empty($section['templates'])) { ?>
                    <div class="ccx-empty-state tw-mt-0 tw-text-left">
                        <?= html_escape(ccx_lang('ccx_reports_no_templates', 'No report templates are assigned to this section yet.')); ?>
                    </div>
                <?php } else { ?>
                    <div class="ccx-template-grid">
                        <?php foreach ($section['templates'] as $template) { ?>
                            <a href="<?= admin_url('ccx/report/' . (int) $template['id']); ?>"
                               class="ccx-template-card ccx-report-item"
                               data-report-name="<?= html_escape(strtolower($template['name'] . ' ' . ($template['description'] ?? ''))); ?>">
                                <h3><?= html_escape($template['name']); ?></h3>
                                <p><?= html_escape($template['description'] ?? ccx_lang('ccx_reports_card_placeholder', 'Launch detailed insights in seconds.')); ?></p>
                                <div class="ccx-template-meta">
                                    <span><i class="fa fa-bar-chart"></i><?= html_escape(ccx_lang('ccx_reports_launch_cta', 'Open Report')); ?></span>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function($) {
    "use strict";
    $('#ccx-report-search').on('keyup', function () {
        const needle = $.trim($(this).val().toLowerCase());

        $('.ccx-report-section').each(function() {
            let visibleItems = 0;
            $(this).find('.ccx-report-item').each(function() {
                const name = $(this).data('reportName');
                const matches = !needle || (name && name.indexOf(needle) !== -1);
                $(this).toggle(matches);
                if (matches) {
                    visibleItems++;
                }
            });
            $(this).toggle(visibleItems > 0 || !needle);
        });
    });
})(jQuery);
</script>
</body>
</html>
