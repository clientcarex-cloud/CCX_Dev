<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'medicine') || staff_can('view',  'medicine')) { ?>
                <div class="_buttons tw-mb-2">
                    <?php if (staff_can('create',  'medicine')) { ?>
                    <a href="<?php echo admin_url('medicine/add'); ?>"
                        class="btn btn-primary pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('medicine'); ?>
                    </a>
                    <?php } ?>
                    
                    <div class="clearfix"></div>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            _l('medicine_name'),
                            ], 'medicine'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-surveys', window.location.href);
});
</script>
</body>

</html>