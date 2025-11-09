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

<input type="hidden" id="selected_branch_id" value="<?= $selected_branch_id[0] ?>">
<input type="hidden" id="category" value="<?= $category ?>">
<input type="hidden" id="type" value="<?= $type ?>">
<input type="hidden" id="consulted_date" value="<?= $consulted_from_date ?>">
<input type="hidden" id="consulted_to_date" value="<?= $consulted_to_date ?>">
<input type="hidden" id="doctor_id" value="<?= $doctor_id ?>">
<input type="hidden" id="appointment_type" value="<?= $appointment_type ?>">

<hr class="hr-panel-heading" />
<div class="clearfix"></div>

<?php echo render_datatable([
    _l('patient'),
    _l('mr_no'),
    _l('phonenumber'),
    _l('source'),
    _l('treatment'), 
	_l('created_date'),
    _l('appointment_date'),
    _l('visited_date'),
    _l('consulted_date'),
    _l('registered_date'),
    _l('registered_end_date'),
    _l('package'),
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

<!-- FullCalendar CSS & JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
$(function(){
    function loadAppointmentsTable() {
        var consulted_date     = $('#consulted_date').val();
        var consulted_to_date  = $('#consulted_to_date').val();

        // Extra parameters from hidden fields
        var selected_branch_id = $('#selected_branch_id').val();
        var category           = $('#category').val();
        var type               = $('#type').val();
        var doctor_id          = $('#doctor_id').val();
        var appointment_type   = $('#appointment_type').val();
		
        var baseUrl = '<?= admin_url("client/reports") ?>';

        var ajaxUrl = `${baseUrl}/${type}/1/${consulted_date}/${consulted_to_date}/NULL/${selected_branch_id}/NULL/${category}`;

        if ($.fn.DataTable.isDataTable('.table-appointments')) {
            $('.table-appointments').DataTable().ajax.url(ajaxUrl).load();
        } else {
            initDataTable('.table-appointments', ajaxUrl, [1], [1]);
        }
    }

    // Load on page load
    loadAppointmentsTable();

    // Search button click
    $('#searchAppointmentsBtn').on('click', function () {
        loadAppointmentsTable();
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
