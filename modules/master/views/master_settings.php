<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
</style>
<div id="wrapper">
<div class="content">
<div class="row">

<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
<?php echo _l($title); ?>

</h4>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>

<?php echo form_open(admin_url('master/save_master_settings')); ?>

	<?php foreach ($results as $res): ?>
	<div class="row">
        <div class="col-md-3">
            <label><?php echo _l($res['title']); ?></label>
        </div>
        <div class="col-md-4">
            <div class="form-group">
               <?php
					$field_base_name = $res['title'];  // e.g. lead_call_log_patient_response
					$field_name = $res['multi_select'] == 1
						? $field_base_name . '[]'  // for multi-select
						: $field_base_name;        // for single-select

					$table = $res['table'];
					$option_array_raw = get_options($res['table']);
					$option_array = is_array($option_array_raw) ? $option_array_raw : [];

					// Pre-selected values from 'options' key
					$selected_values = isset($res['options']) && !empty($res['options'])
						? array_map('trim', explode(',', $res['options']))
						: [];

					// If single select, only keep first value
					if ($res['multi_select'] != 1 && !empty($selected_values)) {
						$selected_values = [$selected_values[0]];
					}

					// Hidden input only needed for multi-select to ensure empty post
					if ($res['multi_select'] == 1) {
						echo '<input type="hidden" name="' . htmlspecialchars($field_base_name) . '" value="">';
					}

					$valueField = 'name';
					$labelField = 'name';

					if ($res['table'] === 'roles') {
						$valueField = 'roleid';
						$labelField = 'name';
					}

					// Render different fields based on multi_select value
					if ($res['multi_select'] == 1) {
						
						// Render multi-select dropdown
						echo render_select(
							$field_name,
							$option_array,
							[$valueField, $labelField],
							'',
							$selected_values,
							['multiple' => true, 'data-none-selected-text' => _l('dropdown_non_selected_tex')]
						);
					} elseif ($res['multi_select'] == 2) {
						// Render a textbox input if multi_select == 2
						echo '<input type="text" class="form-control" name="' . htmlspecialchars($field_name) . '" value="' . htmlspecialchars(implode(',', $selected_values)) . '" placeholder="' . _l('enter_' . $field_base_name) . '">';
					} elseif ($res['multi_select'] == 3) {
						$option_array = [
						  ['id' => 'before_payment', 'name' => 'Before Payment'],
						  ['id' => 'after_payment', 'name' => 'After Payment'],
						  ['id' => 'none', 'name' => 'None']
						];

						echo render_select(
							$field_name,
							$option_array,
							['id', 'name'],
							'',
							$selected_values,
							['data-none-selected-text' => _l('dropdown_non_selected_tex')]
						);

					} else {
						// Handle the case where it's a single select (default behavior)
						echo render_select(
							$field_name,
							$option_array,
							[$valueField, $labelField],
							'',
							$selected_values,
							['data-none-selected-text' => _l('dropdown_non_selected_tex')]
						);
					}
					?>




            </div>
        </div>
		</div>
<?php endforeach; ?>


	<div class="col-md-4">	
		<button type="submit" class="btn btn-primary"><?php echo _l('save_setting'); ?></button>
	</div>



<?php echo form_close(); ?>


</div>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>

</body>
</html>
