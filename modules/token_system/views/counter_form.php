<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l($title); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>

                        <?php echo form_open_multipart(admin_url('token_system/create_counter')); ?>
						<div class="row">
							<!-- Column 1 -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="counter_name"><?php echo _l('counter_name'); ?></label>
									<input type="text" class="form-control" name="counter_name" required>
								</div>
							</div>

							<!-- Column 2 -->
							<div class="col-md-4">
								<div class="form-group">
									<?php
									$selected = "";
									echo render_select('doctor_id', $doctors, ['staffid', ['firstname', 'lastname']], 'doctor_id', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
									?>
								</div>
							</div>

							<!-- Column 3 -->
							<div class="col-md-4">
								<div class="form-group">
									<?php
									$selected = "";
									echo render_select('display_id', $displays, ['id', ['display_name']], 'display_id', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
									?>
								</div>
							</div>
						</div>

						<div class="row">
							<!-- Column 1 -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="counter_status"><?php echo _l('counter_status'); ?></label>
									<select class="form-control" name="counter_status" required>
										<option value="Available"><?php echo _l('Available'); ?></option>
										<option value="Lunch Break"><?php echo _l('Lunch Break'); ?></option>
										<option value="Emergency"><?php echo _l('Emergency'); ?></option>
									</select>
								</div>
							</div>

							<!-- Column 2 (empty for space) -->
							<div class="col-md-4">
								<!-- Empty Column to keep 3 columns per row -->
							</div>

							<!-- Column 3 (empty for space) -->
							<div class="col-md-4">
								<!-- Empty Column to keep 3 columns per row -->
							</div>
						</div>

						<button type="submit" class="btn btn-primary"><?php echo _l('save_counter'); ?></button>
						<?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
