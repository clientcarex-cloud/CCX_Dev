<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                            <div>
                                <h4 class="no-margin">
                                    <?php echo html_escape($section['label'] ?? 'Menus'); ?>
                                </h4>
                                <p class="text-muted mtop5">
                                    Review every menu entry powering Perfex’s sidebar, toggle availability, or evolve the information architecture.
                                </p>
                            </div>
                            <div>
                                <a href="<?php echo admin_url('ccx_creator/menu_form'); ?>" class="btn btn-primary">
                                    <i class="fa-regular fa-plus mright5" aria-hidden="true"></i>
                                    Add Menu
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive mtop30">
                            <table class="table table-striped table-hover ccx-creator-menu-table">
                                <thead>
                                    <tr>
                                        <th>S. No</th>
                                        <th>Menu Name</th>
                                        <th>Sub-Menus</th>
                                        <th>Description</th>
                                        <th>Role Access</th>
                                        <th>Status</th>
                                        <th>Last Created &amp; Modified</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (! empty($menus)) : ?>
                                        <?php foreach ($menus as $index => $menu) : ?>
                                            <tr>
                                                <td><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <span class="tw-text-primary-600">
                                                            <i class="<?php echo html_escape($menu['icon']); ?>" aria-hidden="true"></i>
                                                        </span>
                                                        <div>
                                                            <strong><?php echo html_escape($menu['name']); ?></strong>
                                                            <div class="text-muted">
                                                                <?php echo html_escape($menu['slug']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (! empty($menu['submenus'])) : ?>
                                                        <div class="tw-flex tw-flex-wrap tw-gap-1">
                                                            <?php foreach ($menu['submenus'] as $submenu) : ?>
                                                                <span class="label label-default" title="<?php echo html_escape($submenu['role_labels'] ?? ''); ?>">
                                                                    <i class="<?php echo html_escape($submenu['icon']); ?> mright5" aria-hidden="true"></i>
                                                                    <?php echo html_escape($submenu['name']); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted">
                                                    <?php if (! empty($menu['description'])) : ?>
                                                        <?php echo nl2br(html_escape($menu['description'])); ?>
                                                    <?php else : ?>
                                                        <span>Not documented yet.</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="label label-info">
                                                        <?php echo html_escape($menu['role_labels']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="onoffswitch">
                                                        <input type="checkbox"
                                                            class="onoffswitch-checkbox ccx-toggle-menu"
                                                            id="menu_toggle_<?php echo (int) $menu['id']; ?>"
                                                            data-url="<?php echo admin_url('ccx_creator/toggle_menu_status/' . (int) $menu['id']); ?>"
                                                            <?php echo (int) $menu['status'] === 1 ? 'checked' : ''; ?>>
                                                        <label class="onoffswitch-label" for="menu_toggle_<?php echo (int) $menu['id']; ?>"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>Created:</strong>
                                                        <?php echo _dt($menu['created_at']); ?>
                                                        <?php if (! empty($menu['created_by'])) : ?>
                                                            <span class="text-muted">
                                                                by <?php echo html_escape(get_staff_full_name($menu['created_by'])); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="mtop5">
                                                        <strong>Updated:</strong>
                                                        <?php echo _dt($menu['updated_at']); ?>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?php echo admin_url('ccx_creator/menu_form/' . (int) $menu['id']); ?>" class="btn btn-default btn-icon">
                                                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                                    </a>
                                                    <?php if ($menu['can_delete']) : ?>
                                                        <a href="<?php echo admin_url('ccx_creator/delete_menu/' . (int) $menu['id']); ?>"
                                                            class="btn btn-danger btn-icon _delete">
                                                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                                        </a>
                                                    <?php else : ?>
                                                        <button type="button"
                                                            class="btn btn-danger btn-icon"
                                                            disabled
                                                            data-toggle="tooltip"
                                                            data-title="Deletion allowed only on the creation day.">
                                                            <i class="fa-regular fa-ban" aria-hidden="true"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No menus recorded yet. Use the "Add Menu" button to start building your navigation.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo module_dir_url(CCX_CREATOR_MODULE_NAME, 'assets/js/menus.js'); ?>"></script>
<?php init_tail(); ?>
