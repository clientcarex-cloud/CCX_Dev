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
	<?php
	if(staff_can('view_appointments_calendar', 'customers')){
		?>
		 <a href="<?= admin_url('client/doctor_calendar_view');?>" title="Open Calendar">
			<i class="fa-solid fa-grip-vertical"></i>
		</a>
		
		<?php
	}
	?>
   
</h4>

<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-1"></div>
	<div class="col-md-4">
		<form method="post" action="<?= admin_url('client/appointments'); ?>">
		<?php
			$posted_date = $this->input->post('consulted_date');
			$default_date = date('Y-m-d'); // today's date in YYYY-MM-DD format

			$consulted_date_value = $posted_date ? $posted_date : $default_date;
			?>
			<label><?php echo _l('from_date');?></label><input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">

	
		<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
		
	</div>
	
	<div class="col-md-4">
		<?php
			$posted_date = $this->input->post('consulted_to_date');
			$default_date = date('Y-m-d'); // today's date in YYYY-MM-DD format

			$consulted_date_value = $posted_date ? $posted_date : $default_date;
			?>
			<label><?php echo _l('to_date');?></label><input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape($consulted_date_value) ?>">

		
	</div>
	
	<div class="col-md-2">
	<br>
	<input type="Submit" class="btn btn-success" name="Submit" value="Search">
	</div>
	</form>
</div>
<br>
<?php echo render_datatable([
    _l('visit_id'),
    _l('mr_no'),
    _l('patient_name'),
    _l('patient_mobile'),
    _l('appointment_date'),
	_l('consulted_date'),
    _l('status'),
    _l('action'),
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

<!-- Edit Appointment Modal -->
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

<!-- Calendar Modal -->
<div id="calendarModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg" style="max-width: 90%;">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Appointment Calendar</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="calendar"></div>
      </div>
    </div>
  </div>
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
	var consulted_date = $('#consulted_date').val();
	var consulted_to_date = $('#consulted_to_date').val();
    initDataTable('.table-appointments', '<?= admin_url("client/appointments/1/") ?>' + consulted_date + '/' + consulted_to_date, [1], [1]);
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

let calendarInitialized = false;

function initCalendar() {
    if (calendarInitialized) return;
    calendarInitialized = true;

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        dayMaxEvents: true,
        events: {
            url: '<?= admin_url('client/doctor/get_appointments_json'); ?>',
            method: 'GET',
            failure: function () {
                alert('Error fetching events');
            }
        },
        eventDidMount: function (info) {
            const event = info.event;
            const el = info.el;
			const visitStatus = parseInt(event.extendedProps.visit_status); // ensure it's an integer
			const eventDate = new Date(event.start);
			const today = new Date();

			// Remove time portion for date-only comparison
			today.setHours(0, 0, 0, 0);
			eventDate.setHours(0, 0, 0, 0);

			// Apply background color based on visit status and date
			if (visitStatus === 1) {
				el.style.backgroundColor = '#a3d9a5'; // medium green
				el.style.color = '#0b4f0b'; // darker green text
			} else if (visitStatus === 0) {
				if (eventDate < today) {
					el.style.backgroundColor = '#f5a3a3'; // medium red
					el.style.color = '#7a1f1f'; // darker red text
				} else {
					el.style.backgroundColor = '#f9d27d'; // medium orange
					el.style.color = '#6c4b00'; // darker orange text
				}
			}
            // Display patient name only
            el.innerHTML = `<div>${event.title}</div>`;

            el.style.borderRadius = '12px';
            el.style.padding = '2px 8px';
            el.style.fontWeight = '600';
            el.style.fontSize = '13px';
            
            //el.style.color = 'white';
            el.style.cursor = 'pointer';
        },
        eventClick: function (info) {
            const userid = info.event.extendedProps.userid;
            if (userid) {
                window.location.href = "<?= admin_url('client/index/') ?>" + userid;
            }
        }
    });

    calendar.render();

    // 🔧 Workaround: click 'Month' button after slight delay to trigger reflow
    setTimeout(() => {
        document.querySelector('.fc-dayGridMonth-button')?.click();
    }, 200); // adjust delay if needed
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