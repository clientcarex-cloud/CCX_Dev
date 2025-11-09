<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('create_new_token'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>

                        <?php echo form_open(admin_url('token_system/create_token')); ?>
                        <div class="row">
                            <!-- Column 1: Patient ID -->
                            <div class="col-md-4">
                                <div class="form-group">
                                     <?php
                                    $selected = "";
                                   echo render_select(
											'patient_id',
											$patients,
											['userid', 'company'],
											'patient_id',
											$selected,
											[
												'data-none-selected-text' => _l('dropdown_non_selected_tex'),
												'required' => 'required'
											]
										);

                                    ?>
                                </div>
                            </div>

                            <!-- Column 2: Doctor ID -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php
                                    $selected = "";
                                    echo render_select('doctor_id', $doctors, ['staffid', ['firstname', 'lastname']], 'doctor_id', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']);
                                    ?>
                                </div>
                            </div>

                            <!-- Column 3: Token Date (auto-generated) -->
                            <!--<div class="col-md-4">
                                <div class="form-group">
                                    <label for="date"><?php echo _l('token_date'); ?></label>
                                    <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>-->
							<div class="col-md-4"><br>	
								<button type="submit" class="btn btn-primary"><?php echo _l('save_token'); ?></button>
							</div>
                        </div>

                        <div class="row">
                            <!-- Column 1: Status -->
                            <!--<div class="col-md-4">
                                <div class="form-group">
                                    <label for="status"><?php echo _l('Status'); ?></label>
                                    <select class="form-control" name="status" required>
                                        <option value="Pending"><?php echo _l('Pending'); ?></option>
                                        <option value="Serving"><?php echo _l('Serving'); ?></option>
                                        <option value="Completed"><?php echo _l('Completed'); ?></option>
                                        <option value="Recall"><?php echo _l('Recall'); ?></option>
                                        <option value="Expired"><?php echo _l('Expired'); ?></option>
                                        <option value="Canceled"><?php echo _l('Canceled'); ?></option>
                                        <option value="No Show"><?php echo _l('No Show'); ?></option>
                                        <option value="Ready"><?php echo _l('Ready'); ?></option>
                                        <option value="Delayed"><?php echo _l('Delayed'); ?></option>
                                    </select>
                                </div>
                            </div>-->

                            <!-- Column 2 (empty for space) -->
                            <div class="col-md-4">
                                <!-- Empty Column to keep 3 columns per row -->
                            </div>

                            <!-- Column 3 (empty for space) -->
                            <div class="col-md-4">
                                <!-- Empty Column to keep 3 columns per row -->
                            </div>
                        </div>

                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
