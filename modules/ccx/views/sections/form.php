<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .ccx-form-hero {
        background: linear-gradient(135deg, #0F172A 0%, #0EA5E9 55%, #14B8A6 100%);
        border-radius: 22px;
        padding: 32px 38px;
        color: #E0F2FE;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .ccx-form-hero h1 {
        margin: 0 0 8px;
        font-size: 26px;
        font-weight: 700;
    }
    .ccx-form-hero p {
        margin: 0;
        font-size: 15px;
        opacity: 0.9;
        max-width: 520px;
        line-height: 1.6;
    }
    .ccx-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #E0F2FE;
        font-weight: 500;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(224, 242, 254, 0.35);
        background: rgba(15, 23, 42, 0.22);
        transition: background 160ms ease, transform 160ms ease;
    }
    .ccx-back-link:hover {
        background: rgba(224, 242, 254, 0.18);
        transform: translateY(-1px);
    }
    .ccx-form-card {
        background: #FFFFFF;
        border-radius: 22px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.12);
        padding: 28px 32px;
    }
    .ccx-form-grid {
        display: grid;
        gap: 20px;
    }
    .ccx-form-grid label {
        font-weight: 600;
        color: #0F172A;
    }
    .ccx-form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 28px;
    }
    @media (max-width: 768px) {
        .ccx-form-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 26px 26px;
        }
        .ccx-form-card {
            padding: 22px 22px 28px;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-form-hero">
            <div>
                <a href="<?= admin_url('ccx/sections'); ?>" class="ccx-back-link">
                    <i class="fa fa-arrow-left"></i>
                    <?= html_escape(ccx_lang('ccx_reports_back_to_list', 'Back to Reports')); ?>
                </a>
                <h1><?= html_escape($title); ?></h1>
                <p><?= html_escape(ccx_lang('ccx_sections_intro', 'Organise report templates into branded sections and curate what your team sees.')); ?></p>
            </div>
        </div>

        <div class="ccx-form-card">
            <?php
            $action = 'ccx/section' . (isset($section['id']) ? '/' . (int) $section['id'] : '');
            echo form_open(admin_url($action));
            ?>
            <div class="ccx-form-grid">
                <div class="form-group">
                    <label for="ccx-section-name"><?= html_escape(ccx_lang('ccx_section_field_name', 'Section Name')); ?> *</label>
                    <input type="text" id="ccx-section-name" name="section[name]" class="form-control" value="<?= html_escape($section['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ccx-section-description"><?= html_escape(ccx_lang('ccx_section_field_description', 'Description')); ?></label>
                    <textarea id="ccx-section-description" name="section[description]" class="form-control" rows="3"><?= html_escape($section['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="ccx-section-order"><?= html_escape(ccx_lang('ccx_section_field_order', 'Display Order')); ?></label>
                    <input type="number" id="ccx-section-order" name="section[display_order]" class="form-control" value="<?= html_escape($section['display_order'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label><?= html_escape(ccx_lang('ccx_section_field_templates', 'Report Templates')); ?></label>
                    <select name="template_ids[]" class="form-control selectpicker" data-width="100%" multiple data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                        <?php foreach ($templates as $templateOption) { ?>
                            <option value="<?= (int) $templateOption['id']; ?>" <?= in_array((int) $templateOption['id'], $selectedIds ?? [], true) ? 'selected' : ''; ?>>
                                <?= html_escape($templateOption['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div class="tw-text-xs tw-text-neutral-500 tw-mt-1"><?= html_escape(ccx_lang('ccx_section_templates_hint', 'Choose the templates that should appear under this section.')); ?></div>
                </div>
            </div>

            <div class="ccx-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?= html_escape(ccx_lang('ccx_section_submit', 'Save Section')); ?>
                </button>
                <a href="<?= admin_url('ccx/sections'); ?>" class="btn btn-light"><?= _l('cancel'); ?></a>
            </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    init_selectpicker();
    appValidateForm($('form'), {
        'section[name]': 'required'
    });
});
</script>
</body>
</html>
