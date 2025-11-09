<?php

$fields = [
  'appointment_date', 'appointment_time', 'assigned_doctor', 'date_time', 'branch_address',
  'invoice_link', 'invoice_number', 'reg_date_start', 'reg_date_end', 'follow_up_date',
  'punch_in_time', 'shift_time', 'missed_appoinment', 'package_cost', 'paid_amount',
  'pending_amount', 'treatment_duration', 'renewal_date', 'branch', 'employee_id',
  'treatment', 'last_payment_date', 'email', 'phonenumber', 'patient_name', 'vertical_name', 'branch_phone'
];


$field_placeholders = array_map(function($f) {
    return '{' . $f . '}';
}, $fields);


$modules = [
  'appointment_created' => [],
  'package_accepted' => [],
  'package_created' => [],
  'patient_registered' => [],
  'payment_done' => [],
  'call_back' => [],
  'call_feedback' => [],
  'missed_appointment' => [],
  'reschedule_appointment' => [],
  'not_registered_patient_day1' => [],
  'not_registered_patient_week1' => [],
  'medicine_appointment' => [],
  'renewal_reminder' => [],
  'clinic_closed' => [],
  'doctor_changed' => [],
  'employee_absence' => [],
  'employee_late_hr' => [],
  'uninformed_leave_hr' => [],
  'new_joiner_punch_in' => [],
  'monthly_attendance_confirmation' => [],
  'uninformed_leave_employee' => [],
  'daily_eod' => [],
  'uninformed_leave_manager' => [],
  'eod_report' => [],
  'dr_early_punch_in' => [],
  'dr_normal_punch_in' => [],
  'medicine_followup' => [],
  'leave_reconsideration' => [],
];

// List of required keys
$additional_keys = [
  'call_back',
  'enquiry',
  'call_back',
  'junk',
  'lost',
  'missed_appointment',
  'new',
  'no_feedback',
  'no_response',
  'on_appointment',
  'only_consulted',
  'paid_appointment',
  'prospect',
  //'reg._patients',
  'visited',
  'feedback_auto_reply',
  'refer_to_us_auto_reply',
  'edit_auto_reply',
];

// Add missing keys
foreach ($additional_keys as $key) {
  if (!array_key_exists($key, $modules)) {
    $modules[$key] = [];
  }
}


// Add field placeholders to each module (while ensuring uniqueness)
$field_placeholders = array_map(function($f) {
    return '{' . $f . '}';
}, [
  'appointment_date', 'appointment_time', 'assigned_doctor', 'date_time', 'branch_address',
  'invoice_link', 'invoice_number', 'reg_date_start', 'reg_date_end', 'follow_up_date',
  'punch_in_time', 'shift_time', 'missed_appoinment', 'package_cost', 'paid_amount',
  'pending_amount', 'treatment_duration', 'renewal_date', 'branch', 'employee_id',
  'treatment', 'last_payment_date', 'email', 'phonenumber', 'patient_name', 'vertical_name', 'branch_phone', 'receipt_link', 'paid_date'
]);

foreach ($modules as $key => &$placeholders) {
    $placeholders = array_unique(array_merge($placeholders, $field_placeholders));
}
unset($placeholders); // Unset reference


?>
<?php foreach ($modules as $module_key => $merge_fields): ?>
  <div class="row">
    <div class="col-md-12">
      <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
        <?= _l("{$module_key}_templates_settings"); ?>
      </h4>
      <div class="panel_s">
        <div class="panel-body">
          <!-- Enable/Disable Switch -->
          <div class="col-md-12">
            <?= render_yes_no_option("{$module_key}_template_enabled", _l("enable_{$module_key}_template"), get_option("{$module_key}_template_enabled")); ?>
          </div>

          <!-- Name & Subject -->
         <!-- <div class="col-md-6">
            <?= render_input("{$module_key}_template_name", "{$module_key}_template_name", get_option("{$module_key}_template_name")); ?>
          </div>
          <div class="col-md-6">
            <?= render_input("{$module_key}_template_subject", "{$module_key}_template_subject", get_option("{$module_key}_template_subject")); ?>
          </div>-->

          <!-- Email Content -->
          <div class="col-md-12">
            <h4><strong><?= _l('email_content'); ?>:</strong></h4>
            <?= render_textarea("{$module_key}_template_content", '', get_option("{$module_key}_template_content"), ['rows' => 10], [], '', "{$module_key}_template_content"); ?>
            <label id="toggle_merge_fields_email_<?= $module_key ?>" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
          </div>
          <div class="col-md-12" id="email_merge_fields_<?= $module_key ?>" style="display: none;">
            <?php foreach ($merge_fields as $field): ?>
              <code class="merge-field" data-target="<?= $module_key ?>_template_content"><?= $field ?></code>,
            <?php endforeach; ?>
          </div>

          <!-- SMS Content -->
          <div class="col-md-12"><h4><strong><?= _l('sms_content'); ?>:</strong></h4></div>
          <div class="col-md-6">
            <?= render_input("{$module_key}_sms_template_id", "{$module_key}_sms_template_id", get_option("{$module_key}_sms_template_id")); ?>
          </div>
          <div class="col-md-12">
            <?= render_textarea("{$module_key}_sms_template_content", "{$module_key}_sms_template_content", get_option("{$module_key}_sms_template_content"), ['rows' => 6], [], '', "{$module_key}_sms_template_content"); ?>
            <label id="toggle_merge_fields_sms_<?= $module_key ?>" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
          </div>
          <div class="col-md-12" id="sms_merge_fields_<?= $module_key ?>" style="display: none;">
            <?php foreach ($merge_fields as $field): ?>
              <code class="merge-field" data-target="<?= $module_key ?>_sms_template_content"><?= $field ?></code>,
            <?php endforeach; ?>
          </div>

          <!-- WhatsApp Content -->
          <div class="col-md-12"><h4><strong><?= _l('whatsapp_content'); ?>:</strong></h4></div>
          <div class="col-md-6">
            <?= render_input("{$module_key}_whatsapp_template_name", "{$module_key}_whatsapp_template_name", get_option("{$module_key}_whatsapp_template_name")); ?>
          </div>
          <div class="col-md-12">
            <?= render_textarea("{$module_key}_whatsapp_template_content", "{$module_key}_whatsapp_template_content", get_option("{$module_key}_whatsapp_template_content"), ['rows' => 6], [], '', "{$module_key}_whatsapp_template_content"); ?>
            <label id="toggle_merge_fields_whatsapp_<?= $module_key ?>" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
          </div>
          <div class="col-md-12" id="whatsapp_merge_fields_<?= $module_key ?>" style="display: none;">
            <?php foreach ($merge_fields as $field): ?>
              <code class="merge-field" data-target="<?= $module_key ?>_whatsapp_template_content"><?= $field ?></code>,
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. Hide all merge field sections initially
  document.querySelectorAll('[id^="email_merge_fields_"], [id^="sms_merge_fields_"], [id^="whatsapp_merge_fields_"]').forEach(div => {
    div.style.display = 'none';
  });

  // 2. Add toggle to show/hide merge fields
 document.querySelectorAll('.toggle-merge').forEach(function (toggle) {
  toggle.addEventListener('click', function () {
    const contentId = toggle.dataset.targetId;
    const container = document.getElementById(contentId);
    if (container) {
      container.style.display = container.style.display === 'none' ? 'block' : 'none';
    }
  });
});


  // 3. Insert merge field into the correct textarea or input
  document.querySelectorAll('.merge-field').forEach(function (el) {
    el.addEventListener('click', function () {
      const target = el.getAttribute('data-target');
      const field = el.textContent.trim();
      const targetElement = document.getElementsByName(target)[0];

      if (!targetElement) return;

      const start = targetElement.selectionStart || 0;
      const end = targetElement.selectionEnd || 0;
      const value = targetElement.value;

      targetElement.value = value.substring(0, start) + field + value.substring(end);
      targetElement.focus();
      targetElement.setSelectionRange(start + field.length, start + field.length);
    });
  });
});
</script>
