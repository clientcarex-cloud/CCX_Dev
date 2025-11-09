<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot15">
                            <h4 class="pull-left bold">Dashboards</h4>
                            <div class="pull-right btn-group">
                                <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default">
                                    <i class="fa fa-list"></i> Builders
                                </a>
                                <a href="<?php echo admin_url('ccx_creator/dashboard'); ?>" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> New dashboard
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Visibility</th>
                                        <th>Widgets</th>
                                        <th>Share</th>
                                        <th>Updated</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dashboards as $dashboard) : ?>
                                        <?php $shareable = $dashboard['visibility'] !== 'private'; ?>
                                        <tr>
                                            <td class="bold">
                                                <a href="<?php echo admin_url('ccx_creator/dashboard/' . $dashboard['id']); ?>">
                                                    <?php echo html_escape($dashboard['name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo ucfirst(html_escape($dashboard['visibility'])); ?></td>
                                            <td><?php echo $dashboard['layout'] ? count(json_decode($dashboard['layout'], true) ?? []) : 0; ?></td>
                                            <td>
                                                <?php if ($shareable && $dashboard['share_token']) : ?>
                                                    <code><?php echo substr($dashboard['share_token'], 0, 8); ?>…</code>
                                                <?php else : ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $dashboard['updated_at'] ? _dt($dashboard['updated_at']) : _dt($dashboard['created_at']); ?></td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="<?php echo admin_url('ccx_creator/dashboard/' . $dashboard['id']); ?>" class="btn btn-default btn-icon">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <?php if ($shareable) : ?>
                                                        <button type="button" class="btn btn-default btn-icon ccx-dashboard-share" data-id="<?php echo (int) $dashboard['id']; ?>">
                                                            <i class="fa fa-share-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-icon ccx-dashboard-delete" data-id="<?php echo (int) $dashboard['id']; ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($dashboards)) : ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No dashboards yet.</td>
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

<script>
(function () {
    const adminUrl = '<?php echo admin_url(); ?>';
    const getCsrf = () => {
        const input = document.querySelector('input[name="csrf_token_name"]');
        return input ? { csrf_token_name: input.value } : {};
    };

    document.querySelectorAll('.ccx-dashboard-share').forEach((button) => {
        button.addEventListener('click', function () {
            const dashboardId = this.dataset.id;
            $.post(adminUrl + 'ccx_creator/dashboard_share/' + dashboardId, getCsrf(), function (response) {
                if (!response || !response.success) {
                    alert('Unable to generate share link.');
                    return;
                }
                prompt('Copy the public link:', response.url);
            }, 'json');
        });
    });

    document.querySelectorAll('.ccx-dashboard-delete').forEach((button) => {
        button.addEventListener('click', function () {
            const dashboardId = this.dataset.id;
            if (!confirm('Delete this dashboard?')) {
                return;
            }
            $.post(adminUrl + 'ccx_creator/dashboard_delete/' + dashboardId, getCsrf(), function (response) {
                if (response && response.success) {
                    location.reload();
                } else {
                    alert('Unable to delete dashboard.');
                }
            }, 'json');
        });
    });
})();
</script>
<?php init_tail(); ?>
