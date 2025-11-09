<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin" style="display: inline-block"><?= _l('eod_list'); ?></h4>
						<a href="<?= admin_url('eod/create') ?>" class="btn btn-primary mbot15 pull-right"><?= _l('new_eod'); ?></a>
						<hr class="hr-panel-heading" />
						<div class="clearfix"></div>

                        <?php echo render_datatable([
                            _l('date'),
                            _l('eodid'),
                            _l('employee_name'),
                            _l('branch'),
                            _l('designation'),
                            _l('subject'),
                            _l('activity'),
                            _l('today_report'),
                            _l('status'),
                            _l('actions')
                        ], 'eod'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    initDataTable('.table-eod', '<?= admin_url('eod/my_eod'); ?>', [1], [1]);
});
</script>

</body>
</html>
