<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
<div class="clearfix"></div>
						<div style="font-size: 20px; font-weight: bold; padding: 10px 20px; text-align: left; border-radius: 4px 4px 0 0;">
    <?= _l('modify_appointment') ?>
   <hr class="hr-panel-heading" />
</div>

                        

                        <form id="appointmentForm" method="post" action="<?= admin_url('client/update_appointment'); ?>">
                            <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?? '' ?>">
                            <input type="hidden" name="lead_id" value="<?= $lead_id ?? '' ?>">
                            <input type="hidden" name="client_id" value="<?= $appointment['userid'] ?? '' ?>">
                            <input type="hidden" name="call_back" value="<?= $call_back ?? '' ?>">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

                            <div class="form-section">
                                <div class="row">
								 <div class="col-md-4">
										<?= render_select(
										'branch_id',
										$branch,
										['id', 'name'],
										_l('branch') . '*',
										isset($appointment['branch_id']) ? $appointment['branch_id'] : ($branch['groupid'] ?? ''),
										['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']
									) ?>
									</div>
								  <!-- 📅 Appointment Date -->
								  <div class="col-md-4">
									<div class="form-group">
									  <label class="form-label"><span style="color:red;">*</span> <?= _l('appointment_date') ?></label>
									  <?php
										$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
										?>
									  <input type="datetime-local" class="form-control" name="appointment_date" required
											 value="<?= isset($appointment['appointment_date']) ? date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])) : '' ?>" min="<?php echo $now; ?>">
									</div>
								  </div>

								  <!-- 👨‍⚕️ Assign Doctor -->
								  <div class="col-md-4">
									<div class="form-group">
									  <?= render_select(
										'enquiry_doctor_id',
										$assign_doctor,
										['staffid', ['firstname', 'lastname']],
										'<span style="color:red;">*</span> '._l('assign_doctor'),
										$appointment['enquiry_doctor_id'] ?? '',
										['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']
									  ) ?>
									</div>
								  </div>

								  <!-- 💊 Treatment -->
								  <div class="col-md-4">
									<div class="form-group">
									  <label for="itemSelect"><span style="color:red;">*</span> <?= _l('treatment'); ?></label>
									  <select name="treatment_id" class="form-control selectpicker" data-live-search="true" id="itemSelect" required>
										<option value=""></option>
										<?php foreach ($items as $group_id => $_items) {
										  if (isset($_items[0]['group_name']) && $_items[0]['group_name'] == 'Package') {
											foreach ($_items as $item) { ?>
											  <option value="<?= e($item['id']); ?>" <?= isset($appointment['treatment_id']) && $appointment['treatment_id'] == $item['id'] ? 'selected' : '' ?>>
												<?= e($item['description']); ?>
											  </option>
											<?php }
										  }
										} ?>
									  </select>
									</div>
								  </div>
								  <div class="col-md-4">
									  <div class="form-group">
									  <label><span style="color: #f00">*</span> <?= _l('appointment_type'); ?></label>
										  <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true" required>
											<option value=""></option>
											<?php foreach ($appointment_type as $app): ?>
											  <option value="<?= $app['appointment_type_id']; ?>" <?= isset($appointment['appointment_type_id']) && $appointment['appointment_type_id'] == $app['appointment_type_id'] ? 'selected' : '' ?>><?= $app['appointment_type_name']; ?></option>
											<?php endforeach; ?>
										  </select>
									  </div>
									</div>

								  <!-- 📝 Remarks -->
								  <div class="col-md-8">
									<div class="form-group">
									  <label class="form-label"><span style="color:red;">*</span> <?= _l('remarks') ?></label>
									  <textarea class="form-control" name="remarks" placeholder="<?= _l('enter_remarks') ?>" rows="3" required><?= htmlspecialchars($appointment['remarks'] ?? '') ?></textarea>
									</div>
								  </div>

								  <!-- ✅ Submit Button -->
								  <div class="col-md-4 d-flex align-items-end">
									<div class="form-group w-100">
									  <button type="submit" class="btn btn-primary w-100" id="appointmentSubmitBtn" style="margin-top: 45px">
										<?= _l('submit'); ?>
									  </button>
									</div>
								  </div>
								</div>

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
    $('#appointmentForm').on('submit', function () {
        $('#appointmentSubmitBtn').prop('disabled', true).text('<?= _l('processing') ?>...');
    });
</script>
