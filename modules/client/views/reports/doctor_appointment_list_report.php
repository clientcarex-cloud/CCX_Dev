<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    #calendar .fc-event {
        border: none;
        font-weight: 500;
    }
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
    <?= _l($title); ?>&emsp;
	

</h4>

<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<form method="post" action="<?= admin_url('client/reports/doctor_appointment_list_report'); ?>"> 
<div class="row align-items-end">

    <!-- Branch -->
    <div class="col-md-2">
		<?php
		$selected_branches = $this->input->post('branch') ? $this->input->post('branch') : (isset($branch_id) ? [$branch_id] : []);
		
		echo render_select(
			'branch[]', // Note the [] for multi-select
			$branch,
			['id', ['name']],
			'<span style="color:red;">*</span> '._l('lead_branch'),
			$selected_branches,
			[
				'data-none-selected-text' => _l('dropdown_non_selected_tex'),
				'multiple' => true,
				'data-actions-box' => true,
				'required' => 'required'
			]
		);
		?>
	</div>

    <!-- Doctor -->
    <div class="col-md-2">
       <?php
    $selected_doctor = $this->input->post('doctor_id') ?? [];
    echo render_select(
        'doctor_id[]', // Note the [] for multiple selection
        $doctors,
        ['staffid', ['firstname', 'lastname']],
        _l('doctor'),
        $selected_doctor,
        [
            'multiple' => true,
            'data-actions-box' => true, // enables Select All / Deselect All
            'data-none-selected-text' => _l('dropdown_non_selected_tex')
        ]
    );
?>

    </div>

    <!-- Appointment Type -->
    <div class="col-md-2">
        <?php
            $selected_type = $this->input->post('appointment_type') ?? '';
            echo render_select(
                'appointment_type',
                $appointment_type,
                ['appointment_type_id', ['appointment_type_name']],
                _l('appointment_type'),
                $selected_type,
                ['data-none-selected-text' => _l('dropdown_non_selected_tex')]
            );
        ?>
    </div>

    <!-- From Date -->
    <div class="col-md-2">
        <?php
            $posted_date = $this->input->post('consulted_date');
            $default_date = date('Y-m-d');
            $consulted_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_date" class="control-label"><?= _l('from_date'); ?></label>
        <input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">
    </div>

    <!-- To Date -->
    <div class="col-md-2">
        <?php
            $posted_date = $this->input->post('consulted_to_date');
            $consulted_to_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_to_date" class="control-label"><?= _l('to_date'); ?></label>
        <input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape($consulted_to_date_value) ?>">
    </div>

    <!-- Submit -->
    <div class="col-md-2">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
        <br>
        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 5px;"><?= _l('search'); ?></button>
    </div>

</div>
</form>

<br>
<?= render_datatable([
	_l('visit_id'),
	_l('mr_no'),
	_l('patient_name'),
	_l('patient_mobile'),
	_l('appointment_date'),
	_l('consulted_date'),
	_l('treatment'),
	_l('appointment_type'),
	_l('enquiry_type'),
	_l('consultation_fee'),
	//_l('status'),
	//_l('action'),
], 'appointments'); ?>

</div>

</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<!-- FullCalendar CSS & JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
$(function(){
    // Initialize select picker
    $('.selectpicker').selectpicker();
    
    // Initialize the table
    initAppointmentsTable();
    
    // Reinitialize table when search is clicked
    $('form').on('submit', function(e) {
        e.preventDefault();
        initAppointmentsTable();
    });
});

function initAppointmentsTable() {
    let fromDate = $('#consulted_date').val() || 'null';
    let toDate = $('#consulted_to_date').val() || 'null';
    let appointmentType = $('#appointment_type').val() || 'null';
    
    // Get selected branches
    let branchSelect = $('select[name="branch[]"]');
    let branchIds = branchSelect.val() && branchSelect.val().length > 0 ? 
                   encodeURIComponent(branchSelect.val().join(',')) : 
                   'null';
	
	let doctorSelect = $('select[name="doctor_id[]"]');			   
	let doctorIds = doctorSelect.val() && doctorSelect.val().length > 0 ? 
		   encodeURIComponent(doctorSelect.val().join(',')) : 
		   'null';
    
    let url = '<?= admin_url("client/reports/$type/1/") ?>' 
        + encodeURIComponent(fromDate) + '/' 
        + encodeURIComponent(toDate) + '/' 
        + encodeURIComponent(appointmentType) + '/' 
        + branchIds + '/' 
        + doctorIds;

    console.log('Request URL:', url); // Debugging line

    // Destroy existing table if it exists
    if ($.fn.DataTable.isDataTable('.table-appointments')) {
        $('.table-appointments').DataTable().destroy();
    }
    
    // Initialize new table
    initDataTable('.table-appointments', url, [1], [1]);
}
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
        if ($.fn.DataTable.isDataTable('.table-appointments')) {
            $('.table-appointments').DataTable().ajax.url(
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