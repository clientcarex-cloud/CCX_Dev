<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends App_Model
{
    private $encrypted_fields = ['smtp_password', 'microsoft_mail_client_secret', 'google_mail_client_secret'];

    public function __construct()
    {
        parent::__construct();
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        foreach ($payment_gateways as $gateway) {
            $settings = $gateway['instance']->getSettings();
            foreach ($settings as $option) {
                if (isset($option['encrypted']) && $option['encrypted'] == true) {
                    array_push($this->encrypted_fields, $option['name']);
                }
            }
        }
    }

    /**
     * Update all settings
     * @param  array $data all settings
     * @return integer
     */
    public function update($data)
    {
        $original_encrypted_fields = [];
        foreach ($this->encrypted_fields as $ef) {
            $original_encrypted_fields[$ef] = get_option($ef);
        }
        $affectedRows = 0;
        $data         = hooks()->apply_filters('before_settings_updated', $data);

        if (isset($data['tags'])) {
            $tagsExists = false;
            foreach ($data['tags'] as $id => $name) {
                $this->db->where('name', $name);
                $this->db->where('id !=', $id);
                $tag = $this->db->get('tags')->row();
                if (!$tag) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'tags', ['name' => $name]);
                    $affectedRows += $this->db->affected_rows();
                } else {
                    $tagsExists = true;
                }
            }

            if ($tagsExists) {
                set_alert('warning', _l('tags_update_replace_warning'));

                return false;
            }

            return (bool) $affectedRows;
        }
        if (!isset($data['settings']['default_tax']) && isset($data['finance_settings'])) {
            $data['settings']['default_tax'] = [];
        }
        $all_settings_looped = [];
		unset($data['settings'][0]);
		
        foreach ($data['settings'] as $name => $val) {
            // Do not trim thousand separator option
            // There is an option of white space there and if will be trimmed wont work as configured
            if (is_string($val) && $name != 'thousand_separator') {
                $val = trim($val);
            }

            array_push($all_settings_looped, $name);

            $hook_data['name']  = $name;
            $hook_data['value'] = $val;
            $hook_data          = hooks()->apply_filters('before_single_setting_updated_in_loop', $hook_data);
            $name               = $hook_data['name'];
            $val                = $hook_data['value'];

            if ($name == 'default_contact_permissions') {
                $val = serialize($val);
            } elseif ($name == 'lead_unique_validation') {
                $val = json_encode($val);
            } elseif ($name == 'required_register_fields') {
                $val = json_encode($val);
            } elseif ($name == 'visible_customer_profile_tabs') {
                if ($val == '') {
                    $val = 'all';
                } else {
                    $tabs           = get_customer_profile_tabs();
                    $newVisibleTabs = [];
                    foreach ($tabs as $tabKey => $tab) {
                        $newVisibleTabs[$tabKey] = in_array($tabKey, $val);
                    }
                    $val = serialize($newVisibleTabs);
                }
            } elseif ($name == 'email_signature') {
                $val = html_entity_decode($val);

                if ($val == strip_tags($val)) {
                    // not contains HTML, add break lines
                    $val = nl2br_save_html($val);
                }
            } elseif ($name == 'email_header' || $name == 'email_footer') {
                $val = html_entity_decode($val);
            } elseif ($name == 'default_tax') {
                $val = array_filter($val, function ($value) {
                    return $value !== '';
                });
                $val = serialize($val);
            } elseif ($name == 'company_info_format' || $name == 'customer_info_format' || $name == 'proposal_info_format' || strpos($name, 'sms_trigger_') !== false) {
                $val = strip_tags($val);
                $val = nl2br($val);
            } elseif (in_array($name, $this->encrypted_fields)) {
                // Check if not empty $val password
                // Get original
                // Decrypt original
                // Compare with $val password
                // If equal unset
                // If not encrypt and save
                if (!empty($val)) {
                    $or_decrypted = $this->encryption->decrypt($original_encrypted_fields[$name]);
                    if ($or_decrypted == $val) {
                        continue;
                    }
                    $val = $this->encryption->encrypt($val);
                }
            } elseif ($name == 'staff_notify_completed_but_not_billed_tasks' || $name == 'reminder_for_completed_but_not_billed_tasks_days') {
                $val = json_encode($val);
            }

            if (update_option($name, $val)) {
                $affectedRows++;
                if ($name == 'save_last_order_for_tables') {
                    $this->db->query('DELETE FROM ' . db_prefix() . 'user_meta where meta_key like "%-table-last-order"');
                }
            }
        }

        // Contact permission default none
        if (!in_array('default_contact_permissions', $all_settings_looped)
                && in_array('customer_settings', $all_settings_looped)) {
            $this->db->where('name', 'default_contact_permissions');
            $this->db->update(db_prefix() . 'options', [
                'value' => serialize([]),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } 
        
        // Required register fields, nothing selected
        if (!in_array('required_register_fields', $all_settings_looped)
                && in_array('customer_settings', $all_settings_looped)) {
            $this->db->where('name', 'required_register_fields');
            $this->db->update(db_prefix() . 'options', [
                'value' => json_encode([]),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } 
        
        if (!in_array('visible_customer_profile_tabs', $all_settings_looped)
                && in_array('customer_settings', $all_settings_looped)) {
            $this->db->where('name', 'visible_customer_profile_tabs');
            $this->db->update(db_prefix() . 'options', [
                'value' => 'all',
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }

		$option_keys = [
		// Feedback
		'feedback_template_enabled',
		'feedback_template_content',
		'feedback_sms_template_id',
		'feedback_sms_template_content',
		'feedback_whatsapp_template_name',
		'feedback_whatsapp_template_content',

		// Appointment Created
		'appointment_created_template_enabled',
		'appointment_created_template_content',
		'appointment_created_sms_template_id',
		'appointment_created_sms_template_content',
		'appointment_created_whatsapp_template_name',
		'appointment_created_whatsapp_template_content',

		// Package Accepted
		'package_accepted_template_enabled',
		'package_accepted_template_content',
		'package_accepted_sms_template_id',
		'package_accepted_sms_template_content',
		'package_accepted_whatsapp_template_name',
		'package_accepted_whatsapp_template_content',

		// Package Created
		'package_created_template_enabled',
		'package_created_template_content',
		'package_created_sms_template_id',
		'package_created_sms_template_content',
		'package_created_whatsapp_template_name',
		'package_created_whatsapp_template_content',

		// Patient Registered
		'patient_registered_template_enabled',
		'patient_registered_template_content',
		'patient_registered_sms_template_id',
		'patient_registered_sms_template_content',
		'patient_registered_whatsapp_template_name',
		'patient_registered_whatsapp_template_content',
		
		// Medicine Follow Up
		'medicine_followup_template_enabled',
		'medicine_followup_template_content',
		'medicine_followup_sms_template_id',
		'medicine_followup_sms_template_content',
		'medicine_followup_whatsapp_template_name',
		'medicine_followup_whatsapp_template_content',


		// Payment Done
		'payment_done_template_enabled',
		'payment_done_template_content',
		'payment_done_sms_template_id',
		'payment_done_sms_template_content',
		'payment_done_whatsapp_template_name',
		'payment_done_whatsapp_template_content',

		// Call Back
		'call_back_template_enabled',
		'call_back_template_content',
		'call_back_sms_template_id',
		'call_back_sms_template_content',
		'call_back_whatsapp_template_name',
		'call_back_whatsapp_template_content',

		// Call Feedback
		'call_feedback_template_enabled',
		'call_feedback_template_content',
		'call_feedback_sms_template_id',
		'call_feedback_sms_template_content',
		'call_feedback_whatsapp_template_name',
		'call_feedback_whatsapp_template_content',

		// Missed Appointment
		'missed_appointment_template_enabled',
		'missed_appointment_template_content',
		'missed_appointment_sms_template_id',
		'missed_appointment_sms_template_content',
		'missed_appointment_whatsapp_template_name',
		'missed_appointment_whatsapp_template_content',

		// Reschedule Appointment
		'reschedule_appointment_template_enabled',
		'reschedule_appointment_template_content',
		'reschedule_appointment_sms_template_id',
		'reschedule_appointment_sms_template_content',
		'reschedule_appointment_whatsapp_template_name',
		'reschedule_appointment_whatsapp_template_content',

		// Not Registered Patient Day 1
		'not_registered_patient_day1_template_enabled',
		'not_registered_patient_day1_template_content',
		'not_registered_patient_day1_sms_template_id',
		'not_registered_patient_day1_sms_template_content',
		'not_registered_patient_day1_whatsapp_template_name',
		'not_registered_patient_day1_whatsapp_template_content',

		// Not Registered Patient Week 1
		'not_registered_patient_week1_template_enabled',
		'not_registered_patient_week1_template_content',
		'not_registered_patient_week1_sms_template_id',
		'not_registered_patient_week1_sms_template_content',
		'not_registered_patient_week1_whatsapp_template_name',
		'not_registered_patient_week1_whatsapp_template_content',

		// Medicine Appointment
		'medicine_appointment_template_enabled',
		'medicine_appointment_template_content',
		'medicine_appointment_sms_template_id',
		'medicine_appointment_sms_template_content',
		'medicine_appointment_whatsapp_template_name',
		'medicine_appointment_whatsapp_template_content',

		// Renewal Reminder
		'renewal_reminder_template_enabled',
		'renewal_reminder_template_content',
		'renewal_reminder_sms_template_id',
		'renewal_reminder_sms_template_content',
		'renewal_reminder_whatsapp_template_name',
		'renewal_reminder_whatsapp_template_content',

		// Clinic Closed
		'clinic_closed_template_enabled',
		'clinic_closed_template_content',
		'clinic_closed_sms_template_id',
		'clinic_closed_sms_template_content',
		'clinic_closed_whatsapp_template_name',
		'clinic_closed_whatsapp_template_content',

		// Doctor Changed
		'doctor_changed_template_enabled',
		'doctor_changed_template_content',
		'doctor_changed_sms_template_id',
		'doctor_changed_sms_template_content',
		'doctor_changed_whatsapp_template_name',
		'doctor_changed_whatsapp_template_content',

		// Employee Absence
		'employee_absence_template_enabled',
		'employee_absence_template_content',
		'employee_absence_sms_template_id',
		'employee_absence_sms_template_content',
		'employee_absence_whatsapp_template_name',
		'employee_absence_whatsapp_template_content',

		// Employee Late HR
		'employee_late_hr_template_enabled',
		'employee_late_hr_template_content',
		'employee_late_hr_sms_template_id',
		'employee_late_hr_sms_template_content',
		'employee_late_hr_whatsapp_template_name',
		'employee_late_hr_whatsapp_template_content',

		// Uninformed Leave HR
		'uninformed_leave_hr_template_enabled',
		'uninformed_leave_hr_template_content',
		'uninformed_leave_hr_sms_template_id',
		'uninformed_leave_hr_sms_template_content',
		'uninformed_leave_hr_whatsapp_template_name',
		'uninformed_leave_hr_whatsapp_template_content',

		// New Joiner Punch In
		'new_joiner_punch_in_template_enabled',
		'new_joiner_punch_in_template_content',
		'new_joiner_punch_in_sms_template_id',
		'new_joiner_punch_in_sms_template_content',
		'new_joiner_punch_in_whatsapp_template_name',
		'new_joiner_punch_in_whatsapp_template_content',

		// Monthly Attendance Confirmation
		'monthly_attendance_confirmation_template_enabled',
		'monthly_attendance_confirmation_template_content',
		'monthly_attendance_confirmation_sms_template_id',
		'monthly_attendance_confirmation_sms_template_content',
		'monthly_attendance_confirmation_whatsapp_template_name',
		'monthly_attendance_confirmation_whatsapp_template_content',

		// Uninformed Leave Employee
		'uninformed_leave_employee_template_enabled',
		'uninformed_leave_employee_template_content',
		'uninformed_leave_employee_sms_template_id',
		'uninformed_leave_employee_sms_template_content',
		'uninformed_leave_employee_whatsapp_template_name',
		'uninformed_leave_employee_whatsapp_template_content',

		// Daily EOD
		'daily_eod_template_enabled',
		'daily_eod_template_content',
		'daily_eod_sms_template_id',
		'daily_eod_sms_template_content',
		'daily_eod_whatsapp_template_name',
		'daily_eod_whatsapp_template_content',

		// Uninformed Leave Manager
		'uninformed_leave_manager_template_enabled',
		'uninformed_leave_manager_template_content',
		'uninformed_leave_manager_sms_template_id',
		'uninformed_leave_manager_sms_template_content',
		'uninformed_leave_manager_whatsapp_template_name',
		'uninformed_leave_manager_whatsapp_template_content',

		// EOD Report
		'eod_report_template_enabled',
		'eod_report_template_content',
		'eod_report_sms_template_id',
		'eod_report_sms_template_content',
		'eod_report_whatsapp_template_name',
		'eod_report_whatsapp_template_content',

		// Dr Early Punch In
		'dr_early_punch_in_template_enabled',
		'dr_early_punch_in_template_content',
		'dr_early_punch_in_sms_template_id',
		'dr_early_punch_in_sms_template_content',
		'dr_early_punch_in_whatsapp_template_name',
		'dr_early_punch_in_whatsapp_template_content',

		// Dr Normal Punch In
		'dr_normal_punch_in_template_enabled',
		'dr_normal_punch_in_template_content',
		'dr_normal_punch_in_sms_template_id',
		'dr_normal_punch_in_sms_template_content',
		'dr_normal_punch_in_whatsapp_template_name',
		'dr_normal_punch_in_whatsapp_template_content',

		// Leave Reconsideration
		'leave_reconsideration_template_enabled',
		'leave_reconsideration_template_content',
		'leave_reconsideration_sms_template_id',
		'leave_reconsideration_sms_template_content',
		'leave_reconsideration_whatsapp_template_name',
		'leave_reconsideration_whatsapp_template_content',
		
		
		'enquiry_template_enabled',
		'enquiry_template_content',
		'enquiry_sms_template_id',
		'enquiry_sms_template_content',
		'enquiry_whatsapp_template_name',
		'enquiry_whatsapp_template_content',

		'call_back_template_enabled',
		'call_back_template_content',
		'call_back_sms_template_id',
		'call_back_sms_template_content',
		'call_back_whatsapp_template_name',
		'call_back_whatsapp_template_content',

		'junk_template_enabled',
		'junk_template_content',
		'junk_sms_template_id',
		'junk_sms_template_content',
		'junk_whatsapp_template_name',
		'junk_whatsapp_template_content',

		'lost_template_enabled',
		'lost_template_content',
		'lost_sms_template_id',
		'lost_sms_template_content',
		'lost_whatsapp_template_name',
		'lost_whatsapp_template_content',

		'new_template_enabled',
		'new_template_content',
		'new_sms_template_id',
		'new_sms_template_content',
		'new_whatsapp_template_name',
		'new_whatsapp_template_content',

		'no_feedback_template_enabled',
		'no_feedback_template_content',
		'no_feedback_sms_template_id',
		'no_feedback_sms_template_content',
		'no_feedback_whatsapp_template_name',
		'no_feedback_whatsapp_template_content',

		'no_response_template_enabled',
		'no_response_template_content',
		'no_response_sms_template_id',
		'no_response_sms_template_content',
		'no_response_whatsapp_template_name',
		'no_response_whatsapp_template_content',

		'on_appointment_template_enabled',
		'on_appointment_template_content',
		'on_appointment_sms_template_id',
		'on_appointment_sms_template_content',
		'on_appointment_whatsapp_template_name',
		'on_appointment_whatsapp_template_content',

		'only_consulted_template_enabled',
		'only_consulted_template_content',
		'only_consulted_sms_template_id',
		'only_consulted_sms_template_content',
		'only_consulted_whatsapp_template_name',
		'only_consulted_whatsapp_template_content',

		'paid_appointment_template_enabled',
		'paid_appointment_template_content',
		'paid_appointment_sms_template_id',
		'paid_appointment_sms_template_content',
		'paid_appointment_whatsapp_template_name',
		'paid_appointment_whatsapp_template_content',

		'prospect_template_enabled',
		'prospect_template_content',
		'prospect_sms_template_id',
		'prospect_sms_template_content',
		'prospect_whatsapp_template_name',
		'prospect_whatsapp_template_content',

		'reg._patients_template_enabled',
		'reg._patients_template_content',
		'reg._patients_sms_template_id',
		'reg._patients_sms_template_content',
		'reg._patients_whatsapp_template_name',
		'reg._patients_whatsapp_template_content',

		'visited_template_enabled',
		'visited_template_content',
		'visited_sms_template_id',
		'visited_sms_template_content',
		'visited_whatsapp_template_name',
		'visited_whatsapp_template_content',
		
		'feedback_auto_reply_template_enabled',
		'feedback_auto_reply_template_content',
		'feedback_auto_reply_sms_template_id',
		'feedback_auto_reply_sms_template_content',
		'feedback_auto_reply_whatsapp_template_name',
		'feedback_auto_reply_whatsapp_template_content',
		
		 'refer_to_us_auto_reply_template_enabled',
		'refer_to_us_auto_reply_template_content',
		'refer_to_us_auto_reply_sms_template_id',
		'refer_to_us_auto_reply_sms_template_content',
		'refer_to_us_auto_reply_whatsapp_template_name',
		'refer_to_us_auto_reply_whatsapp_template_content',
		
		'edit_auto_reply_template_enabled',
		'edit_auto_reply_template_content',
		'edit_auto_reply_sms_template_id',
		'edit_auto_reply_sms_template_content',
		'edit_auto_reply_whatsapp_template_name',
		'edit_auto_reply_whatsapp_template_content',


	];


		foreach ($option_keys as $key) {
			if (isset($data[$key])) {
				$this->db->where('name', $key);
				$this->db->update(db_prefix() . 'options', [
					'value' => $data[$key],
				]);
				if ($this->db->affected_rows() > 0) {
					$affectedRows++;
				}
			}
		}

		
        
        if (!in_array('lead_unique_validation', $all_settings_looped)
                && in_array('_leads_settings', $all_settings_looped)) {
            $this->db->where('name', 'lead_unique_validation');
            $this->db->update(db_prefix() . 'options', [
                'value' => json_encode([]),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }

        if (isset($data['custom_fields'])) {
            if (handle_custom_fields_post(0, $data['custom_fields'])) {
                $affectedRows++;
            }
        }

        return $affectedRows;
    }

    public function add_new_company_pdf_field($data)
    {
        $field = 'custom_company_field_' . trim($data['field']);
        $field = preg_replace('/\s+/', '_', $field);
        if (add_option($field, $data['value'])) {
            return true;
        }

        return false;
    }
}