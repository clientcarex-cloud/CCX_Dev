<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo html_escape($submenu['name'] ?? $menu['name']); ?>
                        </h4>
                        <p class="text-muted mtop5">
                            This placeholder confirms the navigation item is registered. Attach future builders, dashboards, or automations to this route.
                        </p>
                        <div class="row mtop20">
                            <div class="col-md-6">
                                <div class="tw-bg-slate-50 tw-rounded tw-p-3 tw-h-full">
                                    <h5 class="tw-text-sm tw-uppercase tw-text-slate-500">Menu snapshot</h5>
                                    <p class="mtop10">
                                        <strong>Name:</strong> <?php echo html_escape($menu['name']); ?><br>
                                        <strong>Icon:</strong> <i class="<?php echo html_escape($menu['icon']); ?>" aria-hidden="true"></i>
                                    </p>
                                    <p class="text-muted">
                                        <?php echo ! empty($menu['description']) ? nl2br(html_escape($menu['description'])) : 'No description yet.'; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tw-bg-white tw-rounded tw-border tw-border-slate-100 tw-p-3 tw-h-full">
                                    <h5 class="tw-text-sm tw-uppercase tw-text-slate-500">Sub-menu context</h5>
                                    <?php if (! empty($submenu)) : ?>
                                        <p class="mtop10">
                                            <strong>Label:</strong> <?php echo html_escape($submenu['name']); ?><br>
                                            <strong>Icon:</strong> <i class="<?php echo html_escape($submenu['icon']); ?>" aria-hidden="true"></i>
                                        </p>
                                    <?php else : ?>
                                        <p class="text-muted mtop10">Currently viewing the parent menu.</p>
                                    <?php endif; ?>
                                    <p class="mtop10">
                                        <strong>Status:</strong>
                                        <?php echo ((int) ($submenu['status'] ?? $menu['status']) === 1) ? 'Active' : 'Inactive'; ?>
                                    </p>
                                    <p class="text-muted">
                                    Created on <?php echo _dt($menu['created_at']); ?> |
                                    Updated on <?php echo _dt($menu['updated_at']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
