<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>


<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<?php

$case = $case[0];
$patient_name = '';

if (!empty($case['company'])) {
	$patient_name = $case['company'];
} elseif (is_object($client ?? null) && !empty($client->company)) {
	$patient_name = $client->company;
} elseif (is_array($client ?? null) && !empty($client['company'])) {
	$patient_name = $client['company'];
}

if ($patient_name !== '') {
	echo '<div class="casesheet-patient-name" style="margin-bottom: 10px;"><strong>' . _l('patient_name') . ':</strong> ' . htmlspecialchars($patient_name, ENT_QUOTES, 'UTF-8') . '</div>';
}

//if (staff_cant('edit_limit_casesheet', 'customers')) {
	?>
	<?php
				$color = '#28a745'; // green or any dynamic color
				$bg = adjust_hex_brightness($color, 0.04);
				$border = adjust_hex_brightness($color, 0.4);

				echo '<label id="consultation_timer_container" style="display: none; margin-left: 10px;">
				  <span class="label label-default" 
						style="color:' . $color . ';
							   border:1px solid ' . $border . ';
							   background:' . $bg . ';
							   padding: 4px 8px;
							   border-radius: 4px;">
					Consultation Duration:&nbsp;<span id="consultation_timer"> 00:00</span>
				  </span>
				</label>';
				?>
	<form id="casesheetForm">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" id="csrf_token">
  <input type="hidden" name="patientid" value="<?= $client->userid; ?>">
 <input type="hidden" name="casesheet_id" id="record_id" value="">
 <input type="hidden" name="consultation_duration" id="consultation_duration" value="">


  <!-- Accordion Tabs -->
  <div class="accordion" id="casesheetAccordion">

    <!-- Preliminary Data Tab -->
    <div class="card">
        <h4>
          
            <strong><?php echo _l('preliminary_data'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
			<div id="treatment_rows">
    <!-- Initial Row -->
    <div class="row treatment-row align-items-end mb-3">
        <div class="col-md-3">
            <?php
			$flat_items = [];

			foreach ($items as $group) {
				foreach ($group as $item) {
					if (isset($item['group_name']) && $item['group_name'] == "Consultation Fee") {
						continue; // Skip Consultation Fee
					}
					$flat_items[] = $item;
				}
			}

			$last_appointment = end($appointment_data);
			
			if($casesheet_data['treatment_type_id']){
				$selected = isset($casesheet_data['treatment_type_id']) ? $casesheet_data['treatment_type_id'] : '';
			}else{
				$selected = isset($last_appointment['treatment_id']) ? $last_appointment['treatment_id'] : '';
			}
			

			echo render_select(
				'description',
				$flat_items,
				['id', 'description'],
				'treatment_type',
				$selected,
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex'),
					//'disabled' => 'disabled',
					'required' => 'required'
				]
			);

			// Add hidden input with actual name used in backend
			echo '<input type="hidden" name="treatment_type[]" value="' . htmlspecialchars($selected) . '">';
			?>


        </div>

       <div class="col-md-2">
		<label><?php echo _l('duration_months');?></label>
		
				
				<?php
$duration_value = '';
if (isset($casesheet_data['duration_value']) && $casesheet_data['duration_value'] !== '') {
    $duration_value = $casesheet_data['duration_value'];
} elseif (isset($patient_treatment[0]['duration_value']) && $patient_treatment[0]['duration_value'] !== '') {
    $duration_value = $patient_treatment[0]['duration_value'];
}
?>

<input type="number" 
       name="duration_value" 
       class="form-control" 
       min="1" 
       placeholder="Number" 
       value="<?= htmlspecialchars($duration_value) ?>">

			
			
	</div>


        <!--<div class="col-md-2">
            <label><?php echo _l('improvement_percentage'); ?></label>
            <input type="number" name="improvement" class="form-control improvement-input" value="0" min="0" max="100" placeholder="Enter Percentage" value="<?= isset($casesheet_data['improvement']) ? htmlspecialchars($casesheet_data['improvement']) : '' ?>" >
        </div>

        <div class="col-md-4">
            <label><?php echo _l('overall_progress'); ?></label>
            <div class="progress">
			
                <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>-->
		<div class="col-md-4">
		<?PHP
		if ($casesheet_data['suggested_diagnostics_id']) {
			$selected_diagnostic_id = isset($casesheet_data['suggested_diagnostics_id']) ? $casesheet_data['suggested_diagnostics_id'] : '';
		} else {
			$selected_diagnostic_id = isset($last_appointment['suggested_diagnostics_id']) ? $last_appointment['suggested_diagnostics_id'] : '';
		}

		echo render_select(
				'suggested_diagnostics_id',
				$suggested_diagnostics, // This should be your array of diagnostic options
				['suggested_diagnostics_id', 'suggested_diagnostics_name'],
				'Suggested Diagnostics',
				$selected_diagnostic_id, // selected value
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex')
				]
			);

		?>
</div>
<div class="col-md-3">
	<label>Status</label>
	<select name="treatment_status" class="form-control" style="padding: 1px" required>
		<option value="Other">Other</option>
		<option value="Better">Better</option>
		<option value="Not Better">Not Better</option>
		<option value="First Consultation">First Consultation</option>
		<option value="SQ">SQ</option>
	</select>
		
	</div>
        <!--<div class="col-md-1">
		<br>
            <button type="button" class="btn btn-success add-row"><i class="fa fa-plus"></i></button>
        </div>-->
    </div>
</div>
		<div class="row">
			
            <div class="col-md-4">
              <label for="medicine_days"><?php echo _l('medicine_days'); ?><!-- <span class="text-danger">*</span> --></label>
              <input type="number" name="medicine_days" id="medicine_days" value="<?php echo $case['medicine_days'];?>" class="form-control" min="1" <!-- required --> >
            </div>
			<div class="col-md-4">
              <label for="followup_date"><?php echo _l('followup_date'); ?></label>
              <input type="date" name="followup_date" id="followup_date"  value="<?php echo $case['followup_date'];?>"  class="form-control">
            </div>
            <div class="col-md-4">
			  
			  <?php
			$selected = isset($case['patient_status']) ? $case['patient_status'] : '';
			echo render_select(
				'patient_status',
				$patient_status,
				['patient_status_id', 'patient_status_name'],
				_l('patient_status'),
				$selected,
				['data-none-selected-text' => _l('dropdown_non_selected_tex')]
			);
			?>
            </div>
			<div class="col-md-4">
              <label for="documents"><?php echo _l('documents'); ?></label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
            </div>
			
			<div class="col-md-8">
             <?php
				$documents = [];

				// Loop through each entry and extract non-empty 'documents'
				foreach ($prev_documents as $entry) {
					if (!empty($entry['documents'])) {
						$decoded = json_decode($entry['documents'], true);
						if (is_array($decoded)) {
							$documents = array_merge($documents, $decoded);
						}
					}
				}
			?>
			<br>
			<h4>Documents</h4>
			<table class="table table-bordered">
			
			  <thead>
				<tr>
				  <th>#</th>
				  <th>File Name</th>
				  <th>Type</th>
				  <th>Action</th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach ($documents as $index => $file): ?>
				  <tr>
					<td><?= $index + 1 ?></td>
					<td><?= basename($file) ?></td>
					<td><?= pathinfo($file, PATHINFO_EXTENSION) ?></td>
					<td>
					  <a href="<?= base_url($file) ?>" target="_blank" class="btn btn-sm btn-primary">
						View
					  </a>
					</td>
				  </tr>
				<?php endforeach; ?>
			  </tbody>
			</table>
            </div>
			
			
			
			
		</div>
			
		<!-- Presenting Complaints -->
		<div class="form-group mtop20">
			<label for="presenting_complaints" class="control-label">
				<?php echo _l('presenting_complaints'); ?>
			</label>
			<textarea id="presenting_complaints" name="presenting_complaints" class="form-control tinymce" rows="6"><?php echo $case['presenting_complaints'];?></textarea>
		</div>
		
		
		<!--Complaints -->
		<div class="form-group mtop20">
			<label for="complaint" class="control-label">
				<?php echo _l('complaints'); ?>
			</label>
			<textarea id="complaint" name="complaint" class="form-control tinymce" rows="6"><?php echo $case['complaint'];?></textarea>
		</div>
        </div>
     
    </div>

    <!-- Clinical Observation Tab -->
    <div class="card">
	
	   <h4>
	   <br>
          <strong><?php echo _l('clinical_observation'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
          <!-- Clinical Observation Content -->
          <div class="row mtop10">
           
            <div class="col-md-12">
              <label for="clinical_observation"><?php echo _l('clinical_observation'); ?></label>
              <textarea name="clinical_observation" id="clinical_observation" class="form-control tinymce" rows="6"><?php echo $case['clinical_observation'];?></textarea>
            </div>
          </div>

        </div>
      
    </div>

    <!-- Personal History Tab -->
    <div class="card">
	
	  <h4>
	  <br>
          <strong><?php echo _l('personal_history'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
          <!-- Personal History Content -->
					  <div class="row">
			  <!-- Row 1 -->
			  <div class="col-md-4 mb-3">
				<label><?php echo _l('appetite'); ?>:</label>
				<input type="text" name="appetite" class="form-control" value="<?php echo $case['appetite'];?>"  placeholder="<?php echo _l('appetite'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
				<label><?php echo _l('thirst'); ?>:</label>
				<input type="text" name="thirst" class="form-control" value="<?php echo $case['thirst'];?>"  placeholder="<?php echo _l('thirst'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
				<label><?php echo _l('desires'); ?>:</label>
				<input type="text" name="desires" class="form-control" value="<?php echo $case['desires'];?>"  placeholder="<?php echo _l('desires'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
				<label><?php echo _l('aversion'); ?>:</label>
				<input type="text" name="aversion" class="form-control" value="<?php echo $case['aversion'];?>"  placeholder="<?php echo _l('aversion'); ?>">
			  </div>

			  <!-- Row 2 -->
			  <div class="col-md-4 mb-3">
				<br>
				<label><?php echo _l('tongue'); ?>:</label>
				<input type="text" name="tongue" class="form-control" value="<?php echo $case['tongue'];?>"  placeholder="<?php echo _l('tongue'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('urine'); ?>:</label>
				<input type="text" name="urine" class="form-control" value="<?php echo $case['urine'];?>"  placeholder="<?php echo _l('urine'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('bowels'); ?>:</label>
				<input type="text" name="bowels" class="form-control" value="<?php echo $case['bowels'];?>"  placeholder="<?php echo _l('bowels'); ?>">
			  </div>

			  <!-- Row 3 -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('sweat'); ?>:</label>
				<input type="text" name="sweat" class="form-control" value="<?php echo $case['sweat'];?>"  placeholder="<?php echo _l('sweat'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('sleep'); ?>:</label>
				<input type="text" name="sleep" class="form-control" value="<?php echo $case['sleep'];?>"  placeholder="<?php echo _l('sleep'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('sun_headache'); ?>:</label>
				<input type="text" name="sun_headache" class="form-control" value="<?php echo $case['sun_headache'];?>"  placeholder="<?php echo _l('sun_headache'); ?>">
			  </div>

			  <!-- Row 4 -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('thermals'); ?>:</label>
				<input type="text" name="thermals" class="form-control" value="<?php echo $case['thermals'];?>"  placeholder="<?php echo _l('thermals'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('habits'); ?>:</label>
				<input type="text" name="habits" class="form-control" value="<?php echo $case['habits'];?>"  placeholder="<?php echo _l('habits'); ?>">
			  </div>

			  <!-- Row 5 -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('addiction'); ?>:</label>
				<input type="text" name="addiction" class="form-control" value="<?php echo $case['addiction'];?>"  placeholder="<?php echo _l('addiction'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('side'); ?>:</label>
				<input type="text" name="side" class="form-control" value="<?php echo $case['side'];?>"  placeholder="<?php echo _l('side'); ?>">
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('dreams'); ?>:</label>
				<textarea name="dreams" class="form-control" placeholder="<?php echo _l('dreams'); ?>"><?php echo $case['dreams'];?></textarea>
			  </div>

			  <!-- Row 6 -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('diabetes'); ?>:</label>
				<textarea name="diabetes" class="form-control" placeholder="<?php echo _l('diabetes'); ?>"><?php echo $case['diabetes'];?></textarea>
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('thyroid'); ?>:</label>
				<textarea name="thyroid" class="form-control" placeholder="<?php echo _l('thyroid'); ?>"><?php echo $case['thyroid'];?></textarea>
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('hypertension'); ?>:</label>
				<textarea name="hypertension" class="form-control" placeholder="<?php echo _l('hypertension'); ?>"><?php echo $case['hypertension'];?></textarea>
			  </div>

			  <!-- Row 7 -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('hyperlipidemia'); ?>:</label>
				<textarea name="hyperlipidemia" class="form-control" placeholder="<?php echo _l('hyperlipidemia'); ?>"><?php echo $case['hyperlipidemia'];?></textarea>
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('menstrual_obstetric_history'); ?>:</label>
				<textarea name="menstrual_obstetric_history" class="form-control" placeholder="<?php echo _l('menstrual_obstetric_history'); ?>"><?php echo $case['menstrual_obstetric_history'];?></textarea>
			  </div>
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('family_history'); ?>:</label>
				<textarea name="family_history" class="form-control" placeholder="<?php echo _l('family_history'); ?>"><?php echo $case['family_history'];?></textarea>
			  </div>

			  <!-- Final Row -->
			  <div class="col-md-4 mb-3">
			  <br>
				<label><?php echo _l('past_treatment_history'); ?>:</label>
				<textarea name="past_treatment_history" class="form-control" placeholder="<?php echo _l('past_treatment_history'); ?>"><?php echo $case['past_treatment_history'];?></textarea>
			  </div>
			</div>
        </div>
    </div>

    <!-- General Examination Tab -->
    <div class="card">
	   <h4>
	   <br>
          <strong><?php echo _l('general_examination'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
          <!-- General Examination Content -->
          <div class="row">
			<div class="col-md-2">
				<label><?php echo _l('bp'); ?>:</label>
				<input type="text" name="bp" value="<?php echo $case['bp'];?>"  class="form-control" placeholder="120/80">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('pulse'); ?>:</label>
				<input type="text" name="pulse" value="<?php echo $case['pulse'];?>"  class="form-control" placeholder="Pulse">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('weight'); ?>:</label>
				<input type="text" name="weight" value="<?php echo $case['weight'];?>"  class="form-control" placeholder="WT.(KG)">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('height'); ?>:</label>
				<input type="text" name="height" value="<?php echo $case['height'];?>"  class="form-control" placeholder="HT.">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('temperature'); ?>:</label>
				<input type="text" name="temperature" value="<?php echo $case['temperature'];?>"  class="form-control" placeholder="TEMP.">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('bmi'); ?>:</label>
				<input type="text" name="bmi" value="<?php echo $case['bmi'];?>"  class="form-control" placeholder="BMI">
			</div>
		</div>


		<?php
		$fields = [
			['mental_generals', 'pg', 'particulars'],
			['miasmatic_diagnosis', 'analysis_evaluation', 'reportorial_result'],
			['management', 'diet', 'exercise'],
			['critical', 'level_of_assent', 'dos_and_donts'],
			['level_of_assurance', 'criteria_future_plan_rx', 'nutrition']
		];

		$labels = [];
		foreach (array_merge(...$fields) as $field) {
			$labels[$field] = _l($field);
		}
		?>


		<?php foreach ($fields as $row): ?>
		<div class="row mtop15">
			<?php foreach ($row as $field): ?>
				<div class="col-md-4">
					<label><?php echo _l($labels[$field]); ?>:</label>
					<?php if ($field == 'nutrition'): ?>
						<select name="nutrition" class="form-control">
							<option value=""><?php echo _l('select'); ?></option>
							<option value="normal" <?php if($case['nutrition'] == "normal"){ echo "Selected"; }?>>Normal</option>
							<option value="poor" <?php if($case['nutrition'] == "poor"){ echo "Selected"; }?>>Poor</option>
							<option value="excessive" <?php if($case['nutrition'] == "excessive"){ echo "Selected"; }?>>Excessive</option>
						</select>
					<?php else: ?>
						<textarea name="<?php echo $field; ?>" class="form-control" rows="2" placeholder="<?php echo $labels[$field]; ?>"><?php echo isset($case[$field]) ? $case[$field] : ''; ?></textarea>


					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
        </div>
    </div>

    <!-- Mind Tab -->
    <div class="card">
	
	  <h4>
	  <br>
          <strong><?php echo _l('mind'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
          <!-- Mind Content -->
          <div class="form-group mtop20">
			<label for="mind" class="control-label">
				<?php echo _l('mind'); ?>
			</label>
			<textarea id="mind" name="mind" class="form-control tinymce" rows="6"><?php echo $case['mind'];?></textarea>
		</div>
        </div>
    </div>
	
	
<div class="card">
  <h4><br><strong><?= _l('prescription'); ?></strong></h4>
  <hr>
  <div class="card-body">
    <?php
    $parsed_prescriptions = [];
    $parsed_remarks = [];

    if (!empty($prescription[0]['prescription_data'])) {
      $items = explode('|', $prescription[0]['prescription_data']);
      foreach ($items as $item) {
        $item = trim(preg_replace('/^\d+\.\s*/', '', $item));
        if ($item !== '') {
          $parts = array_filter(array_map('trim', explode(';', $item)));
          if (!empty($parts)) {
            $parsed_prescriptions[] = array_values($parts);
          }
        }
      }
    }

    if (!empty($prescription[0]['medicine_remarks'])) {
      $parsed_remarks = array_map('trim', explode('|', $prescription[0]['medicine_remarks']));
    }

    // Dropdown options
    $medicine_options = array_map(fn($m) => ['id' => $m['medicine_name'], 'name' => $m['medicine_name']], $medicines);
    $potency_options = array_map(fn($p) => ['id' => $p['medicine_potency_name'], 'name' => $p['medicine_potency_name']], $potencies);
    $dose_options = array_map(fn($d) => ['id' => $d['medicine_dose_name'], 'name' => $d['medicine_dose_name']], $doses);
    $timing_options = array_map(fn($t) => ['id' => $t['medicine_timing_name'], 'name' => $t['medicine_timing_name']], $timings);
    $remarks_options = array_map(fn($t) => ['id' => $t['medicine_timing_name'], 'name' => $t['medicine_timing_name']], $timings);
    ?>

    <table class="prescription-medicine-table table" id="prescriptionMedicineTable">
      <thead>
        <tr>
          <th><?= _l('medicine_name'); ?></th>
          <th><?= _l('potency'); ?></th>
          <th><?= _l('dose'); ?></th>
          <th><?= _l('timings'); ?></th>
          <th><?= _l('doctor_remarks'); ?></th>
          <th><?= _l('remarks'); ?></th>
          <th><?= _l('given'); ?></th>
          <th><?= _l('action'); ?></th>
        </tr>
      </thead>
      <tbody id="prescriptionMedicineBody">
        <?php /* foreach ($parsed_prescriptions as $i => $presc): ?>
        <tr>
          <td><input type="text" name="prescription_medicine_name[]" class="form-control" value="<?= htmlspecialchars($presc[0] ?? '') ?>"></td>
          <td><input type="text" name="prescription_medicine_potency[]" class="form-control" value="<?= htmlspecialchars($presc[1] ?? '') ?>"></td>
          <td><input type="text" name="prescription_medicine_dose[]" class="form-control" value="<?= htmlspecialchars($presc[2] ?? '') ?>"></td>
          <td><input type="text" name="prescription_medicine_timings[]" class="form-control" value="<?= htmlspecialchars($presc[3] ?? '') ?>"></td>
          <td>
			  <textarea name="prescription_medicine_remarks[]" class="form-control"><?= htmlspecialchars($presc[4] ?? '') ?></textarea>
			</td>
			<td>
			  <textarea  class="form-control prescription-medicine-remarks" readonly><?= htmlspecialchars($parsed_remarks[$i] ?? '') ?></textarea>
			</td>

          <td class="text-center">
            <input type="checkbox" class="form-check-input auto-fill-remark" data-index="<?= $i ?>" <?= (strtolower(trim($parsed_remarks[$i] ?? '')) === 'given') ? 'checked' : '' ?>>
          </td>
          <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fa fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; */ ?>

        <!-- First dynamic row -->
        <tr>
          <td><?= render_select('prescription_medicine_name[]', $medicine_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-name'); ?></td>
          <td><?= render_select('prescription_medicine_potency[]', $potency_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-potency'); ?></td>
          <td><?= render_select('prescription_medicine_dose[]', $dose_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-dose'); ?></td>
          <td><?= render_select('prescription_medicine_timings[]', $timing_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-timings'); ?></td>
          <td>
			  <textarea name="prescription_medicine_remarks[]" class="form-control prescription-medicine-remarks"></textarea>
			</td>

          <td>
		  <textarea  class="form-control prescription-medicine-remarks" readonly></textarea>
		  </td>
          <td class="text-center">
            <input type="checkbox" class="form-check-input auto-fill-remark" data-index="new">
          </td>
          <td class="text-center">
            <button type="button" class="btn btn-success btn-sm" id="addPrescriptionRowBtn"><i class="fa fa-plus"></i></button>
          </td>
        </tr>
      </tbody>
    </table>

    <script>
    const medicineOptions = <?= json_encode($medicine_options); ?>;
    const potencyOptions = <?= json_encode($potency_options); ?>;
    const doseOptions = <?= json_encode($dose_options); ?>;
    const timingOptions = <?= json_encode($timing_options); ?>;

    function buildOptions(options) {
      return `<option value="">-- Select --</option>` +
             options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('');
    }

    function createNewRow(index) {
      return `
        <tr>
          <td><select name="prescription_medicine_name[]" class="form-control prescription-medicine-name">${buildOptions(medicineOptions)}</select></td>
          <td><select name="prescription_medicine_potency[]" class="form-control prescription-medicine-potency">${buildOptions(potencyOptions)}</select></td>
          <td><select name="prescription_medicine_dose[]" class="form-control prescription-medicine-dose">${buildOptions(doseOptions)}</select></td>
          <td><select name="prescription_medicine_timings[]" class="form-control prescription-medicine-timings">${buildOptions(timingOptions)}</select></td>
          <td>
		  <textarea name="prescription_medicine_remarks[]" class="form-control prescription-medicine-remarks"></textarea>
		  </td>
		  <td><textarea  class="form-control prescription-prescription-remarks" readonly></textarea></td>
          <td class="text-center">
            <input type="checkbox" class="form-check-input auto-fill-remark" data-index="${index}">
          </td>
          <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fa fa-trash"></i></button>
          </td>
        </tr>`;
    }

    document.addEventListener('DOMContentLoaded', function () {
      const tableBody = document.getElementById('prescriptionMedicineBody');

      document.getElementById('addPrescriptionRowBtn').addEventListener('click', function () {
        const index = tableBody.querySelectorAll('tr').length;
        tableBody.insertAdjacentHTML('beforeend', createNewRow(index));
      });

      tableBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row-btn')) {
          e.target.closest('tr').remove();
        }
      });

      tableBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('prescription-medicine-timings')) {
          const all = document.querySelectorAll('.prescription-medicine-timings');
          if (e.target === all[all.length - 1]) {
            const index = all.length;
            tableBody.insertAdjacentHTML('beforeend', createNewRow(index));
          }
        }

        if (e.target.classList.contains('auto-fill-remark')) {
          const index = e.target.dataset.index;
          const remarkInputs = document.querySelectorAll('input[name="prescription_medicine_remarks[]"]');
          if (index !== "new" && remarkInputs[index]) {
            remarkInputs[index].value = e.target.checked ? 'Given' : '';
          } else if (index === "new") {
            // Find the closest remark input in the same row
            const row = e.target.closest('tr');
            const remarkInput = row.querySelector('.prescription-medicine-remarks');
            if (remarkInput) {
              remarkInput.value = e.target.checked ? 'Given' : '';
            }
          }
        }
      });
    });
    </script>
  </div>
</div>


  </div> <!-- End of Accordion -->

  <!-- Form Actions -->
  <div class="form-actions">
	 <button type="button" class="btn btn-primary" id="saveCasesheetBtn">
  <?= _l('save'); ?>
</button>
	   <button type="button" class="btn btn-primary" id="saveCallNextBtn">
  <?= _l('save_and_call_next_patient'); ?>
</button>
    <a href="<?= admin_url('client/get_patient_list/' . $case['userid'] . '/tab_casesheet'); ?>" class="btn btn-secondary">
    <?= _l('cancel'); ?>
</a>

  </div>

</form>

</div>
</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<script>
let consultationStartTime = null;
let consultationInterval = null;
    function toggleCaseSheetForm() {
	   consultationStartTime = new Date(); // start timer
		document.getElementById('consultation_timer_container').style.display = 'inline-block'; // show timer
		startConsultationTimer(); // start counter
		const form = document.getElementById('casesheet-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
	document.addEventListener("DOMContentLoaded", function () {
    toggleCaseSheetForm();
});

	
	function startConsultationTimer() {
		const timerElement = document.getElementById('consultation_timer');

		// Clear any existing interval (avoid duplicates)
		if (consultationInterval) clearInterval(consultationInterval);

		consultationInterval = setInterval(() => {
			const now = new Date();
			const durationSeconds = Math.floor((now - consultationStartTime) / 1000);
			const minutes = Math.floor(durationSeconds / 60);
			const seconds = durationSeconds % 60;

			// Format as MM:SS
			timerElement.textContent = `${pad(minutes)}:${pad(seconds)}`;

			// Update hidden input for form submission
			const hiddenField = document.getElementById('consultation_duration');
			if (hiddenField) {
				hiddenField.value = durationSeconds;
			}
		}, 1000);
	}

	function pad(value) {
		return value < 10 ? '0' + value : value;
	}
</script>

<script>
// Remove blur when second modal closes
$('#caseSheetModal').on('hidden.bs.modal', function () {
  $('.modal .modal-content').removeClass('blurred');
});

  document.getElementById('medicine_days').addEventListener('input', function () {
    let days = parseInt(this.value);
    if (!isNaN(days) && days > 0) {
      let today = new Date();
      today.setDate(today.getDate() + days);
      let followUp = today.toISOString().split('T')[0]; // Format to YYYY-MM-DD
      document.getElementById('followup_date').value = followUp;
    } else {
      document.getElementById('followup_date').value = '';
    }
  });


</script>

<script>
    document.querySelectorAll('.improvement-input').forEach(function(input) {
        input.addEventListener('input', function () {
            if (this.value > 100) this.value = 100;
            if (this.value < 0) this.value = 0;
        });
    });
</script>
<!-- JavaScript to Add/Clone Rows and Update Progress -->
<script>

$(document).ready(function () {

    // Add new row
    $('#treatment_rows').on('click', '.add-row', function () {
        var newRow = `
        <div class="row treatment-row align-items-end mb-3">
            <div class="col-md-3">
                <?php
				$selected = "";
				echo render_select('treatment_type[]', $treatments, ['treatment_id', 'treatment_name'], '', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
				?>
            </div>

             <div class="col-md-3">
				
						<input type="number" name="duration_value[]" class="form-control" min="1" placeholder="Number">
					
					
			</div>

            <div class="col-md-4">
		<?PHP
		if ($casesheet_data['suggested_diagnostics_id']) {
			$selected_diagnostic_id = isset($casesheet_data['suggested_diagnostics_id']) ? $casesheet_data['suggested_diagnostics_id'] : '';
		} else {
			$selected_diagnostic_id = isset($last_appointment['suggested_diagnostics_id']) ? $last_appointment['suggested_diagnostics_id'] : '';
		}

		echo render_select(
				'suggested_diagnostics_id[]',
				$suggested_diagnostics, // This should be your array of diagnostic options
				['suggested_diagnostics_id', 'suggested_diagnostics_name'],
				'',
				$selected_diagnostic_id, // selected value
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex')
				]
			);

		?>
</div>

            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-row"><i class="fa fa-minus"></i> </button>
            </div>
        </div>`;
        
        // Append the new row
        $('#treatment_rows').append(newRow);

        // Refresh selectpicker
        $('.selectpicker').selectpicker('refresh');
		 $('#treatment_rows .improvement-input').last().on('input', function () {
        if (this.value > 100) this.value = 100;
        if (this.value < 0) this.value = 0;
    });
    });

    // Remove row
    $('#treatment_rows').on('click', '.remove-row', function () {
        // Ensure that there is at least one row left
        if ($('.treatment-row').length > 1) {
            $(this).closest('.treatment-row').remove();
        }
    });

    // Update progress bar based on percentage improvement input
    $('#treatment_rows').on('input', '.improvement-input', function () {
        var value = $(this).val();
        value = Math.max(0, Math.min(100, value)); // Ensure it's between 0 and 100
        var progressBar = $(this).closest('.treatment-row').find('.progress-bar');
		if(value == 0 || value == ''){
			value = 0;
		}
		
        progressBar.css('width', value + '%').attr('aria-valuenow', value).text(value + '%');
    });
	
	$('.edit_treatment_rows').on('input', '.improvement-input', function () {
        var value = $(this).val();
        value = Math.max(0, Math.min(100, value)); // Ensure it's between 0 and 100
        var progressBar = $(this).closest('.treatment-row').find('.progress-bar');
		if(value == 0 || value == ''){
			value = 0;
		}
		
        progressBar.css('width', value + '%').attr('aria-valuenow', value).text(value + '%');
    });
});

$(document).ready(function () {
    $('.edit_treatment_rows .improvement-input').each(function () {
        var value = $(this).val();
        value = Math.max(0, Math.min(100, value)); // Clamp between 0–100
        var progressBar = $(this).closest('.treatment-row').find('.progress-bar');

        if (value == 0 || value === '') {
            value = 0;
        }

        progressBar.css('width', value + '%')
                   .attr('aria-valuenow', value)
                   .text(value + '%');
    });
});

let isSaving = false; // global flag

function handleSave($btn, isCallNext = false) {
  if (isSaving) return; // already saving → block
  isSaving = true;

  $btn.prop('disabled', true).text('<?= _l("please_wait") ?>...');

  const $form = $('#casesheetForm');
  const recordId = $('#record_id').val();
  const selectedClientId = <?= $client->userid; ?>;
  const redirectUrl = "<?= admin_url('client/save_and_call_next_patient/' . $client->userid . '/' . get_staff_user_id()); ?>";
	
  if (consultationStartTime) {
	  
    const now = new Date();
    const durationSeconds = Math.floor((now - consultationStartTime) / 1000);
    $('#consultation_duration').val(durationSeconds);
    console.log("Duration captured:", durationSeconds, "seconds");
  }

  tinyMCE.triggerSave();
  $(window).off('beforeunload');

  const handleError = (msg) => {
    alert(msg || "Error while saving. Please try again.");
    $btn.prop('disabled', false).text(isCallNext ? '<?= _l("save_and_call_next_patient") ?>' : '<?= _l("save") ?>');
    isSaving = false; // allow retry
  };

  const handleSuccess = () => {
    if (isCallNext) {
      if (confirm("The case sheet has been saved. Do you want to proceed to the next patient?")) {
        window.location.href = redirectUrl;
      }
    } else {
      alert_float("success", 'Casesheet Saved');
      window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_casesheet";
    }
  };
  // Update or Insert
  if (recordId) {
    $.ajax({
      url: '<?= admin_url('client/update_casesheet') ?>/' + recordId,
      type: 'POST',
      data: $form.serialize(),
      success: function () {
        $('.table-casesheet').DataTable().ajax.reload(null, false);
        handleSuccess();
      },
      error: function () {
        handleError();
      }
    });
  } else {
    $.post("<?= admin_url('client/save_casesheet') ?>", $form.serialize())
      .done(function (response) {
        let res = JSON.parse(response);
        if (res.success) {
          $('#record_id').val(res.id);
          handleSuccess();
        } else {
          handleError("Save failed: " + res.message);
        }
      })
      .fail(function () {
        handleError("Something went wrong while saving.");
      });
  }
}

$('#saveCasesheetBtn').on('click', function () {
  handleSave($(this), false);
});

$('#saveCallNextBtn').on('click', function () {
  handleSave($(this), true);
});



// Optional: Custom function to handle update (AJAX version)
function updateCaseSheet(id) {
	tinyMCE.triggerSave(); // Sync editor to textarea
let formData = $('#casesheetForm').serialize();
  //const formData = $('#casesheetForm').serialize();
  $.ajax({
    url: '<?= admin_url('client/update_casesheet') ?>/' + id, // your controller path
    type: 'POST',
    data: formData,
    success: function (res) {
		$('.table-casesheet').DataTable().ajax.reload(null, false);
      console.log('Case sheet updated');
    }
  });
}



$("body").on("submit", "#casesheetForm", function (e) {
  e.preventDefault();
alert();
  var form = $(this);
  var formData = form.serializeArray();
  console.log("Serialized array:", formData);

  // Optional: Convert to object for easier manipulation (if needed)
  let payload = {};
  formData.forEach(field => {
    if (payload[field.name]) {
      // If already exists, convert to array or push to it
      if (!Array.isArray(payload[field.name])) {
        payload[field.name] = [payload[field.name]];
      }
      payload[field.name].push(field.value);
    } else {
      payload[field.name] = field.value;
    }
  });

  console.log("Payload object:", payload);

  // Submit to the controller
  $.post(admin_url + "client/save_casesheet", form.serialize())
    .done(function (response) {
      let res = JSON.parse(response);
      if (res.success) {
		  //alert(res.id);
		  $('#record_id').val(res.id); // sets value in the hidden input

        
      } else {
        alert("Error: " + res.message);
      }
    })
    .fail(function () {
      alert("Something went wrong while saving the prescription.");
    });
});

</script>

</body>

</html>
