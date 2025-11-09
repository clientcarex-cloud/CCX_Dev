<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot20">
                            <h4 class="pull-left bold"><?php echo html_escape($form['name']); ?> · Versions</h4>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('ccx_creator/form/' . $form['id']); ?>" class="btn btn-default">
                                    <i class="fa fa-long-arrow-left"></i> Back to builder
                                </a>
                            </div>
                        </div>
                        <p class="text-muted">Every save creates a snapshot automatically. Restore any point-in-time definition with one click.</p>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Note</th>
                                        <th>Created by</th>
                                        <th>Created at</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($versions as $version) : ?>
                                        <tr>
                                            <td>#<?php echo (int) $version['id']; ?></td>
                                            <td><?php echo html_escape($version['note'] ?: 'Auto snapshot'); ?></td>
                                            <td>
                                                <?php if (! empty($version['created_by'])) : ?>
                                                    <?php echo get_staff_full_name($version['created_by']); ?>
                                                <?php else : ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo _dt($version['created_at']); ?></td>
                                            <td class="text-right">
                                                <a href="<?php echo admin_url('ccx_creator/restore_version/' . $form['id'] . '/' . $version['id']); ?>" class="btn btn-default btn-icon" onclick="return confirm('Restore this version?');">
                                                    <i class="fa fa-undo"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($versions)) : ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No versions stored yet.</td>
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
