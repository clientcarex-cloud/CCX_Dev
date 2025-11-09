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
                        <a href="<?php echo admin_url('mailflow/create_template'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('mailflow_add_template'); ?>
                        </a>
                    </div>
                <?php } ?>

                <div class="panel_s">
                    <div class="col-md-12 panel-body">
                        <?php render_datatable([
                            _l('id'),
                            _l('mailflow_template_name'),
                            _l('mailflow_template_subject'),
                            _l('mailflow_created_at'),
                            _l('options'),
                        ], 'newsletter-templates'); ?>

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
            initDataTable('.table-newsletter-templates', window.location.href, [3], [3], [], [3, 'desc']);
        });
    });
</script>
</body>
</html>
