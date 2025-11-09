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
                        <h4 class="no-margin"><?= _l('eod_list'); ?></h4>
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
    initDataTable('.table-eod', '<?= admin_url('eod/all_eod'); ?>', [1], [1]);
});

function updateEodStatus(eodId, status) {
    $.post(admin_url + 'eod/update_status', {
        id: eodId,
        status: status
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
            $('.table-eod').DataTable().ajax.reload(null, false); // Reload table without resetting pagination
        } else {
            alert_float('danger', response.message);
        }
    });
}

</script>

</body>
</html>
