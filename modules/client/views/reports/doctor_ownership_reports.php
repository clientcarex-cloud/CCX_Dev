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
<form method="post" action="<?= admin_url('client/reports/doctor_ownership_reports'); ?>">
<div class="row align-items-end"> <!-- align-items-end for vertical alignment of labels and inputs -->

    <div class="col-md-3">
        <?php
			$selected_branch = $this->input->post('branch') ?? [];
			echo render_select(
				'branch[]', // <-- make it an array
				$branch,
				['id', ['name']],
				'<span style="color:red;">*</span> '._l('lead_branch'),
				$selected_branch,
				[
					'multiple' => true,
					'data-actions-box' => true,
					'data-none-selected-text' => _l('dropdown_non_selected_tex'),
					'required' => 'required'
				]
			);

			?>
    </div>

    <div class="col-md-3">
        <?php
            $posted_date = $this->input->post('consulted_date');
            $default_date = date('Y-m-d'); // today's date
            $consulted_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_date" class="control-label"><?= _l('from_date'); ?></label>
        <input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">
    </div>

    <div class="col-md-3">
        <?php
            $posted_date = $this->input->post('consulted_to_date');
            $consulted_to_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_to_date" class="control-label"><?= _l('to_date'); ?></label>
        <input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape($consulted_to_date_value) ?>">
    </div>

    <div class="col-md-2">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
		<br>
        <button type="submit" class="btn btn-success" style="width: 100%;margin-top: 5px"><?= _l('search'); ?></button>
    </div>

</div>
</form>

<br>



<?php echo render_datatable([
    _l('doctor'),
    _l('appointments'),
     _l('visits'),
     _l('registrations'),
    _l('total_package_amount'),
    _l('total_paid_amount'),
    _l('total_due_amount'),
    /*_l('np_paid_amount'),
    _l('cpot_paid_amount'),
    _l('cpot_due_amount'),*/
    _l('missed_consultation'),
   _l('missed_registrations'),
], 'doctor_ownership_reports'); ?>




</div>



</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>


<script>
$(function(){
	let fromDate = $('#consulted_date').val() || 'null';
	let toDate = $('#consulted_to_date').val() || 'null';
	let appointmentType = $('#appointment_type').val() || 'null';
	let branchSelect = $('select[name="branch[]"]');
let branchIds = branchSelect.val() && branchSelect.val().length > 0 ?
                encodeURIComponent(branchSelect.val().join(',')) :
                'null';

	let doctorId = $('#doctor_id').val() || 'null';

	let url = '<?= admin_url("client/reports/$type/1/") ?>'
    + fromDate + '/'
    + toDate + '/'
    + appointmentType + '/'
    + branchIds + '/' // <-- updated
    + doctorId;


	initDataTable('.table-doctor_ownership_reports', url, [1], [1]);
});
</script>



<script>
$(function () {

    
    // Search Button Click
    $('#searchAppointmentsBtn').on('click', function () {
        let from = $('#consulted_date').val();
        let to = $('#consulted_to_date').val();
		let appointmentType = $('#appointment_type').val(); // use correct ID
		let branchId = $('#branch').val();
		let doctorId = $('#doctor_id').val();
        if ($.fn.DataTable.isDataTable('.table-doctor_ownership_reports')) {
            $('.table-doctor_ownership_reports').DataTable().ajax.url(
                '<?= admin_url("client/reports/$type/1/") ?>' + from + '/' + to + '/' + appointmentType + '/' + branchId + '/' + doctorId
            ).load();
        }
    });
});
</script>
<?php if (isset($client_modal)) echo $client_modal; ?>

<script>
$(function () {
    <?php if (isset($clientid) && $clientid): ?>
        $('#client-model-auto').modal({
			backdrop: 'static',  // disables click outside to close
			keyboard: false      // disables ESC key to close
		});
    <?php endif; ?>
});
</script>



</body>
</html>
