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
                                        <th>Status / Approvals</th>
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
                                            <td style="min-width:250px;">
                                                <?php echo render_status_label($record['status']); ?>
                                                <?php if (! empty($record['steps'])) : ?>
                                                    <ul class="list-unstyled mtop10 ccx-approval-list">
                                                        <?php foreach ($record['steps'] as $step) : ?>
                                                            <?php
                                                                $definition = $step['definition'] ?? [];
                                                                $label = $definition['label'] ?? 'Step';
                                                                $status = $step['status'];
                                                            ?>
                                                            <li class="mtop5">
                                                                <strong><?php echo html_escape($label); ?>:</strong>
                                                                <?php echo render_step_status($status); ?>
                                                                <?php if ($step['can_act'] && $status === 'active') : ?>
                                                                    <div class="btn-group btn-group-xs mtop5">
                                                                        <button type="button" class="btn btn-success ccx-approval-action" data-action="approve" data-step-id="<?php echo (int) $step['id']; ?>">Approve</button>
                                                                        <button type="button" class="btn btn-danger ccx-approval-action" data-action="reject" data-step-id="<?php echo (int) $step['id']; ?>">Reject</button>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if ($step['comment']) : ?>
                                                                    <div class="text-muted small">Comment: <?php echo html_escape($step['comment']); ?></div>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <span class="text-muted small">No approval routing.</span>
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
                                            <td colspan="5" class="text-center text-muted">No submissions yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold">Recent Activity</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>When</th>
                                        <th>Actor</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($audits as $audit) : ?>
                                        <?php $details = json_decode($audit['details'] ?? '[]', true); ?>
                                        <tr>
                                            <td><?php echo _dt($audit['created_at']); ?></td>
                                            <td>
                                                <?php if (! empty($audit['staff_id'])) : ?>
                                                    <?php echo get_staff_full_name($audit['staff_id']); ?>
                                                <?php else : ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo html_escape($audit['action']); ?></code></td>
                                            <td>
                                                <?php if (! empty($details) && is_array($details)) : ?>
                                                    <ul class="list-unstyled mbot0">
                                                        <?php foreach ($details as $key => $value) : ?>
                                                            <li><strong><?php echo html_escape($key); ?>:</strong> <?php echo html_escape(is_scalar($value) ? (string) $value : json_encode($value)); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($audits)) : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No activity yet.</td>
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
<?php
function render_status_label($status)
{
    switch ($status) {
        case 'approved':
            return '<span class="label label-success">Approved</span>';
        case 'rejected':
            return '<span class="label label-danger">Rejected</span>';
        case 'awaiting_approval':
            return '<span class="label label-warning">Awaiting approval</span>';
        default:
            return '<span class="label label-default">' . ucfirst(html_escape($status)) . '</span>';
    }
}

function render_step_status($status)
{
    $map = [
        'pending'  => '<span class="label label-default">Pending</span>',
        'active'   => '<span class="label label-warning">Active</span>',
        'approved' => '<span class="label label-success">Approved</span>',
        'rejected' => '<span class="label label-danger">Rejected</span>',
    ];

    return $map[$status] ?? '<span class="label label-default">' . ucfirst(html_escape($status)) . '</span>';
}
?>

<script>
(function () {
    const adminUrl = '<?php echo admin_url(); ?>';
    const getCsrf = () => {
        const input = document.querySelector('input[name="csrf_token_name"]');
        return input ? { csrf_token_name: input.value } : {};
    };

    document.querySelectorAll('.ccx-approval-action').forEach((button) => {
        button.addEventListener('click', function () {
            const action = this.dataset.action;
            const stepId = this.dataset.stepId;
            const comment = prompt('Optional comment for this step?') || '';

            const payload = Object.assign({
                action: action,
                comment: comment,
            }, getCsrf());

            $.post(adminUrl + 'ccx_creator/record_step_action/' + stepId, payload, function (response) {
                if (response && response.success) {
                    location.reload();
                } else {
                    alert(response && response.message ? response.message : 'Unable to update step.');
                }
            }, 'json');
        });
    });
})();
</script>
<?php init_tail(); ?>
