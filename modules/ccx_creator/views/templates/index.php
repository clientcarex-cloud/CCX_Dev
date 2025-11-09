<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot20">
                            <h4 class="pull-left bold">Template Marketplace</h4>
                            <div class="pull-right btn-group">
                                <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default">
                                    <i class="fa fa-long-arrow-left"></i> Builders
                                </a>
                                <a href="<?php echo admin_url('ccx_creator/docs'); ?>" class="btn btn-default">
                                    <i class="fa fa-book"></i> Docs
                                </a>
                            </div>
                        </div>
                        <p class="text-muted">Kickstart new builders from curated templates. Save your own forms to share across teams.</p>

                        <div class="row">
                            <?php foreach ($templates as $template) : ?>
                                <div class="col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <strong><?php echo html_escape($template['name']); ?></strong>
                                            <?php if (! empty($template['category'])) : ?>
                                                <span class="label label-default pull-right"><?php echo html_escape($template['category']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="panel-body">
                                            <p><?php echo html_escape($template['description'] ?: 'No description'); ?></p>
                                            <?php if (! empty($template['tags'])) : ?>
                                                <p class="text-muted small">
                                                    <i class="fa fa-tags"></i>
                                                    <?php echo html_escape($template['tags']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="btn-group btn-group-justified mtop10">
                                                <a class="btn btn-default" href="<?php echo admin_url('ccx_creator/form?template=' . (int) $template['id']); ?>">
                                                    <i class="fa fa-play"></i> Use template
                                                </a>
                                                <a class="btn btn-danger ccx-template-delete" data-id="<?php echo (int) $template['id']; ?>" href="#">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($templates)) : ?>
                                <div class="col-md-12 text-center text-muted">
                                    No templates yet. Save one from any builder to populate this gallery.
                                </div>
                            <?php endif; ?>
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
        const tokenInput = document.querySelector('input[name="csrf_token_name"]');
        return tokenInput ? { csrf_token_name: tokenInput.value } : {};
    };

    document.querySelectorAll('.ccx-template-delete').forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            if (!confirm('Delete this template?')) {
                return;
            }
            $.post(adminUrl + 'ccx_creator/template_delete/' + this.dataset.id, getCsrf(), function (response) {
                if (response && response.success) {
                    location.reload();
                } else {
                    alert('Unable to delete template.');
                }
            }, 'json');
        });
    });
})();
</script>
<?php init_tail(); ?>
