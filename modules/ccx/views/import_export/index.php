<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$canExport = staff_can('view', 'ccx_import_export') || is_admin();
$canImport = staff_can('create', 'ccx_import_export') || is_admin();
?>
<style>
    .ccx-hero {
        background: linear-gradient(135deg, #0f172a, #2563eb);
        border-radius: 22px;
        padding: 36px 40px;
        color: #f8fafc;
        margin-bottom: 28px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .ccx-hero h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }
    .ccx-hero p {
        margin: 0;
        max-width: 640px;
        font-size: 15px;
        opacity: 0.85;
    }
    .ccx-import-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }
    .ccx-import-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 28px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .ccx-import-card h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #0f172a;
    }
    .ccx-import-card p {
        margin: 0;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }
    .ccx-import-card .btn {
        align-self: flex-start;
        border-radius: 14px;
        padding: 10px 20px;
        font-weight: 600;
    }
    .ccx-import-card form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .ccx-import-card label {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .ccx-import-card .help-text {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }
    @media (max-width: 600px) {
        .ccx-hero {
            padding: 28px;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="ccx-hero">
            <h1><?= html_escape($title); ?></h1>
            <p><?= html_escape(ccx_lang('ccx_import_export_intro', 'Backup or migrate CCX report templates and sections using JSON bundles.')); ?></p>
        </div>

        <div class="ccx-import-grid">
            <div class="ccx-import-card">
                <div>
                    <h2><?= html_escape(ccx_lang('ccx_import_export_export_title', 'Export reports')); ?></h2>
                    <p><?= html_escape(ccx_lang('ccx_import_export_export_desc', 'Download every template, column configuration and section assignment as a JSON file.')); ?></p>
                </div>
                <?php if ($canExport) { ?>
                    <?= form_open($exportUrl, ['method' => 'get']); ?>
                        <div>
                            <label class="control-label" for="ccx-export-template"><?= html_escape(ccx_lang('ccx_import_export_export_select_label', 'Choose report')); ?></label>
                            <select id="ccx-export-template" name="template_id" class="form-control">
                                <option value=""><?= html_escape(ccx_lang('ccx_import_export_export_all_option', 'All reports')); ?></option>
                                <?php foreach ($templatesList as $template) { ?>
                                    <option value="<?= (int) $template['id']; ?>"><?= html_escape($template['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-download mright5"></i><?= html_escape(ccx_lang('ccx_import_export_export_button', 'Download JSON')); ?>
                        </button>
                    <?= form_close(); ?>
                <?php } ?>
            </div>

            <div class="ccx-import-card">
                <div>
                    <h2><?= html_escape(ccx_lang('ccx_import_export_import_title', 'Import reports')); ?></h2>
                    <p><?= html_escape(ccx_lang('ccx_import_export_import_desc', 'Restore templates and sections from a CCX JSON export. Existing items are left untouched.')); ?></p>
                </div>
                <?php if ($canImport) { ?>
                    <?= form_open_multipart($importAction); ?>
                        <div>
                            <label class="control-label" for="ccx-import-file"><?= html_escape(ccx_lang('ccx_import_export_upload_label', 'JSON file')); ?></label>
                            <input type="file"
                                   class="form-control"
                                   id="ccx-import-file"
                                   name="import_file"
                                   accept=".json,application/json"
                                   >
                        </div>
                        <div>
                            <label class="control-label" for="ccx-import-json"><?= html_escape(ccx_lang('ccx_import_export_import_textarea_label', 'JSON payload')); ?></label>
                            <textarea id="ccx-import-json" name="import_json" class="form-control" rows="8" placeholder="<?= html_escape(ccx_lang('ccx_import_export_import_textarea_placeholder', 'Paste a CCX export bundle here…')); ?>"></textarea>
                        </div>
                        <p class="help-text"><?= html_escape(ccx_lang('ccx_import_export_import_help', 'A successful import creates new templates and sections without overwriting existing ones.')); ?></p>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-upload mright5"></i><?= html_escape(ccx_lang('ccx_import_export_import_button', 'Import bundle')); ?>
                        </button>
                    <?= form_close(); ?>
                <?php } else { ?>
                    <p class="help-text"><?= html_escape(ccx_lang('ccx_import_export_import_help', 'A successful import creates new templates and sections without overwriting existing ones.')); ?></p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
