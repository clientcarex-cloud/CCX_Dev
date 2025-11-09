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
<h4 class="no-margin">
    <?= _l($title); ?>
    <!--<a class="btn btn-info mbot30 pull-right" data-toggle="modal" data-target="#addAppointmentModal">
        <?= _l('add_new_appointment'); ?>
    </a>-->
</h4>

<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<div class="row justify-content-center">
    <div class="col-md-4 d-flex justify-content-center"></div>
    <div class="col-md-6 d-flex justify-content-center">
        <form method="post" action="<?= admin_url('client/appointment_reports'); ?>" class="form-inline d-flex align-items-center" style="gap: 10px;">
            <?php
                $posted_date = $this->input->post('consulted_date');
                $default_date = date('Y-m-d');
                $consulted_date_value = $posted_date ? $posted_date : $default_date;
            ?>
            <input type="date" class="form-control" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">

            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

            <input type="submit" class="btn btn-success" name="Submit" value="<?= _l('get_details'); ?>">
        </form>
    </div>
</div>
<?php echo render_datatable([
    _l('patient'),
    _l('mr_no'),
    _l('treatment'),
    _l('source'),
    _l('visited_date'),
    _l('visit_status'),
    _l('doctor'),
    _l('appointment_date'),
    _l('appointment_type'),
    _l('cosult_fee'),
    _l('package_amount'),
    _l('paid_amount'),
    _l('due_amount'),
], 'appointment_reports'); ?>




</div>



</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<script>
 

$(function(){
	var consulted_date = $('#consulted_date').val();
    initDataTable('.table-appointment_reports', '<?= admin_url("client/appointment_reports/") ?>' + consulted_date, [1], [1]);
});

</script>


</body>
</html>
