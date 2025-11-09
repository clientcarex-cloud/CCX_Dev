<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div role="tabpanel" class="tab-pane" id="appointment">
   
    
    <div>
       
        <!-- Table -->
       <?php if (staff_can('view_call_log', 'customers')) : ?>
			<?= render_datatable([
				_l('s_no'),                         // 1. Serial No
				_l('Fee'),                         // 2. Item
				_l('paid_amount'),                  // 3. Paid Amount
				_l('due_amount'),                   // 4. Due
				_l('payment_status'),                   // 4. Due
				_l('payment_mode'),                 // 6. Payment Mode
				_l('reference_image'),              // 7. Reference Image
				_l('appointment_type'),              // 7. Reference Image
				_l('appointment_date_time'),        // 8. Appointment date & time
				_l('remarks'),                      // 9. Remarks
				//_l('save_and_send'),                // 10. Save & Send (Action)
				//_l('reschedule'),                   // 11. Reschedule (Action)
				_l('patient_status'),
				_l('action'),                 // 12. More (dropdown with: No feedback, Mark as Lost, Mark as Prospect)
			], 'appointment-table'); ?>
		<?php endif; ?>


    </div>
</div>

<script>
function toggleCallLogForm() {
    const form = document.getElementById('call-log-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

$(function() {
    initDataTable('.table-appointment-table', admin_url + 'lead_call_log/appointment_table/' + <?= $lead->id ?>, undefined, undefined, '', [0, 'desc']);
});

</script>

