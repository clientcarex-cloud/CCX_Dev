<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
			  <h4 class="no-margin">WhatsApp/SMS Templates</h4>
			  <a href="#" onclick="addTemplate(); return false;" class="btn btn-info pull-right">+ Add</a>
			  <div class="clearfix"></div>
			  <hr class="hr-panel-heading" />
			  <!-- Render the table -->
			  <?php render_datatable([
				'Template ID',
				'Channel',
				'Template Name',
				'Message',
				'Last Updated',
				'Trigger Key',
				'Status',
				_l('Action')
			], 'message-config'); ?>
			</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel">
  <div class="modal-dialog" role="document">
    <form id="templateForm" method="post" action="<?= admin_url('whatsapp/manage_templates/save'); ?>">
	<input type="hidden" class="txt_csrfname" id="txt_csrfname" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="id" id="template_id">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="templateModalLabel">Add Template</h4>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
		  <?php
			$options = [
				['id' => 'APPOINTMENT_CONFIRMATION', 'name' => 'APPOINTMENT_CONFIRMATION'],
				['id' => 'FOLLOWUP_REMINDER', 'name' => 'FOLLOWUP_REMINDER'],
				['id' => 'TREATMENT_NOT_STARTED', 'name' => 'TREATMENT_NOT_STARTED'],
			];
			?>

			<?= render_select('trigger_key', $options, ['id', 'name'], 'Trigger Key'); ?>

		  
		  <?php
			$channels = [
				['value' => 'sms', 'label' => 'SMS'],
				['value' => 'whatsapp', 'label' => 'WhatsApp']
			];
			?>
			<?= render_select('template_channel', $channels, ['value', 'label'], 'Channel'); ?>
          <?= render_input('template_id', 'Template ID'); ?>
          <?= render_input('template_name', 'Template Name'); ?>
          <?= render_textarea('template_body', 'Template Body'); ?>
          <?= render_input('params_required', 'Params (comma separated)'); ?>
		<?php
		$status_options = [
			['value' => '1', 'label' => 'Active'],
			['value' => '0', 'label' => 'Inactive']
		];
		?>
		<?= render_select('status', $status_options, ['value', 'label'], 'Status'); ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php init_tail(); ?>

<script>
  $(function(){
    initDataTable('.table-message-config', window.location.href, [1], [1]);
});
function addTemplate() {
  $('#template_id').val('');
  $('#templateForm')[0].reset();
  $('#templateModalLabel').text('Add Template');
  $('#templateModal').modal('show');
}

function editTemplate(data) {
  $('#templateModalLabel').text('Edit Template');
  $('#template_id').val(data.id);
  $('select[name="trigger_key"]').val(data.trigger_key).change();
  $('select[name="template_channel"]').val(data.template_channel).change();
  $('input[name="template_id"]').val(data.template_id);
  $('input[name="template_name"]').val(data.template_name);
  $('textarea[name="template_body"]').val(data.template_body);
  $('input[name="params_required"]').val(data.params_required);
  $('select[name="status"]').val(data.status).change();
  $('#templateModal').modal('show');
}

$(document).on('change', '.switch_status', function() {
        let status = $(this).prop('checked') ? 'Active' : 'Deactive';
        let id = $(this).data('id');
        let csrfName = $('.txt_csrfname').attr('name'); // CSRF Token Name
        let csrfHash = $('.txt_csrfname').val();        // CSRF Hash

        $.ajax({
            url: '<?= admin_url("whatsapp/update_template_status"); ?>',
            method: 'POST',
            data: {
                id: id,
                status: status,
                [csrfName]: csrfHash
            },
            success: function(resp) {
                let res = JSON.parse(resp);
                if (res.success) {
                    $('.txt_csrfname').val(res.csrfHash); // Update CSRF token
                    Swal.fire('Updated!', 'Status has been updated.', 'success');
                } else {
                    Swal.fire('Error!', 'Failed to update status.', 'error');
                }
            }
        });
    });
</script>
