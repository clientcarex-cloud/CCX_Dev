<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
    .form-section {
        !border: 1px solid #ddd;
        padding: 20px;
        border-radius: 6px;
    }
    .form-heading {
        text-align: center;
        margin-bottom: 30px;
        font-size: 22px;
        font-weight: 600;
        padding-bottom: 10px;
    }
    .section-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        margin: 0px 0 10px;
		border-bottom: 1px solid #ddd;
    }
    .section-title::before,
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        margin: 0 15px;
    }
    .btn-purple {
    }
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<div class="clearfix"></div>

<div style="font-size: 20px; font-weight: bold; padding: 10px 20px; text-align: left; border-radius: 4px 4px 0 0;">
    <?php echo _l('appointment_form');?>
    <hr>
</div>

<?php
//print_r($master_data);
if($master_data){
	extract($master_data);
}

?>
<br>
<?php 
if(!$patient_data){


?>
<div class="row align-items-end">

    <!-- Country Code -->
    <div class="col-md-2"></div>
    <div class="col-md-2">
        <label class="form-label"><?= _l('country_code') ?>*</label>
        <select name="calling_code" id="calling_code" class="form-control selectpicker" data-live-search="true" required>
            <option value=""><?= _l('select_country_code'); ?></option>
            <?php foreach (get_all_countries() as $country): ?>
                <option value="<?= $country['calling_code']; ?>"
                    <?= (isset($lead) && $lead->country == $country['country_id']) ? 'selected' : (($country['short_name'] == 'India') ? 'selected' : ''); ?>>
                    +<?= $country['calling_code']; ?> (<?= $country['short_name']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Contact Number -->
    <div class="col-md-4 position-relative">
        <label class="form-label"><?= _l('contact_number') ?>*</label>
        <input type="text" class="form-control validate-phone" id="contact_number_search" name="contact_number"
               placeholder="<?= _l('enter_contact_number') ?>"
               value="<?= htmlspecialchars($patient['contact_number'] ?? '') ?>" autocomplete="off" required>
        <div id="contact_search_results" class="search-results animated fadeIn no-mtop display-block"
             style="display:none; position:absolute; z-index:999;"></div>
    </div>

    <!-- Add New Patient Button -->
    <div class="col-md-2">
        <button type="button" class="btn btn-success" id="addNewPatientBtn" style="margin-top: 26px;">
            <?= _l('add_new_patient'); ?>
        </button>
    </div>
	
	


</div>
<br>
<div id="patientNotes" class="alert alert-info" style="font-weight: 500;">
  <strong>Note:</strong>
  <ul style="margin-top: 5px; padding-left: 18px;">
    <li>1. Book new appointments or add treatments by selecting an existing patient.</li>
    <li>2. If you see many names under one number, it’s a family – each person can have their own multiple treatments.</li>
    <li>3. To add a new patient on the same number, use <strong>"Add New Patient"</strong>.</li>
  </ul>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('addNewPatientBtn').addEventListener('click', function () {
    const number = document.getElementById('contact_number_search').value.trim();

    if (!number) {
      alert_float('danger', 'Please enter a contact number.');
      return;
    }

    if (!/^\d{10}$/.test(number)) {
      alert_float('danger', 'Please enter a valid 10-digit contact number.');
      return;
    }

    // Redirect with contact number in URL as query param
    window.location.href = "<?= admin_url('client/new_patient'); ?>?contact_number=" + encodeURIComponent(number);
  });
});
</script>




<?php
}
if($patient_data){
    $patient = (array) $patient_data;
	//print_r($patient);	
	?>
        <form method="post" enctype="multipart/form-data" action="<?= admin_url('client/update_client'); ?>" id="updateClientForm">
            <input type="hidden" name="userid" value="<?php echo $patient['userid'];?>">
   
	
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />


    <div class="form-section" style="margin-top: -50px">
    <!--<div class="section-title"><?= _l('patient_information') ?></div>-->
    <?php
    $patient_inactive_fields = array_map('trim', explode(",", $patient_inactive_fields));
    $fields = [];
    // Collect only active fields for patient_name section
    if (!in_array('company', $patient_inactive_fields) || !in_array('salutation', $patient_inactive_fields) || !in_array('gender', $patient_inactive_fields)) {
        ob_start(); ?>
        <label class="form-label"><span style="color: #f00">* </span><?= _l('patient_name') ?></label>
        <div class="mb-3" style="display: flex; gap: 0;">
			<?php if (!in_array('salutation', $patient_inactive_fields)) { ?>
				<div style="flex: 1; max-width: 17%;">
					<select class="form-control" style="padding:0; margin:0" name="salutation">
						<option value="Master" <?= ($patient['salutation'] ?? '') == 'Master' ? 'selected' : '' ?>>Master</option>
						<option value="Baby" <?= ($patient['salutation'] ?? '') == 'Baby' ? 'selected' : '' ?>>Baby</option>
						<option value="Mr." <?= ($patient['salutation'] ?? '') == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
						<option value="Mrs." <?= ($patient['salutation'] ?? '') == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
						<option value="Ms." <?= ($patient['salutation'] ?? '') == 'Ms.' ? 'selected' : '' ?>>Ms.</option>
					</select>
				</div>
			<?php } ?>
			<?php if (!in_array('company', $patient_inactive_fields)) { ?>
				<div style="flex: 1; max-width: 66%;">
					<input type="text" class="form-control" name="company" placeholder="<?= _l('enter_patient_name') ?>" value="<?= htmlspecialchars($patient['company'] ?? '') ?>" required>
				</div>
			<?php } ?>
			<?php if (!in_array('gender', $patient_inactive_fields)) { ?>
				<div style="flex: 1; max-width: 23%;">
					<select class="form-control"  style="padding:0; margin:0" name="gender">
						<option value="Male" <?= ($patient['gender'] ?? '') == 'Male' ? 'selected' : '' ?>><?= _l('male') ?></option>
						<option value="Female" <?= ($patient['gender'] ?? '') == 'Female' ? 'selected' : '' ?>><?= _l('female') ?></option>
						<option value="Other" <?= ($patient['gender'] ?? '') == 'Other' ? 'selected' : '' ?>><?= _l('other') ?></option>
					</select>
				</div>
			<?php } ?>
		</div>
        <?php
        $fields[] = ob_get_clean();
    }
	?>
	
	<?php
// Get selected values as array
$selected_languages = isset($patient['default_language']) 
                      ? array_map('trim', explode(',', $patient['default_language'])) 
                      : [];
    // Map of field name => field HTML
    $fields_map = [
	
					'age_gender' => '
						<div class="form-group">
						  <label class="form-label"><span style="color: #f00">* </span>' . _l('age_dob_gender') . '</label>
						  <div class="row" style="margin-bottom: 0;">

							<div class="col-md-4" style="padding-right:2px;">
							  <select id="ageDobSelector" class="form-control" name="dob_type" required>
								<option value="age" ' . (empty($patient['dob']) ? 'selected' : '') . '>Age</option>
								<option value="dob" ' . (!empty($patient['dob']) ? 'selected' : '') . '>DOB</option>
							  </select>
							</div>

							<div class="col-md-8" style="padding-left:2px; padding-right:2px;">
							  <input type="' . (!empty($patient['dob']) ? 'date' : 'text') . '"
									 id="ageDobInput"
									 class="form-control"
									 required>
							</div>
						  </div>

						  <!-- These 2 hidden fields always carry value in final submission -->
						  <input type="hidden" name="age" id="hiddenAge" value="' . htmlspecialchars($patient['age'] ?? '') . '">
						  <input type="hidden" name="dob" id="hiddenDob" value="' . htmlspecialchars($patient['dob'] ?? '') . '">
						</div>
						',

					'area' => '<label class="form-label"><span style="color: #f00">* </span>' . _l('area') . '</label>
					<input type="text" class="form-control" name="area" placeholder="' . _l('enter_area') . '" value="' . htmlspecialchars($patient['area'] ?? '') . '" required>',
				

				'contact_number' => '
					<div class="form-group">
					  <label class="form-label"><span style="color: #f00">* </span>' . _l('contact_number') . '</label>
					  <div class="row no-gutters" style="margin-bottom: 0;">
						<div class="col-md-4" style="padding-right:2px;">
						  <select name="calling_code" id="calling_code" class="form-control selectpicker" data-live-search="true" required>
							<option value="">' . _l('select_country_code') . '</option>'
							. array_reduce(get_all_countries(), function ($options, $country) use ($lead) {
								$selected = isset($lead) && $lead->country == $country['country_id']
								  ? 'selected'
								  : ($country['short_name'] == 'India' ? 'selected' : '');
								return $options . '<option value="' . $country['calling_code'] . '" ' . $selected . '>
								  +' . $country['calling_code'] . ' (' . $country['short_name'] . ')
								</option>';
							  }, '') . '
						  </select>
						</div>

						<div class="col-md-8" style="padding-left:2px;">
						  <input type="text" class="form-control" name="contact_number" placeholder="' . _l('enter_contact_number') . '"
						  value="' . htmlspecialchars($patient['phonenumber'] ?? $contact_number) . '" required readonly>
						</div>
					  </div>
					</div>
					',
				'alt_number1' => '<label class="form-label">' . _l('alternative_number1') . '</label>
					<input type="text" class="form-control" name="alt_number1" placeholder="' . _l('enter_alt_number1') . '" value="' . htmlspecialchars($patient['alt_number1'] ?? '') . '">
					<input type="hidden" name="alt_number2" value="0">',
				'email_id' => '<label class="form-label">' . _l('email') . '</label>
					<input type="text" class="form-control" name="email_id" placeholder="' . _l('enter_email') . '" value="' . htmlspecialchars($patient['email_id'] ?? '') . '">',
				'marital_status' => render_select(
					'marital_status',
					[
						['id' => 'Single', 'name' => _l('single')],
						['id' => 'Married', 'name' => _l('married')],
						['id' => 'Divorced', 'name' => _l('divorced')],
						['id' => 'Widowed', 'name' => _l('widowed')],
					],
					['id', 'name'],
					_l('marital_status'),
					$patient['marital_status'] ?? '',
					['data-none-selected-text' => _l('dropdown_non_selected_tex')]
				),

				'language_select' => '
					<div class="form-group">
					  <label class="form-label"><span style="color: #f00">*</span> ' . _l('speaking_languages') . '</label>
					  <div class="row" style="margin-bottom: 0;">
						<div class="col-md-12" style="padding-right: 0;">
						  ' . render_select(
							'default_language[]',
							$languages,
							['languages_name', 'languages_name'],
							'',
							$selected_languages,
							[
							  'multiple' => true,
							  'data-none-selected-text' => _l('dropdown_non_selected_tex'),
							  'required' => 'required'
							],
							[],
							'',
							'form-control selectpicker'
						  ) . '
						</div>
					  </div>
					</div>
				',



				/* 'country_select' => '
				  <div class="form-group">
					<label class="form-label"><span style="color: #f00">*</span> ' . _l('country') . '</label>
					<div class="row" style="margin-bottom: 0;">
					  <div class="col-md-12" style="padding-right: 0;">
						<select name="country" id="country" class="form-control selectpicker" data-live-search="true" required>
						  <option value="">' . _l('select_country') . '</option>'
						  . implode('', array_map(function ($country) use ($lead) {
							$selected = (isset($lead) && $lead->country == $country['country_id']) || ($country['short_name'] == 'India') ? 'selected' : '';
							return '<option value="' . $country['country_id'] . '" ' . $selected . '>' . $country['short_name'] . '</option>';
						  }, get_all_countries())) .
						'</select>
					  </div>
					</div>
				  </div>
				',
				'state' => render_select(
					'state',
					$states,
					['state_id', 'state_name'],
					'<span style="color: #f00">*</span> ' . _l('state'),
					($patient['state'] ?? ''),
					[
						'data-none-selected-text' => _l('dropdown_non_selected_tex'),
						'id' => 'state',
						'required' => 'required'
					]
				),

				'city' => render_select(
					'city',
					$cities,
					['city_id', 'city_name'],
					'<span style="color: #f00">*</span> ' . _l('city'),
					($patient['city'] ?? ''),
					[
						'data-none-selected-text' => _l('dropdown_non_selected_tex'),
						'id' => 'city',
						'required' => 'required'
					]
				),

				'pincode' => render_select(
					'pincode',
					$pincodes,
					['pincode_id', 'pincode_name'],
					'<span style="color: #f00">*</span> ' . _l('pincode'),
					($patient['pincode'] ?? ''),
					[
						'data-none-selected-text' => _l('dropdown_non_selected_tex'),
						'id' => 'pincode',
						'required' => 'required'
					]
				), */

				'patient_source_id' => render_select(
					'patient_source_id',
					$patient_source,
					['id', ['name']],
					'<span style="color: #f00">*</span> '._l('patient_source'),
					!empty($patient['patient_source_id']) 
						? $patient['patient_source_id'] 
						: ($enquiry_type[0]['patient_source_id'] ?? ''),
					['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']
				),

			];

    // Add all other active fields
    foreach ($fields_map as $key => $html) {
        if (!in_array($key, $patient_inactive_fields)) {
            $fields[] = $html;
        }
    }

    // Render fields in rows of 3 columns
    echo '<div class="row">';
foreach ($fields as $i => $fieldHtml) {
    // Start new row every 3 fields
    if ($i > 0 && $i % 3 == 0) {
        echo '</div><div class="row">';
    }
    echo '<div class="col-md-4">' . $fieldHtml . '</div>';
}
echo '</div>'; // close last row
    ?>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('ageDobSelector');
    const input = document.getElementById('ageDobInput');
    const hiddenAge = document.getElementById('hiddenAge');
    const hiddenDob = document.getElementById('hiddenDob');

    // Detect initially selected type
    function initializeInput() {
      if (selector.value === 'dob') {
        input.type = 'date';
        input.value = hiddenDob.value;
        input.max = new Date().toISOString().split('T')[0];
      } else {
        input.type = 'text';
        input.value = hiddenAge.value;
        input.placeholder = 'Enter Age';
        input.removeAttribute('max');
      }
    }

    // On selector change (dob <-> age)
    selector.addEventListener('change', function () {
      // Save current input value to hidden field before switching
      if (input.type === 'date') {
        hiddenDob.value = input.value;
      } else {
        hiddenAge.value = input.value;
      }

      // Switch input type
      if (this.value === 'dob') {
        input.type = 'date';
        input.value = hiddenDob.value;
        input.max = new Date().toISOString().split('T')[0];
        input.placeholder = '';
      } else {
        input.type = 'text';
        input.value = hiddenAge.value;
        input.placeholder = 'Enter Age';
        input.removeAttribute('max');
      }
    });

    // On input change, always update hidden field
    input.addEventListener('input', function () {
      if (selector.value === 'dob') {
        hiddenDob.value = this.value;
      } else {
        hiddenAge.value = this.value;
      }
    });

    initializeInput(); // initial render
  });
</script>


    <div class="form-section">
        <div class="section-title"><?= _l('appointment_information') ?></div>
        <div class="row">
            <div class="col-md-4">
				<?= render_select(
				'groupid',
				$branch,
				['id', 'name'],
				_l('branch') . '*',
				isset($current_branch_id) ? $current_branch_id : ($patient['groupid'] ?? ''),
				['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']
			) ?>
            </div>
			<div class="col-md-4 mb-3">
      <label><span style="color: #f00">*</span> <?php echo _l('treatment'); ?></label>
      <select name="treatment_id" class="form-control selectpicker" data-live-search="true" id="itemSelect" required>
        <option value=""></option>
        <?php foreach ($items as $group_id => $_items) { ?>
          <?php if (isset($_items[0]['group_name']) && $_items[0]['group_name'] == 'Package') { ?>
            <?php foreach ($_items as $item) { ?>
              <option value="<?= e($item['id']); ?>">
                <?= e($item['description']); ?>
              </option>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      </select>
    </div>
	
            <div class="col-md-4">
                <?= render_select('assign_doctor_id', $assign_doctor, ['staffid', ['firstname', 'lastname']], '<span style="color: #f00">*</span> '._l('assign_doctor'), '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']) ?>
            </div>
            <div class="col-md-4">
                <label class="form-label"><span style="color: #f00">*</span> <?= _l('appointment_date') ?></label>
               <?php
				$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
				?>
				<input type="datetime-local" class="form-control" name="appointment_date" value="<?php echo $now; ?>" min="<?php echo $now; ?>" required>

            </div>

       
		
		
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
							<select name="item_select" class="form-control selectpicker" data-live-search="true" id="consultation_fee_id" required>
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

		<div class="col-md-4">
		  <div class="form-group">
		  <label><span style="color: #f00">*</span> <?= _l('appointment_type'); ?></label>
			  <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true" required>
				<option value=""></option>
				<?php foreach ($appointment_type as $app): ?>
				  <option value="<?= $app['appointment_type_id']; ?>"><?= $app['appointment_type_name']; ?></option>
				<?php endforeach; ?>
			  </select>
		  </div>
		</div>
		<!--<div class="col-md-4 mb-3">
			<div class="form-group">
				<label class="form-label"><?= _l('paying_amount') ?></label>
				<input type="text" class="form-control" id="paying_amount" name="paying_amount" placeholder="<?= _l('paying_amount') ?>">
				
			</div>
		</div>-->
		<div class="form-group col-md-4">
    <label class="control-label d-block"><?= _l('Amount Received / Payment Mode'); ?></label>
    <div style="display: flex; gap: 0;">
        <div style="flex: 1; max-width: 60%;">
            <input type="number" name="paying_amount" id="paying_amount" value="0" class="form-control" placeholder="<?= _l('paying_amount'); ?>">
        </div>
        <div style="flex: 1; max-width: 40%;">
            <select class="form-control selectpicker" name="paymentmode" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
			 <option value="">Select Payment Mode</option>
                <?php foreach ($payment_modes as $mode) { ?>
                    <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    
    <!-- Red due amount text below the field -->
    <small id="due_amount_display" class="text-danger font-weight-bold" style="display: none;"></small>
</div>



		<div class="col-md-4">
			<div class="form-group">
			  <label><?= _l('attachment_optional'); ?></label>
			  <input type="file" class="form-control" id="paymentAttachment" name="attachment"
					 accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
			</div>
		  </div>

		<div class="col-md-4 mb-3">
			<div class="form-group">
				<label class="form-label"><?= _l('remarks') ?></label>
				<textarea class="form-control" name="remarks" placeholder="<?= _l('enter_remarks') ?>"><?= htmlspecialchars($patient['remarks'] ?? '') ?></textarea>
			</div>
		</div>
	</div>

    </div>

    <div class="text-center mt-4">
        <button type="submit" name="Save" value="Save" class="btn btn-success" id="updateClientBtn">
            <?= _l('save') ?>
        </button>
        <!--<a href="<?= admin_url('patient') ?>" class="btn btn-white"><?= _l('cancel') ?></a>-->
    </div>
</form>
<?php } ?>

</div>
</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#updateClientForm').on('submit', function (e) {
        var form = this;

        // ✅ Check HTML5 required fields first
        if (form.checkValidity() === false) {
            return true; // let browser show validation errors
        }

        // ✅ Disable button to prevent double click
        var $btn = $('#updateClientBtn');
        $btn.prop('disabled', true).text('<?= _l("please_wait") ?>...');

        return true; // allow form submission
    });
});
</script>
<script>
$(document).ready(function () {
    let totalFee = 0;

    // When consultation fee is selected
    $('#consultation_fee_id').change(function () {
        const feeText = $(this).find('option:selected').text();
        totalFee = parseFloat(feeText.replace(/[^\d.]/g, '')) || 0;

        // Show initial due
        $('#due_amount_display').text("<?= _l('due_amount'); ?>: ₹" + totalFee.toFixed(2)).show();
        $('#paying_amount').val(0);
    });

    // On typing paying amount
    $('#paying_amount').on('input', function () {
        const paying = parseFloat($(this).val()) || 0;

        if (paying > totalFee) {
            alert_float('danger', "<?= _l('paying_amount_cannot_exceed_due_amount'); ?>");
            $(this).val('');
            $('#due_amount_display').text("<?= _l('due_amount'); ?>: ₹" + totalFee.toFixed(2)).show();
            return;
        }

        const due = totalFee - paying;
        $('#due_amount_display').text("<?= _l('due_amount'); ?>: ₹" + due.toFixed(2)).show();
    });
});
</script>


<script>
$(document).ready(function () {
    $('#contact_number_search').on('input', function () {
        let query = $(this).val();
        if (query.length >= 3) {
            $.ajax({
                url: admin_url + 'client/client/search_contact_number',
                type: 'POST',
                data: { contact: query },
                dataType: 'json',
                success: function (response) {
                    $('#contact_search_results').html(response.results).show();
                }
            });
        } else {
            $('#contact_search_results').hide();
        }
    });

    // Optional: hide dropdown on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#contact_number_search, #contact_search_results').length) {
            $('#contact_search_results').hide();
        }
    });
});

// custom-phone-validation.js
$(document).ready(function () {
    // Restrict input to numbers and enforce 10-digit length
    $(document).on('input', '.validate-phone', function () {
        let val = $(this).val().replace(/\D/g, '');
        if (val.length > 10) {
            val = val.substring(0, 10);
        }
        $(this).val(val);
    });

});
</script>
<script>
$(document).ready(function () {
  $('#state').on('change', function () {
    let stateId = $(this).val();
    if (stateId) {
      $.ajax({
        url: admin_url + 'client/get_cities_by_state/' + stateId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          let cityOptions = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';
          $.each(data, function (i, city) {
            cityOptions += '<option value="' + city.city_id + '">' + city.city_name + '</option>';
          });
          $('#city').html(cityOptions);
          $('#city').selectpicker('refresh'); // Required for Bootstrap select
        },
        error: function () {
          alert('Failed to fetch cities');
        }
      });
    } else {
      $('#city').html('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>').selectpicker('refresh');
    }
  });
});
</script>
<script>
$(document).ready(function () {
  $('#city').on('change', function () {
    let cityId = $(this).val();
    if (cityId) {
      $.ajax({
        url: admin_url + 'client/get_pincodes_by_city/' + cityId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          let pincodeOptions = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';
          $.each(data, function (i, pin) {
            pincodeOptions += '<option value="' + pin.pincode_id + '">' + pin.pincode_name + '</option>';
          });
          $('#pincode').html(pincodeOptions).selectpicker('refresh');
        },
        error: function () {
          alert('Failed to fetch pincodes');
        }
      });
    } else {
      $('#pincode').html('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>').selectpicker('refresh');
    }
  });
});
</script>

</body>
</html>
