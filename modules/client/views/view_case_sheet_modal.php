<?php
$case = $case[0];
?>
<div class="container-fluid">
  <!-- Personal History -->
  <h4><strong><?php echo _l('personal_history'); ?></strong></h4>
  <hr>
  <div class="row">
    <?php 
    $personal_fields = [
      'appetite', 'desires', 'aversion', 'tongue', 'urine', 'bowels',
      'sweat', 'sleep', 'sun_headache', 'thermals', 'habits', 'addiction',
      'side', 'dreams', 'diabetes', 'thyroid', 'hypertension',
      'hyperlipidemia', 'menstrual_obstetric_history', 'family_history', 'past_treatment_history'
    ];
    foreach ($personal_fields as $field): ?>
      <div class="col-md-4 mb-3">
        <div><strong><?php echo _l($field); ?>:</strong> <?php echo html_escape($case[$field]); ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Preliminary Data -->
   <br>
  <h4><strong><?php echo _l('preliminary_data'); ?></strong></h4>
  <hr>
  <div class="row">
    <div class="col-md-6 mb-3">
      <div><strong><?php echo _l('treatment'); ?>:</strong> <?php echo html_escape($case['treatment']); ?></div>
    </div>
    <div class="col-md-6 mb-3">
      <div><strong><?php echo _l('presenting_complaints'); ?>:</strong> <?php echo $case['presenting_complaints']; ?></div>
    </div>
  </div>

  <!-- General Examination -->
  <br>
  <h4><strong><?php echo _l('general_examination'); ?></strong></h4>
  <hr>
  <div class="row">
    <?php 
    $vitals = ['bp', 'pulse', 'weight', 'height', 'temperature', 'bmi'];
    foreach ($vitals as $field): ?>
      <div class="col-md-2 mb-3">
        <div><strong><?php echo _l($field); ?>:</strong> <?php echo html_escape($case[$field]); ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Other Case Fields -->
  <br>
  <h4><strong><?php echo _l('case_details'); ?></strong></h4>
  <hr>
  <div class="row">
    <?php 
    $others = [
      'mental_generals', 'pg', 'particulars',
      'miasmatic_diagnosis', 'analysis_evaluation', 'reportorial_result',
      'management', 'diet', 'exercise',
      'critical', 'level_of_assent', 'dos_and_donts',
      'level_of_assurance', 'criteria_future_plan_rx', 'nutrition'
    ];
    foreach ($others as $field): ?>
      <div class="col-md-4 mb-3">
        <div><strong><?php echo _l($field); ?>:</strong> <?php echo html_escape($case[$field]); ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Clinical Observation -->
  <br>
  <h4><strong><?php echo _l('clinical_observation'); ?></strong></h4>
  <hr>
  <div class="row">
    <div class="col-md-6 mb-3">
      <div><strong><?php echo _l('progress'); ?>:</strong> <?php echo html_escape($case['progress']); ?></div>
    </div>
    <div class="col-md-6 mb-3">
    <div class="d-flex">
        <strong class="me-2"><?php echo _l('clinical_observation'); ?>:</strong>
        <span><?php echo nl2br($case['clinical_observation']); ?></span>
    </div>
    </div>
    <div class="col-md-4 mb-3">
      <div><strong><?php echo _l('suggested_duration'); ?>:</strong> <?php echo html_escape($case['suggested_duration']); ?></div>
    </div>
    <div class="col-md-4 mb-3">
      <div><strong><?php echo _l('medicine_days'); ?>:</strong> <?php echo html_escape($case['medicine_days']); ?></div>
    </div>
    <div class="col-md-4 mb-3">
      <div><strong><?php echo _l('followup_date'); ?>:</strong> <?php echo html_escape($case['followup_date']); ?></div>
    </div>
    <div class="col-md-4 mb-3">
      <div><strong><?php echo _l('patient_status'); ?>:</strong> <?php echo html_escape($case['patient_status']); ?></div>
    </div>
  </div>
</div>
