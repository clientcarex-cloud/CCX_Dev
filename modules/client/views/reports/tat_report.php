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
    _l('treatment'),
    _l('status'),
    _l('registration_date'),
    _l('registration_end_date'),
    _l('cosult_fee'),
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
	let fromDate = $('#consulted_date').val() || 'null';
	let toDate = $('#consulted_to_date').val() || 'null';
	let appointmentType = $('#appointment_type').val() || 'null';
	let branchId = $('#branch').val() || 'null';
	let doctorId = $('#doctor_id').val() || 'null';

	let url = '<?= admin_url("client/reports/$type/1/") ?>' 
		+ fromDate + '/' 
		+ toDate + '/' 
		+ appointmentType + '/' 
		+ branchId + '/' 
		+ doctorId;

	initDataTable('.table-appointments', url, [1], [1]);
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

<div class="modal fade" id="journeyLogModal" tabindex="-1" role="dialog" aria-labelledby="journeyLogLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><h4 style="display: inline-block">Patient Journey Log</h4></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="journeyLogContent" style="margin-top: -10px">
        <!-- AJAX loaded content -->
      </div>
    </div>
  </div>
</div>


<script>
function showJourneyLog(userid) {
    $.ajax({
        url: admin_url + 'client/get_journey_log/' + userid,
        type: 'GET',
        success: function(response) {
            $('#journeyLogContent').html(response);
            $('#journeyLogModal').modal('show');
        },
        error: function() {
            alert('Failed to load journey log data.');
        }
    });
}

</script>
</body>
</html>
