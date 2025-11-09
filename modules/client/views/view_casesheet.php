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

	?>
	
  <!-- Accordion Tabs -->
  <div class="accordion" id="casesheetAccordion">

    <!-- Preliminary Data Tab -->
    <div class="card">
        <h4>
          
            <strong><?php echo _l('preliminary_data'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
			<div class="form-group">
    <!-- Initial Row -->
	<?php
	$patient_name = '';
	if (isset($client)) {
		if (is_object($client) && isset($client->company)) {
			$patient_name = $client->company;
		} elseif (is_array($client) && isset($client['company'])) {
			$patient_name = $client['company'];
		}
	}
	if ($patient_name !== '') {
		?>
		<div class="row mtop10">
			<div class="col-md-3">
				<label>Patient Name</label>
				<input type="text" class="form-control" value="<?php echo htmlspecialchars($patient_name, ENT_QUOTES, 'UTF-8'); ?>" disabled>
			</div>
		</div>
		<?php
	}
	foreach($prev_treatments as $prev_treatment){
		?>
		<input type="hidden" name="patient_treatment_id[]" value="<?php echo $prev_treatment['id'];?>">
		<div class="edit_treatment_rows">
		<div class="row treatment-row">
        <div class="col-md-2">
		<label>Treatment Type</label>
		<input type="textbox" value="<?php echo $prev_treatment['description'];?>" class="form-control" readonly>
        </div>

       <div class="col-md-2">
		<label>Duration</label>
		
			<input type="number" name="duration_value_<?php echo $prev_treatment['id'];?>" value="<?php echo $prev_treatment['duration_value'];?>" class="form-control" min="1" placeholder="Number">
			
		</div>


			<!--<div class="col-md-2">
				<label>Improvement(%)</label>
				<input type="number" name="improvement_<?php echo $prev_treatment['id'];?>" class="form-control improvement-input" value="<?php echo $prev_treatment['improvement'];?>" min="0" max="100" placeholder="Enter Percentage" >
			</div>

			<div class="col-md-3">
				<label>Overall Progress</label>
				<div class="progress">
				
					<div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
				</div>
			</div>-->
			<div class="col-md-4">
				<label>Suggested Diagnostics</label>
				<input type="textbox" value="<?php echo $prev_treatment['suggested_diagnostics_name'];?>" class="form-control" readonly>
			</div>
			<div class="col-md-3">
			<label>Status</label>
			<select name="treatment_status_<?php echo $prev_treatment['id'];?>" class="form-control">
				<option value="treatment_started" <?php if($prev_treatment['treatment_status'] == "treatment_started"){ echo "Selected"; }?>>Started</option>
				<option value="treatment_inprogress" <?php if($prev_treatment['treatment_status'] == "treatment_inprogress"){ echo "Selected"; }?>>Inprogress</option>
				<option value="treatment_completed" <?php if($prev_treatment['treatment_status'] == "treatment_completed"){ echo "Selected"; }?>>Completed</option>
			</select>
				
			</div>
		</div>
		</div>
		<?php
		}
	?>
	<br>
	<hr>
	

</div>
	
			
		<!-- Presenting Complaints -->
		<div class="form-group mtop20">
			<label for="presenting_complaints" class="control-label">
				<?php echo _l('presenting_complaints'); ?>
			</label>
			<textarea id="presenting_complaints" name="presenting_complaints" class="form-control tinymce" rows="6"><?php echo $case['presenting_complaints'];?></textarea>
		</div>
		
		<!-- Presenting Complaints -->
		<div class="form-group mtop20">
			<label for="complaint" class="control-label">
				<?php echo _l('complaint'); ?>
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
			  </div> <div class="col-md-4 mb-3">
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
	 <h4>
	  <br>
          <strong><?php echo _l('prescription'); ?></strong> 
        </h4>
		<hr>
        <div class="card-body">
        <?php
		$parsed_prescriptions = [];
		if (!empty($prescription[0]['prescription_data'])) {
			// Split each record using "|"
			$items = explode('|', $prescription[0]['prescription_data']);

			foreach ($items as $item) {
				$item = trim($item);
				if ($item !== '') {
					// Remove numbering like "1." or "2."
					$item = preg_replace('/^\d+\.\s*/', '', $item);

					// Split fields by ";"
					$parts = explode(';', $item);

					// Trim each part but keep empty values
					$parts = array_map('trim', $parts);

					// Always add (even if some are empty)
					$parsed_prescriptions[] = $parts;
				}
			}
		}

		
		$parsed_remarks = [];
		if (!empty($prescription[0]['medicine_remarks'])) {
			$parsed_remarks = explode('|', $prescription[0]['medicine_remarks']);
		}
		
		?>

		

      <?php
	  
	$medicine_options = [];
	foreach ($medicines as $m) {
		$medicine_options[] = ['id' => $m['medicine_name'], 'name' => $m['medicine_name']];
	}

	$potency_options = [];
	foreach ($potencies as $p) {
		$potency_options[] = ['id' => $p['medicine_potency_name'], 'name' => $p['medicine_potency_name']];
	}

	$dose_options = [];
	foreach ($doses as $d) {
		$dose_options[] = ['id' => $d['medicine_dose_name'], 'name' => $d['medicine_dose_name']];
	}

	$timing_options = [];
	foreach ($timings as $t) {
		$timing_options[] = ['id' => $t['medicine_timing_name'], 'name' => $t['medicine_timing_name']];
	}
	
	?>

	<table class="prescription-medicine-table table" id="prescriptionMedicineTable">
  <thead>
    <tr>
      <th><?= _l('medicine_name'); ?></th>
      <th><?= _l('potency'); ?></th>
      <th><?= _l('dose'); ?></th>
      <th><?= _l('timings'); ?></th>
      <th><?= _l('doctor_remarks'); ?></th>
      <th><?= _l('pharamacy_remarks'); ?></th>
    </tr>
  </thead>
  <tbody id="prescriptionMedicineBody">
	<?php 
	$i=0;
	foreach ($parsed_prescriptions as $presc): ?>
	<tr>
	  <td><input type="text" name="prescription_medicine_name[]" class="form-control" value="<?= htmlspecialchars($presc[0] ?? '') ?>" readonly></td>
	  <td><input type="text" name="prescription_medicine_potency[]" class="form-control" value="<?= htmlspecialchars($presc[1] ?? '') ?>" readonly></td>
	  <td><input type="text" name="prescription_medicine_dose[]" class="form-control" value="<?= htmlspecialchars($presc[2] ?? '') ?>" readonly></td>
	  <td><input type="text" name="prescription_medicine_timings[]" class="form-control" value="<?= htmlspecialchars($presc[3] ?? '') ?>" readonly></td>
	 <td>
	  <textarea name="prescription_medicine_remarks[]" class="form-control" readonly><?= htmlspecialchars($presc[4] ?? '') ?></textarea>
	</td>
	<td>
	  <textarea name="prescription_pharmacy_remarks[]" class="form-control" readonly><?= htmlspecialchars($parsed_remarks[$i++] ?? '') ?></textarea>
	</td>

	  
	</tr>
	<?php endforeach; ?>

  </tbody>
</table>

        </div>
      </div>
	  
	  <div class="row">
			
            <div class="col-md-4">
              <label for="medicine_days"><?php echo _l('medicine_days'); ?></label>
              <input type="number" name="medicine_days" id="medicine_days" value="<?php echo $case['medicine_days'];?>" class="form-control">
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
			<div class="col-md-12">
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

  </div> <!-- End of Accordion -->

  <!-- Form Actions -->
  <div class="form-actions">
    <a href="<?= admin_url('client/get_patient_list/' . $case['userid'] . '/tab_casesheet'); ?>" class="btn btn-sm" style="background-color: black; color: white;">
    <?= _l('back'); ?>
</a>


  </div>




</div>
</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Make all input fields readonly
  document.querySelectorAll('input').forEach(function (input) {
    input.setAttribute('readonly', true);
  });

  // Disable all select fields
  document.querySelectorAll('select').forEach(function (select) {
    select.setAttribute('disabled', true);
  });

  // Make all textarea fields readonly
  document.querySelectorAll('textarea').forEach(function (textarea) {
    textarea.setAttribute('readonly', true);
  });
});
</script>

</body>

</html>
