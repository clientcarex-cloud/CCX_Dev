<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  body {
    background-color: #f8f9fa;
  }
  .left-panel {
    background: #ffffff;
    border-right: 2px solid #dee2e6;
    padding: 20px;
    height: 100vh;
    overflow-y: auto;
  }
  .right-panel {
    padding: 20px;
    height: 100vh;
    overflow-y: auto;
  }
  .section-title {
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 600;
    color: #495057;
  }
  .patient-card {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  .patient-card h5 {
    margin-bottom: 10px;
  }
  .btn-call, .btn-recall {
    width: 100%;
  }
  .split-half {
    height: 50%;
    overflow-y: auto;
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row no-gutters">
      
      <!-- Left Panel -->
      <div class="col-md-8 left-panel">
        <div class="section-title"><?php echo _l("current_serving_patient"); ?></div>

        <div class="form-group">
          <?php
          echo render_select(
              'counter_id', 
              $counters, 
              ['counter_id', ['counter_name']], 
              'counter', 
              isset($selected_counter_id) ? $selected_counter_id : '', 
              ['data-none-selected-text' => _l('dropdown_non_selected_tex')]
          );
          ?>
        </div>

        <?php if (!empty($current_patient)) {
            $current_patient = $current_patient[0];
        ?>
          <div class="patient-card">
            <h5><?php echo e($current_patient['patient_name']); ?> (<?php echo _l('token'); ?> #<?php echo e($current_patient['token_number']); ?>)</h5>
            <p><strong><?php echo _l('doctor'); ?>:</strong> <?php echo e($current_patient['doctor_name']); ?></p>
            <p><strong><?php echo _l('age'); ?>:</strong> <?php echo e($current_patient['age']); ?></p>
            <p><strong><?php echo _l('gender'); ?>:</strong> <?php echo e($current_patient['gender']); ?></p>
            <?php
            if (!empty($queued_patients)) {
              $token_id = $queued_patients[0]['token_id'];
              $token_status = "Serving";
            ?>
              <a href="<?= admin_url('token_system/next_call/'.$token_id.'/'.$token_status.'/'.$selected_counter_id); ?>">
                <button class="btn btn-success btn-call"><?php echo _l('served'); ?></button>
              </a>
            <?php } else {
              $token_status = "Completed";
            ?>
              <a href="<?= admin_url('token_system/next_call/'.$current_patient['token_id'].'/'.$token_status.'/'.$selected_counter_id); ?>">
                <button class="btn btn-success btn-call"><?php echo _l('served'); ?></button>
              </a>
            <?php } ?>
          </div>
        <?php } else { ?>
          <div class="alert alert-warning">
            <?php echo _l('no_current_patient'); ?>
          </div>
        <?php } ?>
      </div>

      <!-- Right Panel -->
      <div class="col-md-4 right-panel d-flex flex-column">

        <!-- Queued Patients -->
        <div class="split-half">
          <div class="section-title"><?php echo _l('queued_patients'); ?></div>
          <?php if (!empty($queued_patients)) {
              foreach ($queued_patients as $patient) {
              $token_status = "Serving";
          ?>
            <div class="patient-card">
              <h5><?php echo e($patient['company']); ?> (<?php echo _l('token'); ?> #<?php echo e($patient['token_number']); ?>)</h5>
              <p><strong><?php echo _l('doctor'); ?>:</strong> <?php echo e($patient['doctor_name']); ?></p>
              <p><?php echo _l('age'); ?>: <?php echo e($patient['age']); ?> | <?php echo e($patient['gender']); ?></p>
              <a href="<?= admin_url('token_system/next_call/'.$patient['token_id'].'/'.$token_status.'/'.$selected_counter_id); ?>">
                <button class="btn btn-success btn-call"><?php echo _l('call'); ?></button>
              </a>
            </div>
          <?php }
          } else { ?>
            <div class="alert alert-info">
              <?php echo _l('no_queued_patients'); ?>
            </div>
          <?php } ?>
        </div>

        <!-- Completed Patients -->
        <div class="split-half mt-3">
          <div class="section-title"><?php echo _l('completed_patients'); ?></div>
          <?php if (!empty($completed_patients)) {
              foreach ($completed_patients as $patient) {
              $token_status = "Recall";
          ?>
            <div class="patient-card">
              <h5><?php echo e($patient['patient_name']); ?> (<?php echo _l('token'); ?> #<?php echo e($patient['token_number']); ?>)</h5>
              <p><strong><?php echo _l('doctor'); ?>:</strong> <?php echo e($patient['doctor_name']); ?></p>
              <p><?php echo _l('age'); ?>: <?php echo e($patient['age']); ?> | <?php echo e($patient['gender']); ?></p>
              <a href="<?= admin_url('token_system/next_call/'.$patient['token_id'].'/'.$token_status.'/'.$selected_counter_id); ?>">
                <button class="btn btn-warning btn-recall"><?php echo _l('recall'); ?></button>
              </a>
            </div>
          <?php }
          } else { ?>
            <div class="alert alert-info">
              <?php echo _l('no_completed_patients'); ?>
            </div>
          <?php } ?>
        </div>

      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const counterSelect = document.querySelector('select[name="counter_id"]');

    counterSelect.addEventListener('change', function () {
        const selectedCounterId = this.value;

        if (selectedCounterId) {
            const url = admin_url + 'token_system/call/' + selectedCounterId;
            window.location.href = url;
        }
    });
});
</script>

<script>
function callPatient(tokenId) {
  Swal.fire({
    title: '<?php echo _l('call_patient'); ?>',
    text: '<?php echo _l('confirm_call_patient'); ?>',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<?php echo _l('yes_call'); ?>',
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "<?= admin_url('token_system/next_call/'); ?>" + tokenId;
    }
  });
}

function recallPatient(tokenId) {
  Swal.fire({
    title: '<?php echo _l('recall_patient'); ?>',
    text: '<?php echo _l('confirm_recall_patient'); ?>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<?php echo _l('yes_recall'); ?>',
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "<?= admin_url('token_system/recall_patient/'); ?>" + tokenId;
    }
  });
}
</script>
