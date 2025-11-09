<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$menuData         = is_array($menu ?? null) ? $menu : [];
$menuSubmenus     = $menuData['submenus'] ?? [];
$menuRoleSelected = $menuData['role_access'] ?? [];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                            <div>
                                <h4 class="no-margin"><?php echo html_escape($title); ?></h4>
                                <p class="text-muted mtop5">
                                    Define menu metadata, align role access, and craft sub navigation items for your low-code workspace.
                                </p>
                            </div>
                            <div>
                                <a href="<?php echo admin_url('ccx_creator/menus'); ?>" class="btn btn-default">
                                    <i class="fa-regular fa-arrow-left mright5" aria-hidden="true"></i>
                                    Back to Menus
                                </a>
                            </div>
                        </div>

                        <?php echo form_open(current_url(), ['id' => 'ccx-menu-form', 'autocomplete' => 'off']); ?>
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <div class="row mtop30">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="menu-name" class="control-label">Menu Name</label>
                                        <input type="text"
                                            id="menu-name"
                                            name="name"
                                            class="form-control"
                                            required
                                            value="<?php echo html_escape($menuData['name'] ?? ''); ?>"
                                            placeholder="e.g. Sales Command Center">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="menu-icon" class="control-label">Icon</label>
                                        <input type="text"
                                            id="menu-icon"
                                            name="icon"
                                            class="form-control"
                                            value="<?php echo html_escape($menuData['icon'] ?? 'fa-regular fa-square'); ?>"
                                            placeholder="fa-regular fa-laptop">
                                        <small class="text-muted">
                                            Use Font Awesome outline classes (e.g. <code>fa-regular fa-chart-bar</code>).
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="menu-description" class="control-label">
                                            Description
                                        </label>
                                        <textarea id="menu-description"
                                            name="description"
                                            rows="4"
                                            class="form-control"
                                            placeholder="Describe how this menu should be used."><?php echo html_escape($menuData['description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Role Access</label>
                                        <select name="role_access[]"
                                            class="selectpicker"
                                            data-width="100%"
                                            multiple
                                            data-actions-box="true"
                                            title="All staff">
                                            <?php foreach ($roles as $role) : ?>
                                                <?php $selected = in_array((int) $role['roleid'], $menuRoleSelected, true) ? 'selected' : ''; ?>
                                                <option value="<?php echo (int) $role['roleid']; ?>" <?php echo $selected; ?>>
                                                    <?php echo html_escape($role['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted d-block mtop5">
                                            Leave empty to expose this menu to every staff member.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <hr class="hr-panel-heading" />

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 mtop10 mbot20">
                                        <h5 class="no-margin">Sub-Menus</h5>
                                        <button type="button" class="btn btn-default" id="ccx-add-submenu">
                                            <i class="fa-regular fa-plus mright5" aria-hidden="true"></i>
                                            Add Sub-Menu
                                        </button>
                                    </div>
                                    <div id="ccx-submenu-wrapper" data-count="<?php echo ! empty($menuSubmenus) ? count($menuSubmenus) : 0; ?>">
                                        <?php if (! empty($menuSubmenus)) : ?>
                                            <?php foreach ($menuSubmenus as $idx => $submenu) : ?>
                                                <?php include __DIR__ . '/submenu_row.php'; ?>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <div class="ccx-submenu-empty tw-text-center tw-text-slate-500 tw-bg-slate-50 tw-border tw-border-dashed tw-border-slate-300 tw-rounded tw-py-4">
                                                No sub-menus yet. Use "Add Sub-Menu" to start crafting contextual navigation.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-flex tw-justify-end tw-gap-2 mtop30">
                                <a href="<?php echo admin_url('ccx_creator/menus'); ?>" class="btn btn-default">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo ! empty($menuData) ? 'Update Menu' : 'Create Menu'; ?>
                                </button>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="ccx-submenu-template">
    <div class="panel panel-default ccx-submenu-card" data-index="__INDEX__">
        <div class="panel-heading tw-flex tw-items-center tw-justify-between tw-gap-2">
            <strong>Sub-Menu __HUMAN_INDEX__</strong>
            <button type="button" class="btn btn-link text-danger ccx-remove-submenu">
                <i class="fa-regular fa-trash-can mright5" aria-hidden="true"></i>
                Delete
            </button>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text"
                            class="form-control"
                            name="submenus[__INDEX__][name]"
                            placeholder="e.g. Pipeline">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Icon</label>
                        <input type="text"
                            class="form-control"
                            name="submenus[__INDEX__][icon]"
                            value="fa-regular fa-circle-dot"
                            placeholder="fa-regular fa-object-group">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Access</label>
                        <select name="submenus[__INDEX__][role_access][]"
                            class="selectpicker"
                            data-width="100%"
                            multiple
                            data-actions-box="true"
                            title="All staff">
                            <?php foreach ($roles as $role) : ?>
                                <option value="<?php echo (int) $role['roleid']; ?>">
                                    <?php echo html_escape($role['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group text-center">
                        <label class="control-label">Status</label>
                        <div class="onoffswitch">
                            <input type="hidden" name="submenus[__INDEX__][status]" value="0">
                            <input type="checkbox"
                                class="onoffswitch-checkbox"
                                id="__STATUS_ID__"
                                name="submenus[__INDEX__][status]"
                                value="1"
                                checked>
                            <label class="onoffswitch-label" for="__STATUS_ID__"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
<script src="<?php echo module_dir_url(CCX_CREATOR_MODULE_NAME, 'assets/js/menus.js'); ?>"></script>
<?php init_tail(); ?>
