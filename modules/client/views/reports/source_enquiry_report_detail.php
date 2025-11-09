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
<?php
/*
?>
<form method="post" action="<?= admin_url('client/reports/'.$type); ?>"> 
<div class="row align-items-end">

    <div class="col-md-3">
    <?php
    // First priority: posted value (may be array)
    $selected_branch = $this->input->post('branch') ?? null;

    if (!$selected_branch && isset($branch_id)) {
        // Fallback: passed value (force array if single)
        $selected_branch = is_array($branch_id) ? $branch_id : [$branch_id];
    }

    echo render_select(
        'branch[]', // <-- important: array name
        $branch,
        ['id', ['name']],
        '<span style="color:red;">*</span> '._l('lead_branch'),
        $selected_branch,
        [
            'data-none-selected-text' => _l('dropdown_non_selected_tex'),
            'required' => 'required',
            'multiple' => true // <-- multi select
        ]
    );
    ?>
</div>

	<div class="col-md-3">
    <?php
    $selected_sources = set_value('lead_source'); // Posted value (array if multiple)
    if (!$selected_sources && isset($lead_source_id)) {
        $selected_sources = $lead_source_id; // Fallback: passed value (can be array)
    }

    echo render_select(
        'lead_source[]', // [] is needed for multiple
        $leads_sources,
        ['id', 'name'],
        _l('lead_source'),
        $selected_sources,
        [
            'data-none-selected-text' => _l('dropdown_non_selected_tex'),
            'multiple' => true,
            'data-actions-box' => true // "Select All" option (Bootstrap Select feature)
        ],
        [],
        '',
        '',
        false
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
<?php
*/
?>
<input type="hidden" id="selected_branch_id" value="<?= $selected_branch_id[0] ?>">
<input type="hidden" id="category" value="<?= $category ?>">
<input type="hidden" id="type" value="<?= $type ?>">
<input type="hidden" id="consulted_date" value="<?= $consulted_from_date ?>">
<input type="hidden" id="consulted_to_date" value="<?= $consulted_to_date ?>">
<input type="hidden" id="doctor_id" value="<?= $doctor_id ?>">
<input type="hidden" id="appointment_type" value="<?= $appointment_type ?>">
<input type="hidden" id="lead_sourceId" value="<?= $lead_sourceId ?>">
<?php echo render_datatable([
    _l('patient'),
    _l('mr_no'),
    _l('source'),
    _l('treatment'),
    _l('created_date'),
    _l('appointment_date'),
    _l('visited_date'),
    _l('consulted_date'),
    _l('doctor'),
    _l('registered_date'),
    _l('comments'),
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
		+ 'NULL' + '/'
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
