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
<input type="hidden" id="selected_branch_id" value="<?= $selected_branch_id[0] ?>">
<input type="hidden" id="category" value="<?= $category ?>">
<input type="hidden" id="type" value="<?= $type ?>">
<input type="hidden" id="consulted_date" value="<?= $consulted_from_date ?>">
<input type="hidden" id="consulted_to_date" value="<?= $consulted_to_date ?>">
<input type="hidden" id="doctor_id" value="<?= $doctor_id[0] ?>">
<input type="hidden" id="appointment_type" value="<?= $appointment_type ?>">
<input type="hidden" id="lead_sourceId" value="<?= $lead_sourceId ?>">
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
], 'appointments'); ?>




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
    let lead_sourceId = $('#lead_sourceId').val() || 'null';
    let category = $('#category').val() || 'null';
    let doctor_id = $('#doctor_id').val() || 'null';
    
    // Get selected branches
    var selected_branch_id = $('#selected_branch_id').val();
	
	// Get selected branches
    let lead_sourceSelect = $('select[name="lead_source[]"]');
    let lead_sourceIds = lead_sourceSelect.val() && lead_sourceSelect.val().length > 0 ? 
                   encodeURIComponent(lead_sourceSelect.val().join(',')) : 
                   'null';
    
    let url = '<?= admin_url("client/reports/$type/1/") ?>' 
        + encodeURIComponent(fromDate) + '/' 
        + encodeURIComponent(toDate) + '/' 
        + encodeURIComponent(appointmentType) + '/' 
		+ selected_branch_id + '/' 
		+ doctor_id + '/'
		+ category + '/'
		+ lead_sourceId;

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