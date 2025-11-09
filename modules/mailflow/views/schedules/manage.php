<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>

                <?php if (has_permission('mailflow', '', 'create')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('mailflow/manage'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('mailflow_schedule_campaign'); ?>
                        </a>
                    </div>
                <?php } ?>

                <div class="panel_s">
                    <div class="col-md-12 panel-body">
                        <?php render_datatable([
                            _l('id'),
                            _l('mailflow_scheduled_to'),
                            _l('mailflow_campaign_status'),
                            _l('mailflow_scheduled_by'),
                            _l('mailflow_created_at'),
                            _l('options'),
                        ], 'schedules'); ?>

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
            initDataTable('.table-schedules', window.location.href, [0], [0], [], [0, 'desc']);
        });
    });
</script>
