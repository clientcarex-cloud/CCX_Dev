<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop10 mbot20">
                            <h4 class="pull-left bold">CCX Creator</h4>
                            <div class="pull-right btn-group">
                                <a href="<?php echo admin_url('ccx_creator/docs'); ?>" class="btn btn-default">
                                    <i class="fa fa-book"></i> Docs
                                </a>
                                <a href="<?php echo admin_url('ccx_creator/dashboards'); ?>" class="btn btn-default">
                                    <i class="fa fa-chart-line"></i> Dashboards
                                </a>
                                <a href="<?php echo admin_url('ccx_creator/form'); ?>" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> New Builder
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($forms as $form) : ?>
                                        <tr>
                                            <td class="bold">
                                                <a href="<?php echo admin_url('ccx_creator/form/' . $form['id']); ?>">
                                                    <?php echo html_escape($form['name']); ?>
                                                </a>
                                            </td>
                                            <td><code><?php echo html_escape($form['slug']); ?></code></td>
                                            <td>
                                                <?php if ((int) $form['status'] === 1) : ?>
                                                    <span class="label label-success">Active</span>
                                                <?php else : ?>
                                                    <span class="label label-default">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo _dt($form['created_at']); ?></td>
                                            <td><?php echo $form['updated_at'] ? _dt($form['updated_at']) : '—'; ?></td>
                                            <td class="text-right">
                                                <a href="<?php echo admin_url('ccx_creator/form/' . $form['id']); ?>" class="btn btn-default btn-icon">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="<?php echo admin_url('ccx_creator/versions/' . $form['id']); ?>" class="btn btn-default btn-icon">
                                                    <i class="fa fa-history"></i>
                                                </a>
                                                <a href="<?php echo admin_url('ccx_creator/records/' . $form['id']); ?>" class="btn btn-default btn-icon">
                                                    <i class="fa fa-database"></i>
                                                </a>
                                                <a href="<?php echo admin_url('ccx_creator/entry/' . $form['id']); ?>" class="btn btn-default btn-icon">
                                                    <i class="fa fa-play"></i>
                                                </a>
                                                <a href="<?php echo admin_url('ccx_creator/delete/' . $form['id']); ?>" class="btn btn-danger btn-icon _delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($forms)) : ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                No builders yet. Click “New Builder” to get started.
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
<?php init_tail(); ?>
