<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div role="tabpanel" class="tab-pane" id="call_log">
    <button class="btn btn-primary btn-sm" onclick="toggleCallLogForm()" style="float: right; margin-top: -15px;">+ Add Call</button>
    
    <div>
        <!-- Hidden Form -->
        <div id="call-log-form" style="display: none">
            <br><br>
            <?= form_open(admin_url('lead_call_log/add_lead_call_log/' . $lead->id), [
    'id' => 'lead-call-log-form',
    'enctype' => 'multipart/form-data'
]); ?>

                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="leads_id" value="<?= $lead->id; ?>">
				
                <div class="row">
                    <div class="col-md-4">
						<?php
						// Determine branch_id to use in the dropdown
						/* if (isset($lead->branch_id)) {
							$branch_id = $lead->branch_id;

							// Filter $branches to include only the branch matching lead->branch_id
							$branches = array_filter($branches, function($branch) use ($branch_id) {
								return $branch['id'] == $branch_id;
							});
						} elseif (!empty($lead_call_log)) {
							$branch_ids = array_column($lead_call_log, 'branch_id');
							$branch_id = max($branch_ids);
						} else {
							$branch_id = ''; // or null
						} */
						?>

						<?= render_select('branch_id', $branches, ['id', 'name'], _l('branch') . '*', '', [
							'data-none-selected-text' => _l('dropdown_non_selected_tex'),
							'required' => 'required'
						]) ?>
					</div>

					<?php
					// Convert stored CSV options into an array for JS
					$enabled_responses = isset($patient_response_setting->options)
						? array_map('trim', explode(',', $patient_response_setting->options))
						: [];
						
					?>
					<div class="col-md-4">
    <label for="patient_response_id">Lead Response</label>
    <select class="form-control patient_response_id" name="patient_response_id" id="patient_response_id_1" required>
        <option value="">Select Lead Response</option>
        <?php
        $allowed_status_names = ['Enquiry', 'No Response', 'On Appointment', 'Paid Appointment', 'Call back'];
        $allowed_status_names_lower = array_map('strtolower', $allowed_status_names);

        foreach ($statuses as $status) {
            if (in_array(strtolower($status['name']), $allowed_status_names_lower)) {
                ?>
                <option value="<?= $status['id'] ?>" data-name="<?= strtolower($status['name']) ?>"><?= $status['name'] ?></option>
                <?php
            }
        }
        ?>
    </select>
</div>

<div class="col-md-4" id="Followup">
    <label>
        <span id="followup_required_indicator" style="color:red; display: none;">*</span> Follow-up Date & Time
    </label>
	<?php
	$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
	?>
    <input type="datetime-local" name="followup_date" class="form-control" id="followup_date" min="<?php echo $now; ?>">
</div>

<script>
$(document).ready(function () {
    function toggleFollowupRequired() {
		$('#Followup').show();
        var selectedText = $('#patient_response_id_1 option:selected').text().toLowerCase().trim();
        if (selectedText === 'call back') {
            $('#followup_date').attr('required', true);
            $('#followup_required_indicator').show();
        } else {
            $('#Followup').hide();
            $('#followup_date').removeAttr('required');
            $('#followup_required_indicator').hide();
        }
    }

    $('#patient_response_id_1').on('change', toggleFollowupRequired);

    // Run once on load in case form is prefilled
    toggleFollowupRequired();
});
</script>


					</div>
					<div class="row" style="padding: 10px;">
					  <style>
.lead_with_doctor_section.hide,
.appointment_payment_section.hide {
    display: none;
}

</style>

<div class="lead_with_doctor_section" style="margin-top:-10px;">
	<div class="row" style="padding: 10px;">
  <!-- Doctor -->
  <div class="col-md-4">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('doctor'); ?></label>
      <select class="form-control selectpicker" name="doctor_id" id="doctor_id_1" data-live-search="true">
        <option value=""></option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?= $doc['staffid']; ?>"><?= $doc['firstname'] . ' ' . $doc['lastname']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Appointment Date -->
 <div class="col-md-4">
  <div class="form-group">
  <label class="form-label"><span style="color: #f00">*</span> <?= _l('appointment_date') ?></label>
     <?php
	$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
	?>
	<input type="datetime-local" class="form-control" name="appointment_date" value="<?php echo $now; ?>" min="<?php echo $now; ?>" >
  </div>
</div>



  <!-- Treatment -->
 <div class="col-md-4">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('treatment'); ?></label>
      <select name="treatment_id" class="form-control selectpicker" data-live-search="true" id="treatment_id_1">
        <option value=""></option>
        <?php foreach ($items as $group_id => $_items) {
          if (isset($_items[0]['group_name']) && $_items[0]['group_name'] == 'Package') {
            foreach ($_items as $item) { ?>
              <option value="<?= e($item['id']); ?>"><?= e($item['description']); ?></option>
            <?php }
          }
        } ?>
      </select>
    </div>
  </div>
  
   <div class="col-md-4">
	  <div class="form-group">
	  <label><span style="color: #f00">*</span> <?= _l('appointment_type'); ?></label>
		  <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true" >
			<option value=""></option>
			<?php foreach ($appointment_type as $app): ?>
			  <option value="<?= $app['appointment_type_id']; ?>"><?= $app['appointment_type_name']; ?></option>
			<?php endforeach; ?>
		  </select>
	  </div>
	</div>

  <!-- Consultation Fee -->
  <div class="col-md-4">
  <div class="form-group">
	<label><span style="color: #f00">*</span> <?= _l('consultation_fees'); ?></label>
	<?php
	$has_consultation_fee = false;
	foreach ($items as $_group_items) {
	  if (isset($_group_items[0]['group_name']) && $_group_items[0]['group_name'] == "Consultation Fee") {
		$has_consultation_fee = true;
		break;
	  }
	}
	?>
	<select name="item_select" class="form-control selectpicker" data-live-search="true" id="consultation_fee_id_1">
	  <option value=""></option>
	  <?php foreach ($items as $group_id => $_items) {
		$group_name = $_items[0]['group_name'] ?? '';
		if ($has_consultation_fee && $group_name != "Consultation Fee") {
		  continue;
		} ?>
		<optgroup data-group-id="<?= e($group_id); ?>" label="<?= $group_name; ?>">
		  <?php foreach ($_items as $item) { ?>
			<option value="<?= e($item['rate']); ?>"
					data-rate="<?= e($item['rate']); ?>"
					data-subtext="<?= strip_tags(mb_substr($item['long_description'], 0, 200)); ?>">
						<?= e(app_format_number($item['rate'])); ?>
			</option>
		  <?php } ?>
		</optgroup>
	  <?php } ?>
	</select>
  </div>
</div>

 <div class="appointment_payment_section">
  <!-- Payment Amount -->
  <div class="col-md-4">
    <div class="form-group">
	
	   <span id="payment_amount_required_indicator" style="color:red; display: none;">*</span>
      <label><?= _l('payment_amount'); ?></label>
      <input type="number" class="form-control" id="paying_amount_1" name="payment_amount" min="0" step="0.01"
             placeholder="<?= _l('enter_payment_amount'); ?>">
      <small class="text-danger" id="amountError" style="display: none;">
        <?= _l('amount_exceeds_item'); ?>
      </small>
    </div>
  </div>

  <!-- Attachment -->
  <div class="col-md-4">
    <div class="form-group">
      <label><?= _l('attachment_optional'); ?></label>
      <input type="file" class="form-control" id="paymentAttachment" name="attachment"
             accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
    </div>
  </div>

  <!-- Payment Mode -->
  <div class="col-md-4">
    <div class="form-group">
	 <span id="payment_mode_required_indicator" style="color:red; display: none;">*</span>
      <label><?= _l('payment_mode'); ?></label>
      <select class="selectpicker form-control" name="paymentmode" data-width="100%"
              data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
        <option value=""></option>
        <?php foreach ($payment_modes as $mode) { ?>
          <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
</div>

  
</div>

</div>
					
						<div class="col-md-4">
							<label>Comments</label>
							<textarea name="comments" class="form-control" rows="2" placeholder="<?= _l('enter_comments'); ?>"></textarea>
						</div>
						<div class="col-md-3">
							<label>&nbsp;<br></label>
							<button type="submit" class="btn btn-success" style="margin-top: 25px;"><?= _l('submit'); ?></button>
						</div>
                    </div>
               
            <?= form_close(); ?>
        </div>

        <br>

        <!-- Table -->
        <?php if (staff_can('view_call_log', 'customers')) : ?>
			<?= render_datatable([
				_l('s_no'),
				_l('enquiry_on'),
				_l('treatment'),
				_l('lead_response'),
				_l('doctor'),
				_l('appointment_date_time'),
				_l('fee'),
				_l('payment_status'),
				_l('followup_date'),
				_l('comments'),
				_l('branch'),
				_l('enquired_by'),
			], 'lead-call-log-table'); ?>
		<?php endif; ?>

    </div>
</div>

<script>
function toggleCallLogForm() {
    const form = document.getElementById('call-log-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

$(function() {
    initDataTable('.table-lead-call-log-table', admin_url + 'lead_call_log/call_log_table/' + <?= $lead->id ?>, undefined, undefined, '', [0, 'desc']);
});

</script>

<script>
$(function () {
    function toggleFieldsByResponse() {
        const response = $('#patient_response_id_1 option:selected').text().toLowerCase().trim();
		//alert(response);
        // Common mandatory fields
        const doctor = $('#doctor_id_1');
        const appointmentDate = $('#appointment_date_1');
        const treatment = $('#treatment_id_1');
        const consultation = $('#consultation_fee_id_1');

        const paymentAmount = $('#paying_amount_1');
        const paymentMode = $('select[name="paymentmode"]');
        const paymentSection = $('.appointment_payment_section');

        // Reset required
        doctor.removeAttr('required');
        appointmentDate.removeAttr('required');
        treatment.removeAttr('required');
        consultation.removeAttr('required');
        paymentAmount.removeAttr('required');
        paymentMode.removeAttr('required');

        // Hide payment section by default
        paymentSection.hide();

        if (response === 'on appointment' || response === 'paid appointment') {
            $('.lead_with_doctor_section').removeClass('hide').slideDown();

            // Common fields
            doctor.attr('required', true);
            appointmentDate.attr('required', true);
            treatment.attr('required', true);
            consultation.attr('required', true);
        } else {
            $('.lead_with_doctor_section').slideUp().addClass('hide');
        }

        if (response === 'paid appointment') {
            paymentSection.show();
            paymentAmount.attr('required', true);
            paymentMode.attr('required', true);
			$('#payment_amount_required_indicator').show();
			$('#payment_mode_required_indicator').show();
        }
    }

    toggleFieldsByResponse();

    $('#patient_response_id_1').on('change', toggleFieldsByResponse);
});
</script>

<script>
$(document).ready(function() {
    // When consultation fee is selected
    $('#consultation_fee_id_1').change(function() {
        const selectedOption = $(this).find('option:selected');
        const feeValue = parseFloat(selectedOption.data('rate')) || 0;

        $('#paying_amount_1').val('');
        $('#amountError').hide();
    });

    // When payment amount is typed
    $('#paying_amount_1').on('input', function() {
        const selectedRate = parseFloat($('#consultation_fee_id_1 option:selected').data('rate')) || 0;
        const enteredAmount = parseFloat($(this).val()) || 0;

        if (enteredAmount > selectedRate) {
            alert_float('danger', '<?php echo _l('paying_amount_cannot_exceed_due_amount'); ?>');
            $(this).val('');
            $(this).addClass('is-invalid');
        } else {
            $('#amountError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
