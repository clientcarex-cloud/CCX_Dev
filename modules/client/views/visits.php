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
<div class="row">
	<div class="col-md-4"></div>
	<div class="col-md-4">
		<form method="post" action="<?= admin_url('client/visits'); ?>">
		<?php
			$posted_date = $this->input->post('consulted_date');
			$default_date = date('Y-m-d'); // today's date in YYYY-MM-DD format

			$consulted_date_value = $posted_date ? $posted_date : $default_date;
			?>
			<input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">

	
		<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
		
	</div>
	
	<div class="col-md-4">
	<input type="Submit" class="btn btn-success" name="Submit" value="Get Details">
	</div>
	</form>
</div>
<?php echo render_datatable([
    _l('visit_id'),
    _l('mr_no'),
    _l('patient_name'),
    _l('patient_mobile'),
    _l('appointment_date'),
    _l('consulted_date'),
    _l('visit_status')
], 'appointments'); ?>




</div>

<!-- Add Appointment Modal -->
<div id="addAppointmentModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form action="<?= admin_url('appointments/add'); ?>" method="POST">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?= _l('add_appointment'); ?></h4>
    </div>
    <div class="modal-body">
        <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?= render_input('visit_id', 'visit_id'); ?>
        <?= render_input('mr_no', 'mr_no'); ?>
        <?= render_input('patient_name', 'patient_name'); ?>
        <?= render_input('patient_mobile', 'patient_mobile'); ?>
        <?= render_date_input('appointment_date', 'appointment_date'); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
        <button type="submit" class="btn btn-success"><?= _l('save'); ?></button>
    </div>
</form>
</div>
</div>
</div>

<!-- Edit Modal -->
<div id="editAppointmentModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form action="<?= admin_url('appointments/edit'); ?>" method="POST">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?= _l('edit_appointment'); ?></h4>
    </div>
    <div class="modal-body">
        <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <input type="hidden" name="id" id="edit_id">
        <?= render_input('visit_id', 'visit_id', '', 'text', ['id' => 'edit_visit_id']); ?>
        <?= render_input('mr_no', 'mr_no', '', 'text', ['id' => 'edit_mr_no']); ?>
        <?= render_input('patient_name', 'patient_name', '', 'text', ['id' => 'edit_patient_name']); ?>
        <?= render_input('patient_mobile', 'patient_mobile', '', 'text', ['id' => 'edit_patient_mobile']); ?>
        <?= render_date_input('appointment_date', 'appointment_date', '', ['id' => 'edit_appointment_date']); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
        <button type="submit" class="btn btn-success"><?= _l('save'); ?></button>
    </div>
</form>
</div>
</div>
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
    initDataTable('.table-appointments', '<?= admin_url("client/visits/") ?>' + consulted_date, [1], [1]);
});
function open_edit_modal(id) {
    $.post(admin_url + 'appointments/get', {id: id}, function(resp) {
        $('#edit_id').val(resp.id);
        $('#edit_visit_id').val(resp.visit_id);
        $('#edit_mr_no').val(resp.mr_no);
        $('#edit_patient_name').val(resp.patient_name);
        $('#edit_patient_mobile').val(resp.patient_mobile);
        $('#edit_appointment_date').val(resp.appointment_date);
        $('#editAppointmentModal').modal('show');
    }, 'json');
}

function delete_appointment(url){
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
function confirmBooking(id) {
    if (confirm("Are you sure you want to confirm this visit?")) {
        $.post("<?= site_url('client/confirm_booking'); ?>", { id: id }, function(response) {
            if (response.success) {
                alert_float("success", response.message || "Visit confirmed.");
                setTimeout(function() {
                    location.reload();
                }, 1000); // Wait 1.5 seconds to let user see the message
            } else {
                alert_float("danger", response.message || "Failed to confirm Visit.");
            }
        }, 'json');
    }
}


</script>


</body>
</html>
