<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$fieldLabels = [];
if (! empty($form['fields'])) {
    foreach ($form['fields'] as $fieldDef) {
        $fieldLabels[$fieldDef['field_key']] = $fieldDef['label'];
    }
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot10">
                            <h4 class="pull-left bold"><?php echo html_escape($form['name']); ?> · Submissions</h4>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default"><i class="fa fa-long-arrow-left"></i> Back</a>
                                <a href="<?php echo admin_url('ccx_creator/entry/' . $form['id']); ?>" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> New submission
                                </a>
                            </div>
                        </div>
                        <p class="text-muted">Track every record captured through this builder. Data includes audit timestamps and the key/value payload.</p>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Submitted</th>
                                        <th>Captured By</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record) : ?>
                                        <tr>
                                            <td>#<?php echo (int) $record['id']; ?></td>
                                            <td><?php echo _dt($record['created_at']); ?></td>
                                            <td>
                                                <?php if (! empty($record['created_by'])) : ?>
                                                    <?php echo get_staff_full_name($record['created_by']); ?>
                                                <?php else : ?>
                                                    <span class="text-muted">Portal / public</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:300px;">
                                                <ul class="list-unstyled mtop10 mbot0">
                                                    <?php foreach ($record['data'] as $key => $value) : ?>
                                                        <li>
                                                            <strong><?php echo html_escape($fieldLabels[$key] ?? $key); ?>:</strong>
                                                            <span><?php echo html_escape(is_array($value) ? json_encode($value) : (string) $value); ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($records)) : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No submissions yet.</td>
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
