<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('appointments_calendar'); ?>&nbsp; <a href="<?= admin_url('client/get_patient_list#appointments-tab') ?>" 
   title="Switch to Appointments" 
   class="btn btn-sm btn-primary">
   <i class="fa-solid fa-table-list"></i>
</a>
</h4>
		
        <hr />
        <div id="calendar_new"></div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar_new');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      events: {
        url: admin_url + 'client/get_appointments_json',
        method: 'GET',
        failure: function () {
          alert('There was an error fetching appointment data.');
        }
      },
      eventDidMount: function (info) {
	  const el = info.el;
	  const visitStatus = parseInt(info.event.extendedProps.visit_status, 10);

	  // Get today's date at midnight (no time)
	  const today = new Date();
	  today.setHours(0, 0, 0, 0);

	  // Get event's start date at midnight (no time)
	  const eventDate = new Date(info.event.start);
	  eventDate.setHours(0, 0, 0, 0);

	  if (visitStatus === 1) {
		// Visit done — green background
		el.style.backgroundColor = '#c8f7c5';
		el.style.color = '#1b5e20';
	  } else if (visitStatus === 0) {
		if (eventDate < today) {
		  // Past date & visit not done — red background
		  el.style.backgroundColor = '#ffcdd2';
		  el.style.color = '#b71c1c';
		} else {
		  // Future or today date & visit not done — yellow background
		  el.style.backgroundColor = '#fff9c4';
		  el.style.color = '#f57f17';
		}
	  }

	  // Common styles
	  el.style.borderRadius = '8px';
	  el.style.padding = '4px';
	  el.style.fontSize = '13px';
	  el.style.fontWeight = '600';
	  el.style.cursor = 'pointer';
	}
,
      eventClick: function (info) {
        const userid = info.event.extendedProps.userid;
        if (userid) {
          window.location.href = admin_url + 'client/doctor_calendar_view/' + userid;
        }
      }
    });

    calendar.render();
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