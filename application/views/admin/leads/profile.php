<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="lead-wrapper<?= $openEdit == true ? ' open-edit' : ''; ?>"
    <?= isset($lead) && ($lead->junk == 1 || $lead->lost == 1) ? 'lead-is-just-or-lost' : ''; ?>>

    <?php 
	if (isset($lead)) { ?>
    <div class="tw-flex tw-items-center tw-justify-end tw-space-x-1.5">

        <?php
                       $client                  = false;
        $convert_to_client_tooltip_email_exists = '';
        if (total_rows(db_prefix() . 'contacts', ['email' => $lead->email]) > 0 && total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0) {
            $convert_to_client_tooltip_email_exists = _l('lead_email_already_exists');
            $text                                   = _l('lead_convert_to_client');
        } elseif (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id])) {
            $client = true;
        } else {
            $text = _l('lead_convert_to_client');
        }
        ?>

        <?php if ($lead_locked == false) { ?>
        <div
            class="lead-edit<?= isset($lead) ? ' hide' : ''; ?>">
            <button type="button" class="btn btn-primary lead-top-btn lead-save-btn"
                onclick="document.getElementById('lead-form-submit').click();">
                <?= _l('submit'); ?>
            </button>
        </div>
        <?php } ?>
        <?php if ($client && (staff_can('view', 'customers') || is_customer_admin(get_client_id_by_lead_id($lead->id)))) { ?>
        <a data-toggle="tooltip" class="btn btn-primary lead-top-btn lead-view" data-placement="top"
            title="<?= _l('lead_converted_edit_client_profile'); ?>"
            href="<?= admin_url('clients/client/' . get_client_id_by_lead_id($lead->id)); ?>">
            <i class="fa-regular fa-user"></i>
        </a>
        <?php } ?>
        <?php if (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0) { ?>
        <!--<a href="#" data-toggle="tooltip"
            data-title="<?= e($convert_to_client_tooltip_email_exists); ?>"
            class="btn btn-primary lead-convert-to-customer lead-top-btn lead-view"
            onclick="convert_lead_to_customer(<?= e($lead->id); ?>); return false;">
            <i class="fa-regular fa-user"></i>
            <?= e($text); ?>
        </a>-->
        <?php } ?>

        <div
            class="<?= $lead_locked == true ? ' hide' : ''; ?>">
            <a href="#" lead-edit data-toggle="tooltip"
                data-title="<?= _l('edit'); ?>"
                class="btn btn-default lead-top-btn !tw-px-3">

                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div>
		
        <div class="btn-group" id="lead-more-btn">
            <a href="#" class="btn btn-default dropdown-toggle lead-top-btn" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <?= _l('more'); ?>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="lead-more-dropdown">
                <?php if ($lead->junk == 0) {
                    if ($lead->lost == 0 && (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0)) { ?>
                <li>
                    <a href="#"
                        onclick="lead_mark_as_lost(<?= e($lead->id); ?>); return false;">
                        <i class="fa fa-mars"></i>
                        <?= _l('lead_mark_as_lost'); ?>
                    </a>
                </li>
                <?php } elseif ($lead->lost == 1) { ?>
                <li>
                    <a href="#"
                        onclick="lead_unmark_as_lost(<?= e($lead->id); ?>); return false;">
                        <i class="fa fa-smile-o"></i>
                        <?= _l('lead_unmark_as_lost'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php } ?>
                <!-- mark as junk -->
                <?php if ($lead->lost == 0) {
                    if ($lead->junk == 0 && (total_rows(db_prefix() . 'clients', ['leadid' => $lead->id]) == 0)) { ?>
                <li>
                    <a href="#"
                        onclick="lead_mark_as_junk(<?= e($lead->id); ?>); return false;">
                        <i class="fa fa fa-times"></i>
                        <?= _l('lead_mark_as_junk'); ?>
                    </a>
                </li>
                <?php } elseif ($lead->junk == 1) { ?>
                <li>
                    <a href="#"
                        onclick="lead_unmark_as_junk(<?= e($lead->id); ?>); return false;">
                        <i class="fa fa-smile-o"></i>
                        <?= _l('lead_unmark_as_junk'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php } ?>
                <?php if ((staff_can('delete', 'leads') && $lead_locked == false) || is_admin()) { ?>
                <li>
                    <a href="<?= admin_url('leads/delete/' . $lead->id); ?>"
                        class="text-danger delete-text _delete" data-toggle="tooltip" title="">
                        <i class="fa-regular fa-trash-can"></i>
                        <?= _l('lead_edit_delete_tooltip'); ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php } ?>

    <div class="clearfix no-margin"></div>

    <?php if (isset($lead)) { ?>

    <div class="row mbot15" style="margin-top:12px;">
        <hr class="no-margin" />
    </div>

    <div class="alert alert-warning hide mtop20" role="alert" id="lead_proposal_warning">
        <?= _l('proposal_warning_email_change', [_l('lead_lowercase'), _l('lead_lowercase'), _l('lead_lowercase')]); ?>
        <hr />
        <a href="#"
            onclick="update_all_proposal_emails_linked_to_lead(<?= e($lead->id); ?>); return false;"
            class="alert-link">
            <?= _l('update_proposal_email_yes'); ?>
        </a>
        <br />
        <a href="#"
            onclick="init_lead_modal_data(<?= e($lead->id); ?>); return false;"
            class="alert-link">
            <?= _l('update_proposal_email_no'); ?>
        </a>
    </div>
    <?php } ?>
    <?= form_open((isset($lead) ? admin_url('leads/lead/' . $lead->id) : admin_url('leads/lead')), ['id' => 'lead_form']); ?>
    <div class="row">
        <div class="lead-view<?= ! isset($lead) ? ' hide' : ''; ?>"
            id="leadViewWrapper">
            <div class="col-md-3 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4>
                        <?= _l('lead_info'); ?>
                    </h4>
                </div>
                <dl>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_add_edit_name'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 lead-name">
                        <?= isset($lead) && $lead->name != '' ? e($lead->name) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_title'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->title != '' ? e($lead->title) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_add_edit_email'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->email != '' ? '<a href="mailto:' . e($lead->email) . '">' . e($lead->email) . '</a>' : '-' ?>
                    </dd>
                    <!--<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_website'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->website != '' ? '<a href="' . e(maybe_add_http($lead->website)) . '" target="_blank">' . e($lead->website) . '</a>' : '-' ?>
                    </dd>-->
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('lead_add_edit_phonenumber'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1">
						<?php
						if (isset($lead) && $lead->phonenumber != '') {
							$reversed = strrev($lead->phonenumber);
							$masked = '';
							$digitCount = 0;

							for ($i = 0; $i < strlen($reversed); $i++) {
								$char = $reversed[$i];
								if (ctype_digit($char)) {
									if ($digitCount < 5) {
										$masked .= '*';
										$digitCount++;
									} else {
										$masked .= $char;
									}
								} else {
									$masked .= $char;
								}
							}

							if (staff_can('mobile_masking', 'customers')) {
									echo strrev($masked);
							} else {
									echo $lead->phonenumber;
							}
						} else {
							echo '-';
						}
						?>
					</dd>
					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('lead_alternate_number'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1">
						<?php
						if (isset($lead) && $lead->lead_alternate_number != '') {
							$reversed = strrev($lead->lead_alternate_number);
							$masked = '';
							$digitCount = 0;

							for ($i = 0; $i < strlen($reversed); $i++) {
								$char = $reversed[$i];
								if (ctype_digit($char)) {
									if ($digitCount < 5) {
										$masked .= '*';
										$digitCount++;
									} else {
										$masked .= $char;
									}
								} else {
									$masked .= $char;
								}
							}

							if (staff_can('mobile_masking', 'customers')) {
									echo strrev($masked);
							} else {
									echo $lead->lead_alternate_number;
							}
						} else {
							echo '-';
						}
						?>
					</dd>

                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_value'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->lead_value != 0 ? e(app_format_money($lead->lead_value, $base_currency->id)) : '-' ?>
                    </dd>
                    <!--<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_company'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->company != '' ? e($lead->company) : '-' ?>
                    </dd>-->
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_address'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
                        <?= isset($lead) && $lead->address != '' ? e(clear_textarea_breaks($lead->address)) : '-' ?>
                    </dd>
					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('lead_age'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
						<?= isset($lead) && $lead->lead_age != '' ? e($lead->lead_age) : '-' ?>
					</dd>

					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('lead_gender'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
						<?= isset($lead) && $lead->lead_gender != '' ? e($lead->lead_gender) : '-' ?>
					</dd>

                </dl>
            </div>
			<div class="col-md-3 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4>
                       <br>
                    </h4>
                </div>
                <dl>
                  
					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('lead_priority'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
						<?= isset($lead) && $lead->lead_priority != '' ? e($lead->lead_priority) : '-' ?>
					</dd>

					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('area'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
						<?= isset($lead) && $lead->area != '' ? e($lead->area) : '-' ?>
					</dd>

					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
						<?= _l('languages'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-1 tw-whitespace-pre-line">
						<?= isset($lead) && $lead->languages != '' ? e(ucfirst($lead->languages)) : '-' ?>
					</dd>

                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_city'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->city != '' ? e($lead->city_name) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_state'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->state != '' ? e($lead->state_name) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_country'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->country != 0 ? e(get_country($lead->country)->short_name) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_zip'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->pincode_name != '' ? e($lead->pincode_name) : '-' ?>
                    </dd>
				</dl>
            </div>
            <div class="col-md-3 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4>
                        <?= _l('lead_general_info'); ?>
                    </h4>
                </div>
                <dl>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500 no-mtop">
                        <?= _l('lead_add_edit_status'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-2 mbot15">
                        <?php if (isset($lead)) {
                            echo $lead->status_name != '' ? ('<span class="lead-status-' . e($lead->status) . ' label' . (empty($lead->color) ? ' label-default' : '') . '" style="color:' . e($lead->color) . ';border:1px solid ' . adjust_hex_brightness($lead->color, 0.4) . ';background: ' . adjust_hex_brightness($lead->color, 0.04) . ';">' . e($lead->status_name) . '</span>') : '-';
                        } else {
                            echo '-';
                        } ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_add_edit_source'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= isset($lead) && $lead->source_name != '' ? e($lead->source_name) : '-' ?>
                    </dd>
                    <?php if (! is_language_disabled()) { ?>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('localization_default_language'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= isset($lead) && $lead->default_language != '' ? e(ucfirst($lead->default_language)) : _l('system_default_string') ?>
                    </dd>
                    <?php } ?>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_nature'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= isset($lead) && $lead->lead_nature != 0 ? e($lead->lead_nature) : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_add_edit_assigned'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= isset($lead) && $lead->assigned != 0 ? e(get_staff_full_name($lead->assigned)) : '-' ?>
                    </dd>
                    <!--<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('tags'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot10">
                        <?php if (isset($lead)) {
                            $tags = get_tags_in($lead->id, 'lead');
                            if (count($tags) > 0) {
                                echo render_tags($tags);
                                echo '<div class="clearfix"></div>';
                            } else {
                                echo '-';
                            }
                        } ?>
                    </dd>-->
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('leads_dt_datecreated'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->dateadded != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . e(_dt($lead->dateadded)) . '">' . e(time_ago($lead->dateadded)) . '</span>' : '-' ?>
                    </dd>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('leads_dt_last_contact'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= isset($lead) && $lead->lastcontact != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . e(_dt($lead->lastcontact)) . '">' . e(time_ago($lead->lastcontact)) . '</span>' : '-' ?>
                    </dd>
                    <!--<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_public'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?php if (isset($lead)) {
                            if ($lead->is_public == 1) {
                                echo _l('lead_is_public_yes');
                            } else {
                                echo _l('lead_is_public_no');
                            }
                        } else {
                            echo '-';
                        } ?>
                    </dd>-->
                    <?php if (isset($lead) && $lead->from_form_id != 0) { ?>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('web_to_lead_form'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= e($lead->form_data->name); ?>
                    </dd>
                    <?php } ?>
					
					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('refer_type'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?= e($lead->refer_type); ?>
                    </dd>
					
					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('refer_by'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 mbot15">
                        <?php if($lead->refer_type == "staff"){
							echo get_staff_full_name($lead->refer_id);
						}else{
							echo get_patient_name($lead->refer_id);
						} ?>
                    </dd>
                </dl>
            </div>
			
			
			<div class="col-md-3 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4>
                        <?= _l('lead_with_doctor'); ?>
                    </h4>
                </div>
                <dl>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500 no-mtop">
                        <?= _l('doctor_name'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-2 mbot15">
						<?php					
						if (!empty($lead_with_doctor) && isset($lead_with_doctor->enquiry_doctor_id) && $lead_with_doctor->enquiry_doctor_id != 0) {
							echo e(get_staff_full_name($lead_with_doctor->enquiry_doctor_id));
						}
						?>

						
                    </dd>

					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500 no-mtop">
						<?= _l('branch'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-2 mbot15">
						<?= (!empty($lead) && isset($lead->branch_name)) ? e($lead->branch_name) : ''; ?>
					</dd>

					<dt class="lead-field-heading tw-font-normal tw-text-neutral-500 no-mtop">
						<?= _l('treatment'); ?>
					</dt>
					<dd class="tw-text-neutral-900 tw-mt-2 mbot15">
					
						<?= (!empty($lead_with_doctor) && isset($lead_with_doctor->description)) ? e($lead_with_doctor->description) : ''; ?>
					</dd>

                    
                </dl>
            </div>
            <div class="col-md-3 col-xs-12 lead-information-col">
                <?php if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'leads', 'active' => 1]) > 0 && isset($lead)) { ?>
                <div class="lead-info-heading">
                    <h4>
                        <?= _l('custom_fields'); ?>
                    </h4>
                </div>
                <dl>
                    <?php foreach (get_custom_fields('leads') as $field) { ?>
                    <?php $value = get_custom_field_value($lead->id, $field['id'], 'leads'); ?>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500 no-mtop">
                        <?= e($field['name']); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1 tw-break-words">
                        <?= $value != '' ? $value : '-' ?>
                    </dd>
                    <?php } ?>
                    <?php } ?>
                </dl>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <dl>
                    <dt class="lead-field-heading tw-font-normal tw-text-neutral-500">
                        <?= _l('lead_description'); ?>
                    </dt>
                    <dd class="tw-text-neutral-900 tw-mt-1">
                        <?= process_text_content_for_display((isset($lead) && $lead->description != '' ? $lead->description : '-')); ?>
                    </dd>
                </dl>
            </div>
        </div>
        <div class="clearfix"></div>
        <div
            class="lead-edit<?= isset($lead) ? ' hide' : ''; ?>">
            <!--<div class="col-md-3">
                <?php
					$selected = '';

					// Priority 1: if lead is set, use lead->status
					if (isset($lead)) {
						$selected = $lead->status;
					} else {
						// Priority 2: set default to "Enquiry"
						foreach ($statuses as $status) {
							if (strcasecmp(trim($status['name']), 'Enquiry') === 0) {
								$selected = $status['id'];
								break;
							}
						}
					}

					// Filter statuses to include only "Enquiry" and "New"
					$filtered_statuses = [];
					foreach ($statuses as $status) {
						if (in_array(strtolower($status['name']), ['enquiry', 'new'])) {
							$filtered_statuses[] = $status;
						}
					}

					// Render the select
					echo render_leads_status_select($filtered_statuses, $selected, 'lead_add_edit_status');
					?>



            </div>-->
            <div class="col-md-4">
                <?= render_leads_source_select($sources, (isset($lead) ? $lead->source : get_option('leads_default_source')), 'lead_add_edit_source'); ?>
            </div>
			<div class="col-md-4">
			<?php
				$lead_nature_options = [
					['id' => 'Incoming', 'name' => 'Incoming'],
					['id' => 'Outgoing', 'name' => 'Outgoing'],
				];

				$selected_nature = isset($lead) && $lead->lead_nature !== '' ? $lead->lead_nature : 'Incoming';

				echo render_select(
					'lead_nature',
					$lead_nature_options,
					['id', 'name'],
					'<span class="text-danger">*</span> ' . _l('lead_nature'),
					$selected_nature,
					[
						'data-none-selected-text' => _l('dropdown_non_selected_tex'),
						'required' => 'required'
					]
				);
				?>


		</div>
		<div class="col-md-4">
		  <?php
			$assigned_attrs = [];

			// Disable if $lead is not set
			if (!isset($lead)) {
				$assigned_attrs['disabled'] = true;
			}

			// Otherwise, apply additional permission-based disabling
			$selected = (isset($lead) ? $lead->assigned : get_staff_user_id());

			if (
				isset($lead) &&
				$lead->assigned == get_staff_user_id() &&
				$lead->addedfrom != get_staff_user_id() &&
				!is_admin($lead->assigned) &&
				staff_cant('view', 'leads')
			) {
				$assigned_attrs['disabled'] = true;
			}

			echo render_select('assigned', $members, ['staffid', ['firstname', 'lastname']], 'lead_add_edit_assigned', $selected, $assigned_attrs);
		  ?>
		</div>

            
            <div class="clearfix"></div>
            <!--<hr class="mtop5 mbot10" />
            <div class="col-md-12">
                <div class="form-group no-mbot" id="inputTagsWrapper">
                    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                        <?= _l('tags'); ?></label>
                    <input type="text" class="tagsinput" id="tags" name="tags"
                        value="<?= isset($lead) ? prep_tags_input(get_tags_in($lead->id, 'lead')) : ''; ?>"
                        data-role="tagsinput">
                </div>
            </div>-->
            <div class="clearfix"></div>
            <hr class="no-mtop mbot15" />
     
<div class="row" style="padding: 10px;">
    <div class="col-md-4">
        <?php $value = (isset($lead) ? $lead->phonenumber : ''); ?>
<div class="form-group">
    <label for="phonenumber"><?= _l('lead_add_edit_phonenumber'); ?> <span class="text-danger">*</span></label>
    <div class="row no-gutters">
        <div class="col-md-3 pr-0">
            <select name="calling_code" id="calling_code" class="form-control selectpicker" data-live-search="true" required>
                <option value=""><?= _l('select_country_code'); ?></option>
                <?php foreach (get_all_countries() as $country): ?>
                    <option value="<?= $country['calling_code']; ?>"
                            data-phone-length="<?= ($country['calling_code'] == '91') ? 10 : (($country['calling_code'] == '966') ? 9 : 10); ?>"
                            <?= (isset($lead) && $lead->country == $country['country_id']) ? 'selected' : (($country['short_name'] == 'India') ? 'selected' : ''); ?>>
                        +<?= $country['calling_code']; ?> (<?= $country['short_name']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-9 pl-1 position-relative">
            <input type="text"
                   name="phonenumber"
                   value="<?= isset($lead) ? $lead->phonenumber : ''; ?>"
                   id="phonenumber_input"
                   class="form-control contact_number_search"
                   maxlength="10"
                   minlength="10"
                   pattern="\d{10}"
                   autocomplete="off"
                   oninput="checkPhoneNumberSuggestions(this)"
                   required
            >
            <div id="phoneSuggestions" class="list-group"
                 style="position:absolute; top:100%; left:0; right:0; z-index:9999; display:none;"></div>
            <small class="text-danger phone-error" style="display:none;"></small>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    function updatePhoneLength() {
        const selected = $('#calling_code option:selected');
        const phoneLength = selected.data('phone-length') || 10;
        const $input = $('#phonenumber_input');

        $input.attr('maxlength', phoneLength);
        $input.attr('minlength', phoneLength);
        $input.attr('pattern', `\\d{${phoneLength}}`);
    }

    // Initialize on page load
    updatePhoneLength();

    // Update when country changes
    $('#calling_code').on('change', function() {
        updatePhoneLength();
    });

    // Existing phone suggestion logic
    let existingLeads = [];
    function checkPhoneNumberSuggestions(input) {
        const phone = $(input).val();
        const $suggestions = $('#phoneSuggestions');
        const $error = $('.phone-error');
        const phoneLength = $(input).attr('maxlength');

        if(phone.length == phoneLength){
            $.ajax({
                url: admin_url + 'leads/search_phone_matches',
                method: 'POST',
                dataType: 'json',
                data: { phone: phone },
                success: function(data){
                    existingLeads = data;
                    $suggestions.empty().hide();
                    $error.hide();

                    if(Array.isArray(data) && data.length > 0){
                        data.forEach(function(item){
                            const text = `${item.phonenumber} - ${item.name}`;
                            $suggestions.append(`<a href="${admin_url}leads/index/${item.id}" class="list-group-item list-group-item-action">${text}</a>`);
                        });
                        $suggestions.show();
                    }
                }
            });
        } else {
            $suggestions.hide();
            $error.hide();
            existingLeads = [];
        }
    }

    // Prevent duplicate phone submission
    $('form').on('submit', function(e){
        const phone = $('#phonenumber_input').val();
        const $error = $('.phone-error');
        const match = existingLeads.find(lead => lead.phonenumber === phone);

        if(match){
            e.preventDefault();
            $error.text("This phone number already exists in leads.").show();
            $('#phonenumber_input').focus();
        }
    });
});
</script>

<style>
#phoneSuggestions {
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
}
#phoneSuggestions a {
    padding: 8px 12px;
    display: block;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
#phoneSuggestions a:hover {
    background: #f2f2f2;
}
</style>
<div class="form-group">
    <label for="lead_alternate_number"><?= _l('lead_alternate_number'); ?> <span class="text-danger">*</span></label>
    <div class="row no-gutters">
        <div class="col-md-3 pr-0">
            <select name="calling_code_alternate" id="calling_code_alternate" class="form-control selectpicker" data-live-search="true" required>
                <option value=""><?= _l('select_country_code'); ?></option>
                <?php foreach (get_all_countries() as $country): ?>
                    <option value="<?= $country['calling_code']; ?>"
                            data-phone-length="<?= ($country['calling_code'] == '91') ? 10 : (($country['calling_code'] == '966') ? 9 : 10); ?>"
                            <?= (isset($lead) && $lead->country == $country['country_id']) ? 'selected' : (($country['short_name'] == 'India') ? 'selected' : ''); ?>>
                        +<?= $country['calling_code']; ?> (<?= $country['short_name']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        $altNumber = '';
        if(isset($lead)){
            $raw = (string)$lead->lead_alternate_number;
            $altNumber = strlen($raw) > 3 ? ltrim(substr($raw, 3)) : '';
        }
        ?>
        <div class="col-md-9 pl-1 position-relative">
            <input type="text"
                   name="lead_alternate_number"
                   id="lead_alternate_number"
                   value="<?= $altNumber; ?>"
                   class="form-control"
                   maxlength="10"
                   minlength="10"
                   pattern="\d{10}"
                   autocomplete="off"
                   required
            >
            <small class="text-danger phone-error-alt" style="display:none;"></small>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    // Function to update phone input length dynamically
    function updatePhoneLength(inputSelector, selectSelector) {
        const selected = $(`${selectSelector} option:selected`);
        const phoneLength = selected.data('phone-length') || 10;
        const $input = $(inputSelector);
        $input.attr('maxlength', phoneLength);
        $input.attr('minlength', phoneLength);
        $input.attr('pattern', `\\d{${phoneLength}}`);
    }

    // Initialize phone lengths
    updatePhoneLength('#phonenumber_input', '#calling_code');
    updatePhoneLength('#lead_alternate_number', '#calling_code_alternate');

    // Update when country changes
    $('#calling_code').on('change', function(){
        updatePhoneLength('#phonenumber_input', '#calling_code');
    });
    $('#calling_code_alternate').on('change', function(){
        updatePhoneLength('#lead_alternate_number', '#calling_code_alternate');
    });

    // Optional: Add real-time validation for alternate number
    $('#lead_alternate_number').on('input', function(){
        const phoneLength = $(this).attr('maxlength');
        const $error = $('.phone-error-alt');
        if($(this).val().length !== parseInt(phoneLength)){
            $error.text(`Phone number should be ${phoneLength} digits`).show();
            $(this).addClass('is-invalid');
        } else {
            $error.hide();
            $(this).removeClass('is-invalid');
        }
    });

});
</script>

        

<?php $value = (isset($lead) ? $lead->name : ''); ?>
       <?= render_input('name', 'lead_add_edit_name', $value, 'text', ['required' => false]); ?>


        <?php $value = (isset($lead) ? $lead->lead_age : ''); ?>
        <?= render_input('lead_age', 'lead_input_add_edit_age', $value, 'text', [
    'maxlength' => '3',
    'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);"
]); ?>


        <?= render_select(
            'lead_gender',
            [
                ['id' => 'Male', 'name' => 'Male'],
                ['id' => 'Female', 'name' => 'Female'],
                ['id' => 'Other', 'name' => 'Other']
            ],
            ['id', 'name'],
            'lead_input_gender',
            (isset($lead) ? $lead->lead_gender : ''),
            [
                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
            ],
            [],
            ''
        ); ?>

        <?php $value = (isset($lead) ? $lead->email : ''); ?>
        <?= render_input('email', 'lead_input_add_edit_email', $value, 'email'); ?>

        <?php $value = (isset($lead) ? $lead->area : ''); ?>
        <?= render_textarea('area', 'area', $value, ['rows' => 1, 'style' => 'height:36px;font-size:100%;']); ?>
    </div>

    <div class="col-md-4">
        <?php
       
       // echo render_select('treatment', $treatments, ['treatment_id', ['treatment_name']], 'lead_treatment', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
		 $selected = isset($lead_with_doctor) ? $lead_with_doctor->treatment_id : '';
        ?>
		<!--<label for="lead_value"><?= _l('lead_treatment'); ?></label>
		<select name="treatment" class="form-control selectpicker" data-live-search="true" id="itemSelect">
			<option value=""></option>
			<?php foreach ($items as $group_id => $_items) { 
				$group_name = $_items[0]['group_name'] ?? '';
				if ($group_name == "Consultation Fee") {
					continue;
				}
			foreach ($_items as $item) { ?>
				<option value="<?= e($item['id']); ?>">
					<?= e($item['description']); ?>
				</option>
				<?php } ?>
			
			<?php } ?>
		</select>
		<br><br>-->  
		
        <?php $value = (isset($lead) ? $lead->title : ''); ?>
        <?= render_input('title', 'lead_title', $value); ?>

        <div class="form-group">
		
            <label for="lead_value"><?= _l('lead_value'); ?></label>
            <div class="input-group" data-toggle="tooltip" title="<?= _l('lead_value_tooltip'); ?>">
                <input type="number" class="form-control" name="lead_value"
                    value="<?= isset($lead) ? $lead->lead_value : ''; ?>">
                <div class="input-group-addon">
                    <?= e($base_currency->symbol); ?>
                </div>
            </div>
        </div>

        <?= render_select(
            'lead_priority',
            [
                ['id' => 'High', 'name' => 'High'],
                ['id' => 'Medium', 'name' => 'Medium'],
                ['id' => 'Low', 'name' => 'Low']
            ],
            ['id', 'name'],
            'lead_priority',
            (isset($lead) ? $lead->lead_priority : ''),
            ['data-none-selected-text' => _l('dropdown_non_selected_tex')]
        ); ?>

        <?php
// Define Indian languages
$indian_languages = [
    'Assamese'   => 'Assamese',
    'Bengali'    => 'Bengali',
    'Bodo'       => 'Bodo',
    'Dogri'      => 'Dogri',
    'Gujarati'   => 'Gujarati',
    'Hindi'      => 'Hindi',
    'Kannada'    => 'Kannada',
    'Kashmiri'   => 'Kashmiri',
    'Konkani'    => 'Konkani',
    'Maithili'   => 'Maithili',
    'Malayalam'  => 'Malayalam',
    'Manipuri'   => 'Manipuri',
    'Marathi'    => 'Marathi',
    'Nepali'     => 'Nepali',
    'Odia'       => 'Odia',
    'Punjabi'    => 'Punjabi',
    'Sanskrit'   => 'Sanskrit',
    'Santali'    => 'Santali',
    'Sindhi'     => 'Sindhi',
    'Tamil'      => 'Tamil',
    'Telugu'     => 'Telugu',
    'English'     => 'English',
    'Urdu'       => 'Urdu',
];


// Map into format required by render_select
$language_options = array_map(function($key, $value) {
    return ['lang_key' => $key, 'lang_name' => $value];
}, array_keys($indian_languages), $indian_languages);

// Get selected values as array
$selected_languages = isset($lead->languages) ? explode(',', $lead->languages) : [];

echo render_select(
    'languages[]',
    $language_options,
    ['lang_key', ['lang_name']],
    'select_language',
    $selected_languages,
    ['multiple' => true]
);
?>
<?php $value = (isset($lead) ? $lead->address : ''); ?>
        <?= render_textarea('address', 'lead_address', $value, ['rows' => 1, 'style' => 'height:36px;font-size:100%;']); ?>

        <?php
		if(!isset($lead)){
			$allowed_status_names = [
				'Enquiry',
				'New',
				'Paid Appointment',
				'On Appointment',
				'No Feedback',
				'Call back',
				'No Response',
			];
			$selected = isset($lead_with_doctor) ? $lead_with_doctor->patient_response_id : '';

			// Filter $statuses to include only allowed names
			$filtered_statuses = array_filter($statuses, function ($status) use ($allowed_status_names) {
				return in_array($status['name'], $allowed_status_names);
			});

			// Re-index the filtered array
			$filtered_statuses = array_values($filtered_statuses);

			// If $selected is empty, select the ID of "New"
			if (empty($selected)) {
				foreach ($filtered_statuses as $status) {
					if ($status['name'] === 'New') {
						$selected = $status['id'];
						break;
					}
				}
			}

			// Render the select
			echo render_select(
				'patient_response_id',
				$filtered_statuses,
				['id', ['name']],
				'lead_patient_response',
				$selected,
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex'),
					'required' => 'true'
				],
				[],
				'',
				'required'
			);
		}
		
		?>



        <!--<div class="checkbox-inline checkbox checkbox-primary">
            <br>
            <input type="checkbox" id="lead_with_doctor"
                <?= (!empty($lead_with_doctor) && !empty($lead_with_doctor->staffid)) ? 'checked' : ''; ?>>
            <label for="lead_with_doctor"><?= _l('book_appointment'); ?></label>
            <br><br>
        </div>-->

        
    </div>
	
    <div class="col-md-4">
        <?php
		$countries = get_all_countries();
		$customer_default_country = get_option('customer_default_country');

		// If $lead->country is set, use it. Else use system default. If still not set, fallback to India.
		$selected = isset($lead) && !empty($lead->country)
			? $lead->country
			: (!empty($customer_default_country) ? $customer_default_country : '');

		// If still empty, find India in the country list and set its country_id
		if (empty($selected)) {
			foreach ($countries as $country) {
				if (strtolower($country['short_name']) === 'india') {
					$selected = $country['country_id'];
					break;
				}
			}
		}

		// Render the select
		echo render_select(
			'country',
			$countries,
			['country_id', ['short_name']],
			'lead_country',
			$selected,
			['data-none-selected-text' => _l('dropdown_non_selected_tex')]
		);
		?>

        <?= render_select(
            'state',
            $states,
            ['state_id', 'state_name'],
            'lead_state',
            (isset($lead) ? $lead->state : ''),
            ['data-none-selected-text' => _l('dropdown_non_selected_tex')]
        ); ?>

        <?= render_select(
            'city',
            $cities,
            ['city_id', 'city_name'],
            'lead_city',
            (isset($lead) ? $lead->city : ''),
            ['data-none-selected-text' => _l('dropdown_non_selected_tex')]
        ); ?>

        


        <?= render_select(
					'zip',
					$pincodes,
					['pincode_id', 'pincode_name'],
					'lead_zip',
					(isset($lead) ? $lead->zip : ''),
					['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'id' => 'pincode']
				);
		 ?>

        <?php
        $selected_branch = isset($branch_id) ? $branch_id : '';
        echo render_select(
            'branch',
            $branches,
            ['id', ['name']],
            '<span class="text-danger">*</span> '._l('lead_branch'),
            $selected_branch,
            [
                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                'required' => 'required'
            ]
        );
        ?>
		<div class="row">
    <div class="col-md-6">
        <?php
        echo render_select(
            'entity_type',
            [
                ['id' => 'staff', 'name' => 'Staff'],
                ['id' => 'patient', 'name' => 'Patient']
            ],
            ['id', 'name'],
            'Select Refer Type',
            (isset($lead) ? $lead->refer_type : ''),
            ['id' => 'entity_type']
        );
        ?>
    </div>
    <div class="col-md-6">
        <?php
        $entityOptions = [];

        if (isset($lead)) {
            if ($lead->refer_type === 'staff') {
                $this->db->select('staffid as id, CONCAT(firstname, " ", lastname) as name');
                $entityOptions = $this->db->get(db_prefix() . 'staff')->result_array();
            } elseif ($lead->refer_type === 'patient') {
                $this->db->select('userid as id, company as name');
                $entityOptions = $this->db->get(db_prefix() . 'clients')->result_array();
            }
        }

        echo render_select(
            'entity_id',
            $entityOptions,
            ['id', 'name'],
            'Select Referrer',
            (isset($lead) ? $lead->refer_id : ''),
            ['id' => 'entity_id']
        );
        ?>
    </div>
</div>

    </div>
	
</div>

<style>
.lead_with_doctor_section.hide,
.appointment_payment_section.hide {
   ! display: none;
}

</style>
<script>
$(function () {
    const staffList   = <?= json_encode($staff_list); ?>;
    const patientList = <?= json_encode($patient_list); ?>;

    $('#entity_type').on('change', function () {
        const type = $(this).val();
        let list = [];
        let options = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';

        if (type === 'staff') {
            list = staffList;
        } else if (type === 'patient') {
            list = patientList;
        }

        list.forEach(item => {
            options += `<option value="${item.id}">${item.name}</option>`;
        });

        $('#entity_id').html(options).selectpicker('refresh');
    });
});
</script>

<?php

if(!isset($lead)){
	?>
    <div class="lead_with_doctor_section" style="margin-top:-10px;">
	<div class="row" style="padding: 10px;">
  <!-- Doctor -->
  <div class="col-md-4">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('doctor'); ?></label>
      <select class="form-control selectpicker" name="doctor_id" id="doctor_id" data-live-search="true">
        <option value=""></option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?= $doc['staffid']; ?>"><?= $doc['firstname'] . ' ' . $doc['lastname']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Appointment Date -->
  <div class="col-md-4">
    <div class="form-group">
	<label class="form-label"><span style="color: #f00">*</span> <?= _l('appointment_date') ?></label>
     <?php
$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
?>
<input type="datetime-local" class="form-control" name="appointment_date" value="<?php echo $now; ?>" min="<?php echo $now; ?>" required>


    </div>
  </div>


  <!-- Treatment -->
 <div class="col-md-4">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('treatment'); ?></label>
      <select name="treatment_id" class="form-control selectpicker" data-live-search="true" id="treatment_id">
        <option value=""></option>
        <?php foreach ($items as $group_id => $_items) {
          if (isset($_items[0]['group_name']) && $_items[0]['group_name'] == 'Package') {
            foreach ($_items as $item) {
              $item_id = $item['id'] ?? '';
              $item_name = $item['description'] ?? '';
              $item_rate = isset($item['rate']) ? $item['rate'] : '';
              ?>
              <option value="<?= e($item_id); ?>"
                data-rate="<?= e($item_rate); ?>"
                <?= ($selected !== '' && (string) $selected === (string) $item_id) ? 'selected' : ''; ?>>
                <?= e($item_name); ?>
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
			  <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true">
				<option value=""></option>
				<?php foreach ($appointment_type as $app): ?>
				  <option value="<?= $app['appointment_type_id']; ?>"><?= $app['appointment_type_name']; ?></option>
				<?php endforeach; ?>
			  </select>
		  </div>
		</div>

  <!-- Consultation Fee -->
  <div class="col-md-4">
  <div class="form-group">
	<label><span style="color: #f00">*</span> <?= _l('consultation_fees'); ?></label>
	<?php
	$has_consultation_fee = false;
	foreach ($items as $_group_items) {
	  if (isset($_group_items[0]['group_name']) && $_group_items[0]['group_name'] == "Consultation Fee") {
		$has_consultation_fee = true;
		break;
	  }
	}
	?>
	<select name="item_select" class="form-control selectpicker" data-live-search="true" id="consultation_fee_id">
	  <option value=""></option>
	  <?php foreach ($items as $group_id => $_items) {
		$group_name = $_items[0]['group_name'] ?? '';
		if ($has_consultation_fee && $group_name != "Consultation Fee") {
		  continue;
		} ?>
		<optgroup data-group-id="<?= e($group_id); ?>" label="<?= $group_name; ?>">
		  <?php foreach ($_items as $item) { ?>
			<option value="<?= e($item['rate']); ?>"
					data-rate="<?= e($item['rate']); ?>"
					data-subtext="<?= strip_tags(mb_substr($item['long_description'], 0, 200)); ?>">
						<?= e(app_format_number($item['rate'])); ?>
			</option>
		  <?php } ?>
		</optgroup>
	  <?php } ?>
	</select>
  </div>
</div>

 <div class="appointment_payment_section">
  <!-- Payment Amount -->
  <div class="col-md-4">
    <div class="form-group">
	
      <label><span style="color: #f00">*</span> <?= _l('payment_amount'); ?></label>
      <input type="number" class="form-control" id="paying_amount" name="payment_amount" min="0" step="0.01"
             placeholder="<?= _l('enter_payment_amount'); ?>">
      <small class="text-danger" id="amountError" style="display: none;">
        <?= _l('amount_exceeds_item'); ?>
      </small>
    </div>
  </div>

  <!-- Attachment -->
  <div class="col-md-4">
    <div class="form-group">
      <label><?= _l('attachment_optional'); ?></label>
      <input type="file" class="form-control" id="paymentAttachment" name="attachment"
             accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
    </div>
  </div>

  <!-- Payment Mode -->
  <div class="col-md-4">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('payment_mode'); ?></label>
      <select class="selectpicker form-control" name="paymentmode" data-width="100%"
              data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
        <option value=""></option>
        <?php foreach ($payment_modes as $mode) { ?>
          <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
</div>

 
  

  
</div>

</div>

<?php
}
?>

            <div class="col-md-12">
                <?php $value = (isset($lead) ? $lead->description : ''); ?>
                <?= render_textarea('description', 'lead_description', $value); ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php if (! isset($lead)) { ?>
                        <div class="lead-select-date-contacted hide">
                            <?= render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', '', ['data-date-end-date' => date('Y-m-d')]); ?>
                        </div>
                        <?php } else { ?>
                        <?= render_datetime_input('lastcontact', 'leads_dt_last_contact', _dt($lead->lastcontact), ['data-date-end-date' => date('Y-m-d')]); ?>
                        <?php } ?>
                        <!--<div
                            class="checkbox-inline checkbox<?= isset($lead) ? ' hide' : ''; ?><?= isset($lead) && (is_lead_creator($lead->id) || staff_can('edit', 'leads')) ? ' lead-edit' : ''; ?>">
                            <input type="checkbox" name="is_public"
                                <?= isset($lead) && $lead->is_public ? 'checked' : ''; ?>
                            id="lead_public">
                            <label for="lead_public">
                                <?= _l('lead_public'); ?>
                            </label>
                        </div>-->
                        <?php if (! isset($lead)) { ?>
                        <div class="checkbox-inline checkbox checkbox-primary">
                            <input type="checkbox" name="contacted_today" id="contacted_today" checked>
                            <label for="contacted_today">
                                <?= _l('lead_add_edit_contacted_today'); ?>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
			
			<?php if (! is_language_disabled()) { ?>
			<div class="col-md-4">
			<br>
                <div class="form-group">
                    <label for="default_language"
                        class="control-label"><?= _l('localization_default_language'); ?></label>
                    <select name="default_language" data-live-search="true" id="default_language"
                        class="form-control selectpicker"
                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                        <option value="">
                            <?= _l('system_default_string'); ?>
                        </option>
                        <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                            $selected = '';
                            if (isset($lead)) {
                                if ($lead->default_language == $availableLanguage) {
                                    $selected = 'selected';
                                }
                            } ?>
                        <option value="<?= e($availableLanguage); ?>"
                            <?= e($selected); ?>>
                            <?= e(ucfirst($availableLanguage)); ?>
                        </option>
                        <?php
                        } ?>
                    </select>
                </div>
                </div>
                <?php } ?>
            <div class="col-md-12 mtop15">
                <?php $rel_id = (isset($lead) ? $lead->id : false); ?>
                <?= render_custom_fields('leads', $rel_id); ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php if (isset($lead)) { ?>
    <div class="lead-latest-activity tw-mb-3 lead-view">
        <div class="lead-info-heading">
            <h4><?= _l('lead_latest_activity'); ?>
            </h4>
        </div>
        <div id="lead-latest-activity" class="pleft5"></div>
    </div>
    <?php } ?>
    <?php if ($lead_locked == false) { ?>
    <div
        class="lead-edit<?= isset($lead) ? ' hide' : ''; ?>">
        <hr class="-tw-mx-5 tw-border-neutral-200" />
        <button type="submit" class="btn btn-primary pull-right lead-save-btn" id="lead-form-submit">
            <?= _l('submit'); ?>
        </button>
        <button type=" button" class="btn btn-default pull-right mright5" data-dismiss="modal">
            <?= _l('close'); ?>
        </button>
    </div>
    <?php } ?>
    <div class="clearfix"></div>
    <?= form_close(); ?>
</div>
<?php if (isset($lead) && $lead_locked == true) { ?>
<script>

    $(function() {
        // Set all fields to disabled if lead is locked
        $.each($('.lead-wrapper').find('input, select, textarea'), function() {
            $(this).attr('disabled', true);
            if ($(this).is('select')) {
                $(this).selectpicker('refresh');
            }
        });
    });
</script>
<?php } ?>
<script>
   $(function () {
    $('#lead_form').on('submit', function (e) {
        var form = this;
        if (form.checkValidity() === false) {
            // Let browser show validation errors
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        // Passed validation -> disable button
        var $btn = $('#lead-form-submit');
        $btn.prop('disabled', true).text('<?= _l("please_wait") ?>...');
    });
});
</script>
<script>
$(function () {
    function toggleFieldsByResponse() {
        const response = $('#patient_response_id option:selected').text().toLowerCase().trim();

        // Common mandatory fields
        const doctor = $('#doctor_id');
        const appointmentDate = $('#appointment_date');
        const treatment = $('#treatment_id');
        const consultation = $('#consultation_fee_id');

        const paymentAmount = $('#paying_amount');
        const paymentMode = $('select[name="paymentmode"]');
        const paymentSection = $('.appointment_payment_section');

        // Reset required
        doctor.removeAttr('required');
        appointmentDate.removeAttr('required');
        treatment.removeAttr('required');
        consultation.removeAttr('required');
        paymentAmount.removeAttr('required');
        paymentMode.removeAttr('required');

        // Hide payment section by default
        paymentSection.hide();

        if (response === 'on appointment' || response === 'paid appointment') {
            $('.lead_with_doctor_section').removeClass('hide').slideDown();
			<?php
			if(!isset($lead)){
			?>
				// Common fields
				doctor.attr('required', true);
				appointmentDate.attr('required', true);
				treatment.attr('required', true);
				consultation.attr('required', true);
			<?php
			}
			?>
            
        } else {
            $('.lead_with_doctor_section').slideUp().addClass('hide');
        }

        if (response === 'paid appointment') {
            paymentSection.show();
            paymentAmount.attr('required', true);
            paymentMode.attr('required', true);
        }
    }

    toggleFieldsByResponse();

    $('#patient_response_id').on('change', toggleFieldsByResponse);
});
</script>
<script>
$(function () {
    const $treatmentSelect = $('#treatment_id');
    const $leadValueInput = $('input[name="lead_value"]');
    if (!$treatmentSelect.length || !$leadValueInput.length) {
        return;
    }

    function getCurrentResponse() {
        return $('#patient_response_id option:selected').text().toLowerCase().trim();
    }

    function shouldAutoFillLeadValue() {
        const resp = getCurrentResponse();
        return resp === 'on appointment' || resp === 'paid appointment';
    }

    function syncLeadValue(forceUpdate) {
        if (!shouldAutoFillLeadValue()) {
            return;
        }

        const $selected = $treatmentSelect.find('option:selected');
        const rateAttr = $selected.data('rate');
        const hasRate = typeof rateAttr !== 'undefined' && rateAttr !== null && rateAttr !== '';

        if (hasRate) {
            if (forceUpdate || !$leadValueInput.val()) {
                $leadValueInput.val(rateAttr);
            }
        } else if (forceUpdate) {
            $leadValueInput.val('');
        }
    }

    syncLeadValue(false);

    $treatmentSelect.on('changed.bs.select change', function () {
        if (shouldAutoFillLeadValue()) {
            syncLeadValue(true);
        }
    });

    $('#patient_response_id').on('changed.bs.select change', function () {
        if (shouldAutoFillLeadValue()) {
            syncLeadValue(true);
        }
    });
});
</script>



<script>
$(document).ready(function() {
    $('select[name="state"]').on('change', function() {
        var state_id = $(this).val();

        $.ajax({
            url: admin_url + 'leads/get_cities_by_state',
            type: 'POST',
            data: {
                state_id: state_id,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            dataType: 'json',
            success: function(response) {
                var $citySelect = $('select[name="city"]');
                $citySelect.empty().append('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>');

                $.each(response, function(i, city) {
                    $citySelect.append($('<option>', {
                        value: city.city_id,
                        text: city.city_name
                    }));
                });

                $citySelect.selectpicker('refresh');
            }
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $('select[name="country"]').on('change', function() {
        var countryId = $(this).val();

        $.ajax({
            url: admin_url + 'leads/get_country_calling_code',
            type: 'POST',
            data: {
                country_id: countryId,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            dataType: 'json',
            success: function(response) {
                if (response.calling_code) {
                    $('#calling_code').val(response.calling_code);
                    $('#calling_code').selectpicker('refresh');
                }
            }
        });
    });
});
</script>

<script>
$(document).ready(function () {
  $('select[name="city"]').on('change', function () {
    let cityId = $(this).val();
    if (cityId) {
      $.ajax({
        url: admin_url + 'client/get_pincodes_by_city/' + cityId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          let pincodeOptions = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';
          $.each(data, function (i, pin) {
            pincodeOptions += '<option value="' + pin.pincode_id + '">' + pin.pincode_name + '</option>';
          });
          $('#pincode').html(pincodeOptions).selectpicker('refresh');
        },
        error: function () {
          alert('Failed to fetch pincodes');
        }
      });
    } else {
      $('#pincode').html('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>').selectpicker('refresh');
    }
  });
});
</script>
<script>
$(document).ready(function() {
    // When consultation_fee is selected
    $('#consultation_fee_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var feeText = selectedOption.text();
        var feeValue = parseFloat(feeText.replace(/[^\d.]/g, '')) || 0;

        $('#due_amount').val(feeValue.toFixed(2));
        //$('#paying_amount').val(0);
    });

    // When paying_amount is typed
    $('#paying_amount').on('input', function() {
        var feeText = $('#consultation_fee_id option:selected').text();
        var totalFee = parseFloat(feeText.replace(/[^\d.]/g, '')) || 0;
        var payingAmount = parseFloat($(this).val()) || 0;

        if (payingAmount > totalFee) {
            alert_float('danger', '<?php echo _l('paying_amount_cannot_exceed_due_amount'); ?>');
            $(this).val('');
            $('#due_amount').val(totalFee.toFixed(2));
            return;
        }

        var due = totalFee - payingAmount;
        $('#due_amount').val(due.toFixed(2));
    });
});
</script>
