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
<form method="post" action="<?= admin_url('client/reports/'.$type); ?>"> 

<div class="row align-items-end">

    <!-- Branch -->
    <div class="col-md-3">
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

    <!-- Role -->
    <div class="col-md-3">
        <?php
        $selected_roles = $this->input->post('role') ? $this->input->post('role') : [];
        
        echo render_select(
            'role[]', // multi-select
            $roles,   // assume $roles contains list of roles: id + name
            ['roleid', ['name']],
            '<span style="color:red;">*</span> '._l('staff_role'),
            $selected_roles,
            [
                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                'multiple' => true,
                'data-actions-box' => true
            ]
        );
        ?>
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

<?php
// Load statuses
$CI = &get_instance();
$this->load->model('tasks_model'); // adjust if model is different
$statuses = $this->tasks_model->get_statuses();

// Extract just the status names for datatable columns
$status_columns = array_map(function ($status) {
    return $status['name']; // e.g. "Not Started", "In Progress"
}, $statuses);

// Build the final column list
$columns = array_merge([
    _l('s_no'),
    _l('Assignee'),
	_l('branch'),
	_l('role'),
], $status_columns,
[_l('percentage')]);

echo render_datatable($columns, 'appointments');
?>


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
	
    // Get selected branches
    let roleSelect = $('select[name="role[]"]');
    let roleIds = roleSelect.val() && roleSelect.val().length > 0 ? 
                   encodeURIComponent(roleSelect.val().join(',')) : 
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
        + roleIds;

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
		let roleId = $('#role').val();
		let doctorId = $('#doctor_id').val();
        if ($.fn.DataTable.isDataTable('.table-appointments')) {
            $('.table-appointments').DataTable().ajax.url(
                '<?= admin_url("client/reports/$type/1/") ?>' + from + '/' + to + '/' + appointmentType + '/' + branchId + '/' + roleId
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