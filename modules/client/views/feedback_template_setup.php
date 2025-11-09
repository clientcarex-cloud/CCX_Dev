<div class="row">
  <div class="col-md-12">
    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
      <?= _l('feedback_templates_settings'); ?>
    </h4>
    <div class="panel_s">
      <div class="panel-body">
        <div class="col-md-6">
          <?= render_input('feedback_template_name', 'feedback_template_name', get_option('feedback_template_name')); ?>
        </div>

        <div class="col-md-6">
          <?= render_input('feedback_template_subject', 'feedback_template_subject', get_option('feedback_template_subject')); ?>
        </div>

        <div class="col-md-12">
		  <h4><strong><?= _l('email_content'); ?>:</strong></h4>
		</div>

		<div class="col-md-12">
		  <?= render_textarea('feedback_template_content', '', get_option('feedback_template_content'), ['rows' => 10], [], '', 'feedback_template_content'); ?>
		  <label id="toggle_merge_fields_email" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
		</div>

		<div class="col-md-12" id="email_merge_fields" style="display: none;">
		  <code class="merge-field" data-target="feedback_template_content">{patient_name}</code>,
		  <code class="merge-field" data-target="feedback_template_content">{mobile}</code>,
		  <code class="merge-field" data-target="feedback_template_content">{email}</code>,
		  <code class="merge-field" data-target="feedback_template_content">{link}</code>
		</div>


        <div class="col-md-12">
          <h4><strong><?= _l('sms_content'); ?>:</strong></h4>
        </div>

        <div class="col-md-6">
          <?= render_input('feedback_sms_template_id', 'feedback_sms_template_id', get_option('feedback_sms_template_id')); ?>
        </div>

        <div class="col-md-12">
          <?= render_textarea('feedback_sms_template_content', 'feedback_sms_template_content', get_option('feedback_sms_template_content'), ['rows' => 6], [], '', 'feedback_sms_template_content'); ?>
          <label id="toggle_merge_fields_sms" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
        </div>

        <div class="col-md-12" id="sms_merge_fields" style="display: none;">
          <code class="merge-field" data-target="feedback_sms_template_content">{patient_name}</code>,
          <code class="merge-field" data-target="feedback_sms_template_content">{mobile}</code>,
          <code class="merge-field" data-target="feedback_sms_template_content">{email}</code>,
          <code class="merge-field" data-target="feedback_sms_template_content">{link}</code>
        </div>

        <div class="col-md-12">
          <h4><strong><?= _l('whatsapp_content'); ?>:</strong></h4>
        </div>

        <div class="col-md-6">
          <?= render_input('feedback_whatsapp_template_name', 'feedback_whatsapp_template_name', get_option('feedback_whatsapp_template_name')); ?>
        </div>

        <div class="col-md-12">
          <?= render_textarea('feedback_whatsapp_template_content', 'feedback_whatsapp_template_content', get_option('feedback_whatsapp_template_content'), ['rows' => 6], [], '', 'feedback_whatsapp_template_content'); ?>
          <label id="toggle_merge_fields" style="cursor: pointer; color: blue;"><?= _l('available_merge_fields'); ?></label>
        </div>

        <div class="col-md-12" id="whatsapp_merge_fields" style="display: none;">
          <code class="merge-field" data-target="feedback_whatsapp_template_content">{patient_name}</code>,
          <code class="merge-field" data-target="feedback_whatsapp_template_content">{mobile}</code>,
          <code class="merge-field" data-target="feedback_whatsapp_template_content">{email}</code>,
          <code class="merge-field" data-target="feedback_whatsapp_template_content">{link}</code>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Toggle merge fields
    document.getElementById('toggle_merge_fields_sms')?.addEventListener('click', function () {
      const div = document.getElementById('sms_merge_fields');
      div.style.display = div.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('toggle_merge_fields')?.addEventListener('click', function () {
      const div = document.getElementById('whatsapp_merge_fields');
      div.style.display = div.style.display === 'none' ? 'block' : 'none';
    });
	
	document.getElementById('toggle_merge_fields_email')?.addEventListener('click', function () {
	  const div = document.getElementById('email_merge_fields');
	  div.style.display = div.style.display === 'none' ? 'block' : 'none';
	});


    // Insert merge field into textarea
    document.querySelectorAll('.merge-field').forEach(function (el) {
      el.addEventListener('click', function () {
        const target = el.getAttribute('data-target');
        const field = el.textContent;
        const textarea = document.getElementsByName(target)[0];

        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;

        textarea.value = value.substring(0, start) + field + value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + field.length, start + field.length);
      });
    });
  });
</script>
