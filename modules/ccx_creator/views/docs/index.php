<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix mtop5 mbot20">
                            <h4 class="pull-left bold">CCX Creator Documentation</h4>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('ccx_creator'); ?>" class="btn btn-default">
                                    <i class="fa fa-long-arrow-left"></i> Back to builders
                                </a>
                            </div>
                        </div>
                        <p class="text-muted">Short playbooks covering the most common workflows. Bookmark this page or share the direct link.</p>

                        <?php foreach ($topics as $topic) : ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong><?php echo html_escape($topic['title']); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <p class="mbot0"><?php echo nl2br(html_escape($topic['body'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="alert alert-info mtop20">
                            Need something more advanced? Drop your requirements with CCX support—we can extend Creator with custom widgets, APIs, or scheduled automations.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
