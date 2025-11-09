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

<?php echo render_datatable([
    _l('patient'),
    _l('mr_no'),
   _l('doctor'),
    _l('treatment'),
    _l('appointment_date'),
    _l('consulted_date'),
    _l('package_amount'),
    _l('paid_amount'),
    _l('due_amount'),
], 'ownership_details'); ?>




</div>



</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<?php
$type = isset($type) ? $type : '';
$doctor_id = isset($doctor_id) ? $doctor_id : '';
?>

<script>
$(function() {
    var type = '<?= $type ?>';
    var doctor_id = '<?= $doctor_id ?>';

    var url = '<?= admin_url("client/ownership_details") ?>';

    // Append type and doctor_id only if not empty
    if (type !== '' && doctor_id !== '') {
        url += '/' + encodeURIComponent(type) + '/' + encodeURIComponent(doctor_id);
    } else if (type !== '') {
        url += '/' + encodeURIComponent(type);
    }

    initDataTable('.table-ownership_details', url, [1], [1]);
});
</script>




</body>
</html>
