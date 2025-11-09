<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>

                <div class="_buttons mbot20">
                <?php if (has_permission('mailflow_integrations', '', 'create')) { ?>
                    <a href="<?php echo admin_url('mailflow/create_integration'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('mailflow_add_integration'); ?>
                    </a>
                    <a href="<?php echo admin_url('mailflow/sms_integrations'); ?>" class="btn btn-success">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('mailflow_sms_integrations'); ?>
                    </a>
                <?php } ?>
                </div>


                <div class="panel_s">
                    <div class="col-md-12 panel-body">
                        <?php render_datatable([
                            _l('id'),
                            _l('mailflow_integration_name'),
                            _l('mailflow_created_at'),
                            _l('options'),
                        ], 'mailflow-integrations'); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function() {
        "use strict";
        $(function() {
            initDataTable('.table-mailflow-integrations', window.location.href, [2], [2], [], [2, 'desc']);
        });
    });
</script>
