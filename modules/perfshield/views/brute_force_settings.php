<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
        <div class="panel_s">
	        <div class="panel-body">
	        	<!-- Horizontal tabs : start -->
	        	<div class="horizontal-scrollable-tabs panel-full-width-tabs">
				    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
				    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
		        	<div class="horizontal-tabs">
		        		<ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
		        			<li role="presentation" class="active">
		        				<a href="#brute_force_settings" aria-controls="brute_force_settings" role="tab" data-toggle="tab">
		        					<i class="fa-solid fa-vault"></i>
		        					<?php echo _l('brute_force_settings'); ?>
		        				</a>
		        			</li>
		        			<li role="presentation" class="">
		        				<a href="#blacklist_settings" aria-controls="blacklist_settings" role="tab" data-toggle="tab">
		        					<i class="fa-solid fa-user-secret"></i>
		        					<?php echo _l('blacklist_ip_email'); ?>
		        				</a>
		        			</li>
		        			<li role="presentation" class="">
		        				<a href="#login_expiry_for_staff" aria-controls="login_expiry_for_staff" role="tab" data-toggle="tab">
		        					<i class="fa-solid fa-stopwatch"></i>
		        					<?php echo _l('login_expiry_for_staff'); ?>
		        				</a>
		        			</li>
		        			<li role="presentation" class="">
		        				<a href="#single_session_settings" id="single_session_settings_tab" aria-controls="single_session_settings" role="tab" data-toggle="tab">
		        					<i class="fa-solid fa-globe"></i>
		        					<?php echo _l('single_session_settings'); ?>
		        				</a>
		        			</li>
						</ul>
					</div>
				</div>
				<!-- Horizontal tabs : over -->
				<div class="tab-content">
					<!-- Brute force settings : start -->
					<div role="tabpanel" class="tab-pane active" id="brute_force_settings">
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-warning" font-medium="">
									<p><?php echo _l('settings_for_only_staff_login') ?></p>
								</div>
							</div>
						</div>
						<?php echo form_open(admin_url('perfshield/brute_force_settings'), ['class' => 'form-inline', 'id' => 'bruteForceSettingsForm'], []); ?>
							<!-- max retries -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="max_retries">
								    	<small class="req text-danger">* </small>
								    	<i class="fa-regular fa-circle-question" data-title="<?php echo _l('max_retries_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('max_retries') ?>
								    </label> <br>
								    <small class="text-muted"><?php echo _l('max_failed_attempts_allowed_before_lockout') ?></small>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="max_retries" name="settings[max_retries]" min="1" max="10" required value="<?php echo get_option('max_retries') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- lockout time -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="lockout_time">
								    	<small class="req text-danger">* </small>
								    	<i class="fa-regular fa-circle-question" data-title="<?php echo _l('lockout_time_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('lockout_time') ?>
								    </label>
								    <small class="text-muted">(<?php echo _l('in_minutes') ?>)</small>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="lockout_time" name="settings[lockout_time]" min="5" max="60" required value="<?php echo get_option('lockout_time') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- Max lockouts -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="max_lockouts">
								    	<small class="req text-danger">* </small>
								    	<i class="fa-regular fa-circle-question" data-title="<?php echo _l('max_lockouts_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('max_lockouts') ?>
								    </label>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="max_lockouts" name="settings[max_lockouts]" min="1" max="10" required value="<?php echo get_option('max_lockouts') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- Extend Lockout -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="extend_lockout">
								    	<small class="req text-danger">* </small>
								    	<i class="fa-regular fa-circle-question" data-title="<?php echo _l('extend_lockout_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('extend_lockout') ?>
								    	<small class="text-muted">(<?php echo _l('in_hours') ?>)</small>
								    </label> <br>
									<small class="text-muted"><?php echo _l('extend_lockout_time_after_max_lockouts') ?></small>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="extend_lockout" name="settings[extend_lockout]" min="5" max="240" required value="<?php echo get_option('extend_lockout') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- Reset Retries -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="reset_retries">
								    	<small class="req text-danger">* </small>
								    	<i class="fa-regular fa-circle-question" data-title="<?php echo _l('reset_retries_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('reset_retries') ?>
								    	<small class="text-muted">(<?php echo _l('in_hours') ?>)</small>
								    </label>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="reset_retries" name="settings[reset_retries]" min="1" max="48" required value="<?php echo get_option('reset_retries') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- Email Notification -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="email_notification_after_no_of_lockouts">
								    	<small class="req text-danger">* </small>
										<i class="fa-regular fa-circle-question" data-title="<?php echo _l('email_notification_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('email_notification') ?>
								    	<small class="text-muted"><?php echo _l('after') ?> (<?php echo _l('no_of_lockouts') ?>) <?php echo _l('lockouts') ?></small>
								    </label> <br>
									<small class="text-muted"><?php echo _l('0_to_disable_email_notifications') ?></small>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="email_notification_after_no_of_lockouts" name="settings[email_notification_after_no_of_lockouts]" min="0" max="10" required value="<?php echo get_option('email_notification_after_no_of_lockouts') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- over -->
							<!-- User inactivity timeout -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="user_inactivity">
								    	<small class="req text-danger">* </small>
										<i class="fa-regular fa-circle-question" data-title="<?php echo _l('user_inactivity_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('user_inactivity') ?>
								    	<small class="text-muted">(<?php echo _l('in_minutes') ?>)</small>
								    </label> <br>
								    <small class="text-muted"><?php echo _l('user_inactivity_description') ?></small>
								</div>
								<div class="col-md-4 col-sm-6">
								    <input type="number" class="form-control mright5" id="user_inactivity" name="settings[user_inactivity]" min="0" max="240" required value="<?php echo get_option('user_inactivity') ?>">
								</div>
								<div class="col-md-4"></div>
							</div>
							<!-- Over -->
							<div class="row mbot20 mleft10">
								<div class="col-md-4 col-sm-6 mtop5">
								    <label for="send_mail_if_ip_is_different">
								    	<small class="req text-danger">* </small>
										<i class="fa-regular fa-circle-question" data-title="<?php echo _l('send_email_notification_to_admin_tooltip') ?>" data-toggle="tooltip"></i>
								    	<?php echo _l('send_mail_if_ip_is_different') ?>
								    </label> <br>
								</div>
								<div class="col-md-4 col-sm-6">
									<label class="radio-inline">
								     	<input type="radio" name="settings[send_mail_if_ip_is_different]" value="1" <?= get_option('send_mail_if_ip_is_different') == '1' ? 'checked' : '' ?>>
								     	<?php echo _l('yes') ?>
								    </label>
								    <label class="radio-inline">
								     	<input type="radio" name="settings[send_mail_if_ip_is_different]" value="0" <?= get_option('send_mail_if_ip_is_different') == '0' ? 'checked' : '' ?>>
								     	<?php echo _l('no') ?>
								    </label>
								</div>
								<div class="col-md-4"></div>
							</div>
							<div class="clearfix"></div>
							<?php if (has_permission('perfshield', '', 'create')) { ?>
								<hr class="hr-panel-heading"/>
								<button class="btn btn-primary pull-right"><?php echo _l('save_settings') ?></button>
							<?php } ?>
						<?php echo form_close(); ?>
					</div>
					<!-- Brute force settings : over -->
					<!-- Blacklist IP/Email settings : start -->
					<div role="tabpanel" class="tab-pane" id="blacklist_settings">
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-warning" font-medium="">
									<p><?php echo _l('settings_for_only_staff_login') ?></p>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="panel_s">
									<div class="panel-body">
										<?php echo form_open('', ['class' => '', 'id' => 'blacklist_ip_form'], ['type' => 'ip']); ?>
											<div class="mbot20">
												<small class="req"><span class=text-danger> * </<span></small>
												<label for="blacklist_ip"><?php echo _l('blacklist_ip') ?></label>
												<small class="text-muted"><?php echo _l('one_ip_or_ip_range_per_line') ?></small>
												<div class="blacklist_ip_section">
												    <div class="blacklist_ip_row mbot15" id="blacklist_ip_row_0">
												    	<div class="row">
													    	<div class="col-md-10">
							                            		<?php echo render_input('blacklist_ip[0]', '', '', 'text', ['required' => true], [], 'mbot5', "blacklist_ip"); ?>
													    	</div>
													    	<div class="col-md-2">
														 		<button class="btn btn-success add_blacklist_ip_row" type="button"><i class="fa fa-plus"></i></button>
																<button class="btn btn-danger remove_blacklist_ip_row hidden" data-count="0"><i class="fa fa-times"></i></button>
													    	</div>
												    	</div>
							                        </div>
												</div>
											</div>
											<?php if (has_permission('perfshield', '', 'create')) { ?>
											<button class="btn btn-sm btn-primary pull-right" id="blacklist_ip_form_submit_btn"><?php echo _l('add_ip_to_blacklist') ?></button>
											<?php } ?>
										<?php echo form_close(); ?>
										<div class="clearfix"></div>
										<hr class=""/>
										<h4 class="mbot30"><?php echo _l('list_of_blacklist_ip') ?></h4>
										<?php echo
											render_datatable([
												_l('#'),
												_l('ip_address'),
												_l('actions'),
											], 'blacklist_ip');
										?>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="panel_s">
									<div class="panel-body">
										<?php echo form_open('', ['class' => '', 'id' => 'blacklist_email_form'], ['type' => 'email']); ?>
											<div class="mbot20">
												<small class="req"><span class=text-danger> * </<span></small>
												<label for="blacklist_email"><?php echo _l('blacklist_email') ?></label>
												<small class="text-muted"><?php echo _l('one_email_address_per_line') ?></small>
												<div class="blacklist_email_section">
												    <div class="blacklist_email_row mbot15" id="blacklist_email_row_0">
												    	<div class="row">
													    	<div class="col-md-10">
							                            		<?php echo render_input('blacklist_email[0]', '', '', 'email', ['required' => true], [], 'mbot5', "blacklist_email"); ?>
													    	</div>
													    	<div class="col-md-2">
														 		<button class="btn btn-success add_blacklist_email_row"><i class="fa fa-plus"></i></button>
																<button class="btn btn-danger remove_blacklist_email_row hidden" data-count="0"><i class="fa fa-times"></i></button>
													    	</div>
												    	</div>
							                        </div>
												</div>
											</div>
											<?php if (has_permission('perfshield', '', 'create')) { ?>
											<button class="btn btn-sm btn-primary pull-right" id="blacklist_email_form_submit_btn"><?php echo _l('add_email_to_blacklist') ?></button>
											<?php } ?>
										<?php echo form_close(); ?>
										<div class="clearfix"></div>
										<hr class=""/>
										<h4 class="mbot30"><?php echo _l('list_of_blacklist_emails') ?></h4>
										<?php echo
											render_datatable([
												_l('#'),
												_l('email_address'),
												_l('actions'),
											], 'blacklist_email');
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Blacklist IP/Email settings : over -->
					<!-- Additional settings : start -->
					<div role="tabpanel" class="tab-pane" id="login_expiry_for_staff">
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-warning" font-medium="">
									<ul>
										<li>-> <?php echo _l('settings_for_only_staff_login') ?></li>
										<li>-> <?php echo _l('cron_job_setup_required') ?></li>
									</ul>
								</div>
							</div>
						</div>
						<?php echo form_open('', ['id' => 'addStaffExpiryForm']); ?>
							<div class="mbot20">
								<div class="row">
									<?php
										echo render_select('staffid', $staff, ['staffid', ['firstname', 'lastname']], 'select_staff', '', [], [], 'col-md-5');
										echo render_date_input('expiry_date', 'expiry_date', '', [], [], 'col-md-5');
									?>
									<div class="col-md-2">
										<?php if (has_permission('perfshield', '', 'create')) { ?>
										<button class="btn btn-sm btn-primary mtop25" type="submit">
											<span class="add"><?php echo _l('set_expiry_date') ?></span>
											<span class="update hide"><?php echo _l('update_expiry_date') ?></span>
										</button>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php echo form_close(); ?>
						<div class="clearfix"></div>
						<hr class=""/>
						<h4 class="mbot30"><?php echo _l('user_expiry') ?></h4>
						<?php echo
							render_datatable([
								_l('name'),
								_l('expiry_date'),
								_l('actions'),
							], 'staff_expiry');
						?>
					</div>
					<!-- Additional settings : over -->
					<!-- Single session settings : start -->
					<div role="tabpanel" class="tab-pane" id="single_session_settings">
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-warning">
									<p><?php echo _l('settings_for_only_staff_login') ?></p>
								</div>
							</div>
						</div>
						<?php echo form_open('', ['class' => 'form-inline', 'id' => 'single-session-form'], []); ?>
						<div class="row mbot20 mleft10">
							<div class="col-md-5 col-sm-12">
							    <label for="prevent_user_from_login_more_than_once">
									<i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="<?php echo _l('prevent_user_tooltip')  ?>"></i>
							    	<?php echo _l('prevent_user_from_login_more_than_once') ?> ?
							    </label> <br>
							</div>
							<div class="col-md-5 col-sm-12">
								<label class="radio-inline">
							     	<input type="radio" name="settings[prevent_user_from_login_more_than_once]" value="1" <?= get_option('prevent_user_from_login_more_than_once') == '1' ? 'checked' : '' ?>>
							     	<?php echo _l('yes') ?>
							    </label>
							    <label class="radio-inline">
							     	<input type="radio" name="settings[prevent_user_from_login_more_than_once]" value="0" <?= get_option('prevent_user_from_login_more_than_once') == '0' ? 'checked' : '' ?>>
							     	<?php echo _l('no') ?>
							    </label>
							</div>
							<div class="col-md-2 col-sm-12"></div>
						</div>
						<div class="clearfix"></div>
						<?php if (has_permission('perfshield', '', 'create')) { ?>
						<hr class="hr-panel-heading"/>
						<button class="btn btn-primary pull-right"><?php echo _l('save_settings') ?></button>
						<?php } ?>
						<?php echo form_close(); ?>
					</div>
					<!-- Single session settings : over -->
				</div>
	        </div>
        </div>
	</div>
</div>

<?php $this->load->view('includes/edit_ip_address') ?>

<?php init_tail(); ?>

<script>
	"use strict";
	$(function () {
		$.validator.addMethod('IP4Checker', function(value) {
			return value.match(/(^(?:[0-9]{1,3}\.){3}[0-9]{1,3}-(?:[0-9]{1,3}\.){3}[0-9]{1,3})|(^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$)/);
		}, 'Invalid IP address');

		add_ip_validation();
		add_email_validation();
	});

    $(".add_blacklist_ip_row").on("click", function(event) {
        event.preventDefault();
        var total_element = $(".blacklist_ip_row").length;
        var last_id = $(".blacklist_ip_row:last").attr('id').split("_");
        var next_id = Number(last_id[3]) + 1;
        $("#blacklist_ip_row_0").clone()
        .attr('id', `blacklist_ip_row_${next_id}`)
		.html((i, OldHtml) => {
            OldHtml = OldHtml.replaceAll("blacklist_ip[0]",`blacklist_ip[${next_id}]`);
            return OldHtml;
        })
        .appendTo($(".blacklist_ip_row:last").parent());
        $(`#blacklist_ip_row_${next_id} .add_blacklist_ip_row`).remove();
		$(`#blacklist_email_row_${next_id} .form-group`).removeClass("has-error");
		$(`#blacklist_email_row_${next_id} .form-group p`).remove();
        $(`#blacklist_ip_row_${next_id} :input`).val("");
        $(`#blacklist_ip_row_${next_id} .remove_blacklist_ip_row`).removeClass('hidden').data('count', next_id);

		add_ip_validation();

    });

    $(".add_blacklist_email_row").on("click", function(event) {
        event.preventDefault();
        var total_element = $(".blacklist_email_row").length;
        var last_id = $(".blacklist_email_row:last").attr('id').split("_");
        var next_id = Number(last_id[3]) + 1;
        $("#blacklist_email_row_0").clone()
        .attr('id', `blacklist_email_row_${next_id}`)
		.html((i, OldHtml) => {
            OldHtml = OldHtml.replaceAll("blacklist_email[0]",`blacklist_email[${next_id}]`);
            return OldHtml;
        })
        .appendTo($(".blacklist_email_row:last").parent());
        $(`#blacklist_email_row_${next_id} .add_blacklist_email_row`).remove();
		console.log($(`#blacklist_email_row_${next_id} .add_blacklist_email_row .form-group`));
		$(`#blacklist_email_row_${next_id} .form-group`).removeClass("has-error");
		$(`#blacklist_email_row_${next_id} .form-group p`).remove();
        $(`#blacklist_email_row_${next_id} :input`).val("");
        $(`#blacklist_email_row_${next_id} .remove_blacklist_email_row`).removeClass('hidden').data('count',next_id);

		add_email_validation();

    });

    $(document).on('click', '.remove_blacklist_ip_row', function(event) {
        event.preventDefault();
        $(`#blacklist_ip_row_${$(this).data('count')}`).remove();

		add_ip_validation();

    });

    $(document).on('click', '.remove_blacklist_email_row', function(event) {
        event.preventDefault();
        $(`#blacklist_email_row_${$(this).data('count')}`).remove();

		add_email_validation();
    });

    initDataTable('.table-blacklist_ip', `${admin_url}perfshield/get_table_data/blacklist_ip_email/ip`);
    initDataTable('.table-blacklist_email', `${admin_url}perfshield/get_table_data/blacklist_ip_email/email`);
    initDataTable('.table-staff_expiry', `${admin_url}perfshield/get_table_data/staff_expiry`);
</script>