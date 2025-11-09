<?php

use app\services\AbstractKanban;

defined('BASEPATH') or exit('No direct script access allowed');

class Leads_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get lead
     * @param  string $id Optional - leadid
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.id,' . db_prefix() . 'leads_status.name as status_name,' . db_prefix() . 'leads_sources.name as source_name, city.*, state.*, pincode.*, branch.name as branch_name');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id=' . db_prefix() . 'leads.status', 'left');
        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id=' . db_prefix() . 'leads.source', 'left');
        $this->db->join(db_prefix() . 'state', db_prefix() . 'state.state_id=' . db_prefix() . 'leads.state', 'left');
        $this->db->join(db_prefix() . 'city', db_prefix() . 'city.city_id=' . db_prefix() . 'leads.city', 'left');
        $this->db->join(db_prefix() . 'pincode', db_prefix() . 'pincode.pincode_id=' . db_prefix() . 'leads.zip', 'left');
		
		$this->db->join(db_prefix() . 'customers_groups as branch', "branch.id=leads.branch_id", "left");

        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'leads.id', $id);
            $lead = $this->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                if ($lead->from_form_id != 0) {
                    $lead->form_data = $this->get_form([
                        'id' => $lead->from_form_id,
                    ]);
                }
                $lead->attachments = $this->get_lead_attachments($id);
                $lead->public_url  = leads_public_url($id);
            }

            return $lead;
        }

        return $this->db->get(db_prefix() . 'leads')->result_array();
    }

    /**
     * Get lead by given email
     *
     * @since 2.8.0
     *
     * @param  string $email
     *
     * @return \strClass|null
     */
    public function get_lead_by_email($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('leads')->row();
    }

    /**
     * Add new lead to database
     * @param mixed $data lead data
     * @return mixed false || leadid
     */
    public function add($data)
    {
		$request = $data;
		$leads_with_doctor = array(
			"branch_id"           => isset($data['branch']) ? $data['branch'] : 0,
			"treatment_id"        => isset($data['treatment_id']) ? $data['treatment_id'] : null,
			"slot_time"           => isset($data['slot_time']) ? $data['slot_time'] : null,
			"staffid"             => isset($data['staffid']) ? $data['staffid'] : null,
			"patient_response_id" => isset($data['patient_response_id']) ? $data['patient_response_id'] : null,
		);
		
		$branch_id = isset($data['branch']) ? $data['branch'] : 0;
		
		$statuses = $this->get_status();
		$status_name = '';
		$status_id = '';
		foreach ($statuses as $status) {
			if ($data['patient_response_id'] == $status['id']) {
				$status_name = $status['name'];
				$status_id = $status['id'];
				break;
			}
		}
		// Step 1: Paid Appointment
		/* if (!empty($data['amount_paid']) && $data['amount_paid'] > 0) {
			foreach ($statuses as $status) {
				if (strcasecmp(trim($status['name']), 'Paid Appointment') === 0) {
					$status_name = $status['name'];
					$status_id = $status['id'];
					break;
				}
			}
		} else {
			// Step 2: On appointment
			if (!empty($data['appointment_date']) AND !empty($data['doctor_id'])) {
				foreach ($statuses as $status) {
					if (strcasecmp(trim($status['name']), 'On appointment') === 0) {
						$status_name = $status['name'];
						$status_id = $status['id'];
						break;
					}
				}
			} else {
				// Step 3: Follow up
				if (!empty($data['followup_date'])) {
					foreach ($statuses as $status) {
						if (strcasecmp(trim($status['name']), 'Follow up') === 0) {
							$status_name = $status['name'];
							$status_id = $status['id'];
							break;
						}
					}
				} else {
					// Step 4: Fallback to selected
					if (!empty($data['patient_response_id'])) {
						foreach ($statuses as $status) {
							if ($status['id'] == $data['patient_response_id']) {
								$status_name = $status['name'];
								$status_id = $status['id'];
								break;
							}
						}
					}
				}
			}
		} */


		$status_name = strtolower(str_replace(' ', '_', $status_name));
		
		$data['status'] = $status_id;
		if (!isset($status_id)) {
			$data['status'] = $status_id;
		}

		$call_log_data = array(
			'created_date' => date('Y-m-d H:i:s'),
			'branch_id'    => isset($data['branch']) ? $data['branch'] : 0,
			'patient_response_id'    => isset($data['patient_response_id']) ? $data['patient_response_id'] : 0,
			'enquired_by'  => get_staff_user_id(),
			'comments'     => isset($data['description']) ? $data['description'] : null,
		);
		$request['comments'] = isset($data['description']) ? $data['description'] : null;
		// Unset only if keys exist to avoid additional warnings
		$unset_keys = [
			'appointment_date',
			'doctor_id',
			'paymentmode',
			'payment_amount',
			'item_select',
			'treatment_id',
			'appointment_type_id',
			'consultation_fee_id',
			'branch',
			'treatment',
			'slot_time',
			'calling_code',
			'patient_response_id',
			'staffid'
		];

		foreach ($unset_keys as $key) {
			if (isset($data[$key])) {
				unset($data[$key]);
			}
		}

        if (isset($data['custom_contact_date']) || isset($data['custom_contact_date'])) {
            if (isset($data['contacted_today'])) {
                $data['lastcontact'] = date('Y-m-d H:i:s');
                unset($data['contacted_today']);
            } else {
                $data['lastcontact'] = to_sql_date($data['custom_contact_date'], true);
            }
        }

        if (isset($data['is_public']) && ($data['is_public'] == 1 || $data['is_public'] === 'on')) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }

        if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {
            $data['country'] = 0;
        }

        if (isset($data['custom_contact_date'])) {
            unset($data['custom_contact_date']);
        }
		
        if (isset($data['calling_code_alternate'])) {
			$data['lead_alternate_number'] = $data['calling_code_alternate'].' '.$data['lead_alternate_number'];
            unset($data['calling_code_alternate']);
        }
		
        if (isset($data['entity_type'])) {
			$data['refer_type'] = $data['entity_type'];
            unset($data['entity_type']);
        }
        if (isset($data['entity_id'])) {
			$data['refer_id'] = $data['entity_id'];
            unset($data['entity_id']);
        }

        $data['description'] = nl2br($data['description']);
        $data['dateadded']   = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();

        $data = hooks()->apply_filters('before_lead_added', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
		
		if (!$this->db->field_exists('lead_nature', db_prefix() . 'leads')) {
			unset($data['lead_nature']); // Prevent error if it doesn't exist
		}
		
		$languages = $this->input->post('languages');
        $data['languages'] = is_array($languages) ? implode(',', $languages) : $languages;
		
		$this->load->helper('lead_rollercoaster/lead_rollercoaster');

		if (get_option('lead_rollercoaster_enabled') == '1') {
			
			$lead_source = $this->input->post('source');
			$manual_assigned = $this->input->post('assigned');
			
			$res = lead_rollercoaster($lead_source, $manual_assigned);
			if($res == 1){
				 $data['assigned'] = get_staff_user_id();
			}else{
				$data['assigned'] = $res;
			}
			
			
		} else {
			//$data['assigned'] = 1; // Default assigned staff
		}


        $data['email'] = trim($data['email']);
		
        $data['branch_id'] = $branch_id;
		
        $this->db->insert(db_prefix() . 'leads', $data);
		//exit();

        $lead_id = $this->db->insert_id();
        if ($lead_id) {
			
			// Add leads_id later
			$leads_with_doctor['leads_id'] = $lead_id;
			$this->db->insert(db_prefix() . 'leads_with_doctor', $leads_with_doctor);
			
			$call_log_data['leads_id'] = $lead_id;
			$this->db->insert(db_prefix() . 'lead_call_logs', $call_log_data);
			$lead_call_log_id = $this->db->insert_id();
			
			if(!empty($request['doctor_id']) AND !empty($request['appointment_date'])){
				
				$assigned_doctor = $request['doctor_id'];
				$appointment_date = date("d-m-Y", strtotime($request['appointment_date']));
				$appointment_time = date("H:i A", strtotime($request['appointment_date']));
				$date_time = date("d-m-Y H:i A", strtotime($request['appointment_date']));

				$custom_model_path = FCPATH . 'modules/lead_call_log/models/Lead_call_log_model.php';

				if (file_exists($custom_model_path)) {
					
					$CI = &get_instance(); // Get CI super object

					// Manually load the model
					require_once($custom_model_path);
					$CI->load->model('lead_call_log/Lead_call_log_model');
					$request['leads_id'] = $lead_id;
					// Now you can use it like:
					
					$CI->Lead_call_log_model->convert_patient($request, $lead_call_log_id, $status_id, $status_name);
					
				}
			}else{
				$this->load->model('client/client_model');
			
				$add_lead_patient_status = array(
					"leadid" => $lead_id,
					"status" => $status_id,
					"datetime" => date('Y-m-d H:i:s')
				);
				$this->client_model->add_lead_patient_status($add_lead_patient_status);
			}
			
			
            log_activity('New Lead Added [ID: ' . $lead_id . ']');
            $this->log_lead_activity($lead_id, 'not_lead_activity_created');

            handle_tags_save($tags, $lead_id, 'lead');

            if (isset($custom_fields)) {
                handle_custom_fields_post($lead_id, $custom_fields);
            }
			
			if (isset($data['assigned'])) {
				
            $this->lead_assigned_member_notification($lead_id, $data['assigned']);
			}
            hooks()->do_action('lead_created', $lead_id);
			
			$get_assigned_staff = $this->db->get_where(db_prefix() . 'leads', array('id'=>$lead_id))->row();
			if($get_assigned_staff){
				$assigned_staff = $get_assigned_staff->assigned;
			}else{
				$assigned_staff = 1;
			}
			$staff_name = get_staff_full_name($assigned_staff);
			
			$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"staff_name" => $staff_name,
				"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
				"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
				"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
				"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
				"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
			);

			
			
			$this->lead_journey_log_event($lead_id, $status_name, $status_id, $communication_data);
			
            return $lead_id;
        }

        return false;
    }
	public function lead_journey_log_event($lead_id, $status, $status_id, $communication_data = null)
	{
		$actual_status = $status;
		
		$status_lower = strtolower($status);
		
		if(!$status_id){
			$status_clean = str_replace('_', ' ', $status);
			$this->db->where('LOWER(name)', strtolower($status_clean));
			$row = $this->db->get(db_prefix() . 'leads_status')->row();
			if($row){
				$status_id = $row->id;
			}
		}

		$this->db->where('LOWER(title)', $status_lower);

		// Show the compiled query before executing
		//echo $this->db->get_compiled_select(db_prefix() . 'flextestimonial'); // For debugging
		

		$get_testimonial = $this->db->get(db_prefix() . 'flextestimonial')->row();
		
		if($get_testimonial){
			$id = $get_testimonial->id;
			$share_key = str_pad(mt_rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
			$share_request = array(
			"user_id" => $lead_id,
			"type" => "lead",
			"date_sent" => date("Y-m-d H:i:s"),
			"created_by" => get_staff_user_id(),
			"status" => 'pending',
			"send_email" => 1,
			"send_sms" => 1,
			"send_whatsapp" => 1,
			"share_key" => $share_key,
			"feedback_id" => $id
			);
			$this->db->insert(db_prefix() . 'share_request', $share_request);
			$feedback_link = base_url('review/dr/' . $share_key);
		}else{
			$feedback_link = "https://positiveautism.com/";
		}
		$status = strtolower(str_replace(' ', '_', $status));
		
		//$status = "appointment_created";
		if (is_array($communication_data) && !empty($communication_data)) {
			
			$testimonial_link = "https://youtube.com/shorts/kmTdpLom6WU?si=GUfeARnZJyzRgA3N";
			$link = "https://youtube.com/shorts/kmTdpLom6WU?si=GUfeARnZJyzRgA3N";
			$payment_link = "https://positiveautism.com/";
			$this->load->helper('lead_call_log/custom');
		
			$send_email    = get_option($status . '_template_enabled');
			$send_sms      = get_option($status . '_template_enabled');
			$send_whatsapp = get_option($status . '_template_enabled');

			// Fetch patient details
			$this->db->select('lead.email, lead.phonenumber, lead.name');
			$this->db->from('leads as lead');
			$this->db->where('lead.id', $lead_id);
			$row = $this->db->get()->row();

			$email         = $row ? $row->email : null;
			$phonenumber   = $row ? $row->phonenumber : null;
			$patient_name  = $row ? $row->name : null;
			$this->load->config('client/config');
			$vertical_name = config_item('vertical_name');
			$branch_phone = get_option('invoice_company_phonenumber');
			
			// Fields expected in $communication_data
			$fields = [
				'appointment_date', 'appointment_time', 'assigned_doctor', 'date_time', 'branch_address',
				'invoice_link', 'invoice_number', 'reg_date_start', 'reg_date_end', 'follow_up_date',
				'punch_in_time', 'shift_time', 'missed_appoinment', 'package_cost', 'paid_amount',
				'pending_amount', 'treatment_duration', 'renewal_date', 'branch', 'employee_id',
				'treatment', 'last_payment_date', 'paid_date', 'receipt_link', 'staff_name', 'followup_date', 'consult_paid'
			];
			// Dynamically extract values from $communication_data
			foreach ($fields as $field) {
				${$field} = isset($communication_data[$field]) ? $communication_data[$field] : null;
			}
			// Email sending
			if ($send_email == 1 && $email) {
				$subject  = _l($status);
				$message  = get_option($status . '_template_content');
				
				
				$replacements = [
				'{patient_name}'       => htmlspecialchars((string) ($patient_name ?? '')),
				'{mobile}'             => htmlspecialchars((string) ($phonenumber ?? '')),
				'{staff_name}'   	   => htmlspecialchars((string) ($staff_name ?? '')),
				'{payment_link}'   	   => htmlspecialchars((string) ($payment_link ?? '')),
				'{testimonial_link}'   => htmlspecialchars((string) ($testimonial_link ?? '')),
				'{branch_phone}'       => htmlspecialchars((string) ($branch_phone ?? '')),
				'{email}'              => htmlspecialchars((string) ($email ?? '')),
				'{link}'               => htmlspecialchars((string) ($link ?? '')),
				'{feedback_link}'      => htmlspecialchars((string) ($feedback_link ?? '')),
				'{appointment_date}'   => htmlspecialchars((string) ($appointment_date ?? '')),
				'{appointment_time}'   => htmlspecialchars((string) ($appointment_time ?? '')),
				'{assigned_doctor}'    => htmlspecialchars((string) ($assigned_doctor ?? '')),
				'{invoice_link}'       => htmlspecialchars((string) ($invoice_link ?? '')),
				'{invoice_number}'     => htmlspecialchars((string) ($invoice_number ?? '')),
				'{reg_date_start}'     => htmlspecialchars((string) ($reg_date_start ?? '')),
				'{reg_date_end}'       => htmlspecialchars((string) ($reg_date_end ?? '')),
				'{follow_up_date}'     => htmlspecialchars((string) ($follow_up_date ?? '')),
				'{punch_in_time}'      => htmlspecialchars((string) ($punch_in_time ?? '')),
				'{shift_time}'         => htmlspecialchars((string) ($shift_time ?? '')),
				'{missed_appoinment}'  => htmlspecialchars((string) ($missed_appoinment ?? '')),
				'{package_cost}'       => htmlspecialchars((string) ($package_cost ?? '')),
				'{paid_amount}'        => htmlspecialchars((string) ($paid_amount ?? '')),
				'{pending_amount}'     => htmlspecialchars((string) ($pending_amount ?? '')),
				'{treatment_duration}' => htmlspecialchars((string) ($treatment_duration ?? '')),
				'{renewal_date}'       => htmlspecialchars((string) ($renewal_date ?? '')),
				'{branch}'             => htmlspecialchars((string) ($branch ?? '')),
				'{branch_address}'     => htmlspecialchars((string) ($branch_address ?? '')),
				'{paid_date}'          => htmlspecialchars((string) ($paid_date ?? '')),
				'{employee_id}'        => htmlspecialchars((string) ($employee_id ?? '')),
				'{treatment}'          => htmlspecialchars((string) ($treatment ?? '')),
				'{last_payment_date}'  => htmlspecialchars((string) ($last_payment_date ?? '')),
				'{vertical_name}'      => htmlspecialchars((string) ($vertical_name ?? '')),
				'{date_time}'          => htmlspecialchars((string) ($date_time ?? '')),
				'{receipt_link}'       => htmlspecialchars((string) ($receipt_link ?? '')),
				'{followup_date}'       => htmlspecialchars((string) ($followup_date ?? '')),
				'{consult_paid}'       => htmlspecialchars((string) ($consult_paid ?? '')),
			];

				$final_message = str_replace(array_keys($replacements), array_values($replacements), $message);
				
				$result = mailflow_send_email($email, $subject, $final_message, 'system');
				
				log_message_communication([
						'leadid'       => $lead_id,
						'status'       => $status_id,
						'message_type' => 'email',
						'message'      => $final_message,
						'response'      => $result
					]);
				
			}

			// SMS sending
			if ($send_sms == 1 && $phonenumber) {
				$feedback_sms_template_id      = get_option($status . '_sms_template_id');
				$feedback_sms_template_content = get_option($status . '_sms_template_content');
				
				$replacements = [
					'{patient_name}'       => $patient_name,
					'{mobile}'             => $phonenumber,
					'{testimonial_link}'   => $testimonial_link,
					'{staff_name}'   	   => $staff_name,
					'{payment_link}'   	   => $payment_link,
					'{branch_phone}'       => $branch_phone,
					'{email}'              => $email,
					'{feedback_link}'      => $feedback_link,
					'{link}'               => $link,
					'{appointment_date}'   => $appointment_date,
					'{appointment_time}'   => $appointment_time,
					'{assigned_doctor}'    => $assigned_doctor,
					'{invoice_link}'       => $invoice_link,
					'{invoice_number}'     => $invoice_number,
					'{reg_date_start}'     => $reg_date_start,
					'{reg_date_end}'       => $reg_date_end,
					'{follow_up_date}'     => $follow_up_date,
					'{punch_in_time}'      => $punch_in_time,
					'{shift_time}'         => $shift_time,
					'{missed_appoinment}'  => $missed_appoinment,
					'{package_cost}'       => $package_cost,
					'{paid_amount}'        => $paid_amount,
					'{pending_amount}'     => $pending_amount,
					'{paid_date}'     => $paid_date,
					'{treatment_duration}' => $treatment_duration,
					'{renewal_date}'       => $renewal_date,
					'{branch}'             => $branch,
					'{branch_address}'     => $branch_address,
					'{employee_id}'        => $employee_id,
					'{treatment}'          => $treatment,
					'{last_payment_date}'  => $last_payment_date,
					'{vertical_name}'      => $vertical_name,
					'{date_time}'      => $date_time,
					'{receipt_link}'      => $receipt_link,
					'{followup_date}'      => $followup_date,
					'{consult_paid}'      => $consult_paid,
				];

				preg_match_all('/\{(.*?)\}/', $feedback_sms_template_content, $matches);

				// Step 1: Extract placeholders like {name}, {phone}
				$found_placeholders = $matches[0]; // Includes curly braces
				$values = [];

				// Copy of original content to modify
				$final_message = $feedback_sms_template_content;

				foreach ($found_placeholders as $placeholder) {
					$key = trim($placeholder, '{}'); // e.g., "name" from "{name}"
					
					$value = isset($replacements[$placeholder]) ? $replacements[$placeholder] : '';

					// Collect for pipe-separated string
					$values[] = $value;

					// Replace placeholder with actual value
					$final_message = str_replace($placeholder, $value, $final_message);
				}

				// Output 1: Pipe-separated raw values (Srinu|9000720819)
				$final_output = implode('|', $values);

				// Output 2: Fully formatted message with replacements
				$final_message_output = $final_message;


				$gateway = $this->app_sms->get_active_gateway();
				if ($gateway !== false) {
					$className = 'sms_' . $gateway['id'];
					$retval = $this->{$className}->send_fastAPI($phonenumber, $feedback_sms_template_id, $final_output);
					
					log_message_communication([
						'leadid'       => $lead_id,
						'status'       => $status_id,
						'message_type' => 'sms',
						'message'      => $final_message_output,
						'response'      => $retval
					]);
				}
			}
			// WhatsApp sending
			if ($send_whatsapp == 1 && $phonenumber) {
				$templateName    = get_option($status . '_whatsapp_template_name');
				$templateContent = get_option($status . '_whatsapp_template_content');
				
				$replacements = [
					'patient_name'       => $patient_name,
					'mobile'             => $phonenumber,
					'testimonial_link'   => $testimonial_link,
					'staff_name'         => $staff_name,
					'payment_link'         => $payment_link,
					'branch_phone'       => $branch_phone,
					'email'              => $email,
					'paid_date'          => $paid_date,
					'feedback_link'      => $feedback_link,
					'link'               => $link,
					'appointment_date'   => $appointment_date,
					'appointment_time'   => $appointment_time,
					'assigned_doctor'    => $assigned_doctor,
					'invoice_link'       => $invoice_link,
					'invoice_number'     => $invoice_number,
					'reg_date_start'     => $reg_date_start,
					'reg_date_end'       => $reg_date_end,
					'follow_up_date'     => $follow_up_date,
					'punch_in_time'      => $punch_in_time,
					'shift_time'         => $shift_time,
					'missed_appoinment'  => $missed_appoinment,
					'package_cost'       => $package_cost,
					'paid_amount'        => $paid_amount,
					'pending_amount'     => $pending_amount,
					'treatment_duration' => $treatment_duration,
					'renewal_date'       => $renewal_date,
					'branch'             => $branch,
					'branch_address'     => $branch_address,
					'employee_id'        => $employee_id,
					'treatment'          => $treatment,
					'last_payment_date'  => $last_payment_date,
					'vertical_name'      => $vertical_name,
					'date_time'      => $date_time,
					'receipt_link'      => $receipt_link,
					'followup_date'      => $followup_date,
					'consult_paid'      => $consult_paid,
				];

				preg_match_all('/\{{1,2}(\w+)\}{1,2}/', $templateContent, $matches);
				$found_keys = $matches[1]; // Only the keys (without braces)
				$raw_placeholders = $matches[0]; // With braces, e.g. {name}, {{name}}

				$parameterArray = [];
				$final_message_output = $templateContent;

				foreach ($found_keys as $index => $key) {
					$placeholder = $raw_placeholders[$index]; // e.g., {name} or {{name}}

					if (isset($replacements[$key])) {
						$value = $replacements[$key];
						$parameterArray[$key] = $value;

						// Replace in message
						$final_message_output = str_replace($placeholder, $value, $final_message_output);
					}
				}

				//print_r($parameterArray);
				$retval = send_message_via_api($phonenumber, $templateName, $parameterArray);
				
				log_message_communication([
						'leadid'       => $lead_id,
						'status'       => $status_id,
						'message_type' => 'whatsapp',
						'message'      => $final_message_output,
						'response'      => $retval
					]);
					
			}
		}
		
	}

    public function lead_assigned_member_notification($lead_id, $assigned, $integration = false)
    {
        if (empty($assigned) || $assigned == 0) {
            return;
        }

        if ($integration == false) {
            if ($assigned == get_staff_user_id()) {
                return false;
            }
        }

        $name = $this->db->select('name')->from(db_prefix() . 'leads')->where('id', $lead_id)->get()->row()->name;

        $notification_data = [
            'description'     => ($integration == false) ? 'not_assigned_lead_to_you' : 'not_lead_assigned_from_form',
            'touserid'        => $assigned,
            'link'            => '#leadid=' . $lead_id,
            'additional_data' => ($integration == false ? serialize([
                $name,
            ]) : serialize([])),
        ];

        if ($integration != false) {
            $notification_data['fromcompany'] = 1;
        }

        if (add_notification($notification_data)) {
            pusher_trigger_notification([$assigned]);
        }

        $this->db->select('email');
        $this->db->where('staffid', $assigned);
        $email = $this->db->get(db_prefix() . 'staff')->row()->email;

        send_mail_template('lead_assigned', $lead_id, $email);

        $this->db->where('id', $lead_id);
        $this->db->update(db_prefix() . 'leads', [
            'dateassigned' => date('Y-m-d'),
        ]);

        $not_additional_data = [
            e(get_staff_full_name()),
            '<a href="' . admin_url('profile/' . $assigned) . '" target="_blank">' . e(get_staff_full_name($assigned)) . '</a>',
        ];

        if ($integration == true) {
            unset($not_additional_data[0]);
            array_values(($not_additional_data));
        }

        $not_additional_data = serialize($not_additional_data);

        $not_desc = ($integration == false ? 'not_lead_activity_assigned_to' : 'not_lead_activity_assigned_from_form');
        $this->log_lead_activity($lead_id, $not_desc, $integration, $not_additional_data);

        hooks()->do_action('after_lead_assigned_member_notification_sent', $lead_id);
    }

    /**
     * Update lead
     * @param  array $data lead data
     * @param  mixed $id   leadid
     * @return boolean
     */
    public function update($data, $id)
    {
		$patient_response_id = isset($data['patient_response_id']) ? $data['patient_response_id'] : 0;
		
		$statuses = $this->get_status();
		$status_name = '';
		$status_id = '';
		foreach ($statuses as $status) {
			if ($patient_response_id == $status['id']) {
				$status_name = $status['name'];
				$status_id = $status['id'];
			
			}
		}
		
		$status_name = strtolower(str_replace(' ', '_', $status_name));
		//$data['status'] = $status_id;
		$staff_name = get_staff_full_name($data['assigned']);
		$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"staff_name"   => !empty($staff_name)   ? $staff_name   : "Staff",
				"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
				"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
				"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
				"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
				"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
			);

			
			
		//$this->lead_journey_log_event($id, $status_name, $status_id, $communication_data);
		
		$leads_with_doctor = array(
		"branch_id"    => $data['branch'],
		"treatment_id" => isset($data['treatment_id']) ? $data['treatment_id'] : 0,
		'patient_response_id' => isset($data['patient_response_id']) ? $data['patient_response_id'] : 0,
		);
		// Then unset the used fields from $data
		unset(
			$data['consultation_fee_id'],
			$data['branch'],
			$data['doctor_id'],
			$data['treatment_id'],
			$data['appointment_date'],
			$data['item_select'],
			$data['followup_date'],
			$data['payment_amount'],
			$data['paymentmode'],
			$data['treatment'],
			$data['slot_time'],
			$data['patient_response_id'],
			$data['calling_code'],
			$data['staffid']
		);
		//$this->db->where('leads_id', $id);
        //$this->db->update(db_prefix() . 'leads_with_doctor', $leads_with_doctor);
		
        $current_lead_data = $this->get($id);
        $current_status    = $this->get_status($current_lead_data->status);
        if ($current_status) {
            $current_status_id = $current_status->id;
            $current_status    = $current_status->name;
        } else {
            if ($current_lead_data->junk == 1) {
                $current_status = _l('lead_junk');
            } elseif ($current_lead_data->lost == 1) {
                $current_status = _l('lead_lost');
            } else {
                $current_status = '';
            }
            $current_status_id = 0;
        }

        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (!defined('API')) {
            if (isset($data['is_public'])) {
                $data['is_public'] = 1;
            } else {
                $data['is_public'] = 0;
            }

            if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {
                $data['country'] = 0;
            }

            if (isset($data['description'])) {
                $data['description'] = nl2br($data['description']);
            }
        }

        if (isset($data['lastcontact']) && $data['lastcontact'] == '' || isset($data['lastcontact']) && $data['lastcontact'] == null) {
            $data['lastcontact'] = null;
        } elseif (isset($data['lastcontact'])) {
            $data['lastcontact'] = to_sql_date($data['lastcontact'], true);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'lead')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }
		if (isset($data['calling_code_alternate'])) {
			$data['lead_alternate_number'] = $data['calling_code_alternate'].' '.$data['lead_alternate_number'];
            unset($data['calling_code_alternate']);
        }

        if (isset($data['remove_attachments'])) {
            foreach ($data['remove_attachments'] as $key => $val) {
                $attachment = $this->get_lead_attachments($id, $key);
                if ($attachment) {
                    $this->delete_lead_attachment($attachment->id);
                }
            }
            unset($data['remove_attachments']);
        }
		
		if (isset($data['entity_type'])) {
			$data['refer_type'] = $data['entity_type'];
            unset($data['entity_type']);
        }
        if (isset($data['entity_id'])) {
			$data['refer_id'] = $data['entity_id'];
            unset($data['entity_id']);
        }

        $data['assigned'] = $data['assigned'];
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
		
		$languages = $this->input->post('languages');
        $data['languages'] = is_array($languages) ? implode(',', $languages) : $languages;

        $data['email'] = trim($data['email']);
		if (!$this->db->field_exists('lead_nature', db_prefix() . 'leads')) {
			unset($data['lead_nature']); // Prevent error if it doesn't exist
		}
		
		$this->db->where('id', $id);
		$existing_data = $this->db->get(db_prefix() . 'leads')->row_array(); // 1. Get old data


        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', $data);
		
        if ($this->db->affected_rows() > 0) {
             $affectedRows++;

			// 2. Compare old and new values
			$changed_fields = [];
			foreach ($data as $key => $new_value) {
				if (isset($existing_data[$key]) && $existing_data[$key] != $new_value) {
					$changed_fields[] = ucfirst($key) . " changed from '{$existing_data[$key]}' to '{$new_value}'";
				}
			}

			// 3. Log change summary
			if (!empty($changed_fields)) {
				$this->log_lead_activity($id, 'custom_lead_update_log', false, serialize([
					get_staff_full_name(),
					implode("; ", $changed_fields),
				])); 
			}

			// 4. If status updated, log separately
			/* if (isset($data['patient_response_id']) && $current_status_id != $data['patient_response_id']) {
				$this->db->where('id', $id);
				$this->db->update(db_prefix() . 'leads', [
					'last_status_change' => date('Y-m-d H:i:s'),
				]);
				$new_status_name = $this->get_status($data['patient_response_id'])->name;
				$this->log_lead_activity($id, 'not_lead_activity_status_updated', false, serialize([
					get_staff_full_name(),
					$current_status,
					$new_status_name,
				]));
			} */

              /*   hooks()->do_action('lead_status_changed', [
                    'lead_id'    => $id,
                    'old_status' => $current_status_id,
                    'new_status' => $data['status'],
                ]);
         */

            /* if (($current_lead_data->junk == 1 || $current_lead_data->lost == 1) && $data['status'] != 0) {
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'leads', [
                    'junk' => 0,
                    'lost' => 0,
                ]);
            } */

            if (isset($data['assigned'])) {
                if ($current_lead_data->assigned != $data['assigned'] && (!empty($data['assigned']) && $data['assigned'] != 0)) {
                    $this->lead_assigned_member_notification($id, $data['assigned']);
                }
            }
			log_activity('Lead Updated [ID: ' . $id . ']');
            $this->log_lead_activity($id, 'not_lead_activity_updated');

            hooks()->do_action('after_lead_updated', $id);

            return true;
        }
        if ($affectedRows > 0) {
			log_activity('Lead Updated [ID: ' . $id . ']');
            $this->log_lead_activity($id, 'not_lead_activity_updated');
            hooks()->do_action('after_lead_updated', $id);
			
            return true;
        }

        return false;
    }

    /**
     * Delete lead from database and all connections
     * @param  mixed $id leadid
     * @return boolean
     */
    public function delete($id)
    {
        $affectedRows = 0;

        hooks()->do_action('before_lead_deleted', $id);

        $lead = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'leads');
        if ($this->db->affected_rows() > 0) {
            log_activity('Lead Deleted [Deleted by: ' . get_staff_full_name() . ', ID: ' . $id . ']');

            $attachments = $this->get_lead_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_lead_attachment($attachment['id']);
            }

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'leads');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('leadid', $id);
            $this->db->delete(db_prefix() . 'lead_activity_log');

            $this->db->where('leadid', $id);
            $this->db->delete(db_prefix() . 'lead_integration_emails');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'lead');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_type', 'lead');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_type', 'lead');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'taggables');

            $this->load->model('proposals_model');
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'lead');
            $proposals = $this->db->get(db_prefix() . 'proposals')->result_array();

            foreach ($proposals as $proposal) {
                $this->proposals_model->delete($proposal['id']);
            }

            // Get related tasks
            $this->db->where('rel_type', 'lead');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }

            if (is_gdpr()) {
                $this->db->where('(description LIKE "%' . $lead->email . '%" OR description LIKE "%' . $lead->name . '%" OR description LIKE "%' . $lead->phonenumber . '%")');
                $this->db->delete(db_prefix() . 'activity_log');
            }

            $affectedRows++;
        }
        if ($affectedRows > 0) {
            hooks()->do_action('after_lead_deleted', $id);
            return true;
        }

        return false;
    }

    /**
     * Mark lead as lost
     * @param  mixed $id lead id
     * @return boolean
     */
    public function mark_as_lost($id)
    {
        $this->db->select('status');
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('id', $id);
        $last_lead_status = $this->db->get()->row()->status;
		
		$status_name = "Lost";
		$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>"Lost"))->row();
		$status_id = $check_status->id;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', [
            'lost'               => 1,
            'status'             => $status_id,
            'last_status_change' => date('Y-m-d H:i:s'),
            'last_lead_status'   => $last_lead_status,
        ]);

        if ($this->db->affected_rows() > 0) {
            $this->log_lead_activity($id, 'not_lead_activity_marked_lost');
			
			$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
				"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
				"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
				"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
				"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
			);

			
			$this->lead_journey_log_event($id, $status_name,$status_id, $communication_data);
			
			
			$this->load->model('client/client_model');
			
			$add_lead_patient_status = array(
				"leadid" => $id,
				"status" => $status_id,
				"datetime" => date('Y-m-d H:i:s')
			);
			$this->client_model->add_lead_patient_status($add_lead_patient_status);
			
			

            log_activity('Lead Marked as Lost [ID: ' . $id . ']');

            hooks()->do_action('lead_marked_as_lost', $id);

            return true;
        }

        return false;
    }

    /**
     * Unmark lead as lost
     * @param  mixed $id leadid
     * @return boolean
     */
    public function unmark_as_lost($id)
    {
        $this->db->select('last_lead_status');
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('id', $id);
        $last_lead_status = $this->db->get()->row()->last_lead_status;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', [
            'lost'   => 0,
            'status' => $last_lead_status,
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->log_lead_activity($id, 'not_lead_activity_unmarked_lost');

            log_activity('Lead Unmarked as Lost [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Mark lead as junk
     * @param  mixed $id lead id
     * @return boolean
     */
    public function mark_as_junk($id)
    {
        $this->db->select('status');
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('id', $id);
        $last_lead_status = $this->db->get()->row()->status;
		
		$status_name = "Junk";
		$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("name"=>"Junk"))->row();
		$status_id = $check_status->id;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', [
            'junk'               => 1,
            'status'             => $status_id,
            'last_status_change' => date('Y-m-d H:i:s'),
            'last_lead_status'   => $last_lead_status,
        ]);

        if ($this->db->affected_rows() > 0) {
            $this->log_lead_activity($id, 'not_lead_activity_marked_junk');

            log_activity('Lead Marked as Junk [ID: ' . $id . ']');
			
			$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
				"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
				"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
				"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
				"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
			);

			
			
			$this->lead_journey_log_event($id, $status_name, $status_id, $communication_data);
			
			$this->load->model('client/client_model');
			
			$add_lead_patient_status = array(
				"leadid" => $id,
				"status" => $status_id,
				"datetime" => date('Y-m-d H:i:s')
			);
			$this->client_model->add_lead_patient_status($add_lead_patient_status);

            hooks()->do_action('lead_marked_as_junk', $id);

            return true;
        }

        return false;
    }

    /**
     * Unmark lead as junk
     * @param  mixed $id leadid
     * @return boolean
     */
    public function unmark_as_junk($id)
    {
        $this->db->select('last_lead_status');
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('id', $id);
        $last_lead_status = $this->db->get()->row()->last_lead_status;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', [
            'junk'   => 0,
            'status' => $last_lead_status,
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->log_lead_activity($id, 'not_lead_activity_unmarked_junk');
            log_activity('Lead Unmarked as Junk [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get lead attachments
     * @since Version 1.0.4
     * @param  mixed $id lead id
     * @return array
     */
    public function get_lead_attachments($id = '', $attachment_id = '', $where = [])
    {
        $this->db->where($where);
        $idIsHash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;
        if (is_numeric($attachment_id) || $idIsHash) {
            $this->db->where($idIsHash ? 'attachment_key' : 'id', $attachment_id);

            return $this->db->get(db_prefix() . 'files')->row();
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'lead');
        $this->db->order_by('dateadded', 'DESC');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    public function add_attachment_to_database($lead_id, $attachment, $external = false, $form_activity = false)
    {
        $this->misc_model->add_attachment_to_database($lead_id, 'lead', $attachment, $external);

        if ($form_activity == false) {
            $this->leads_model->log_lead_activity($lead_id, 'not_lead_activity_added_attachment');
        } else {
            $this->leads_model->log_lead_activity($lead_id, 'not_lead_activity_log_attachment', true, serialize([
                $form_activity,
            ]));
        }

        // No notification when attachment is imported from web to lead form
        if ($form_activity == false) {
            $lead         = $this->get($lead_id);
            $not_user_ids = [];
            if ($lead->addedfrom != get_staff_user_id()) {
                array_push($not_user_ids, $lead->addedfrom);
            }
            if ($lead->assigned != get_staff_user_id() && $lead->assigned != 0) {
                array_push($not_user_ids, $lead->assigned);
            }
            $notifiedUsers = [];
            foreach ($not_user_ids as $uid) {
                $notified = add_notification([
                    'description'     => 'not_lead_added_attachment',
                    'touserid'        => $uid,
                    'link'            => '#leadid=' . $lead_id,
                    'additional_data' => serialize([
                        $lead->name,
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $uid);
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }
    }

    /**
     * Delete lead attachment
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_lead_attachment($id)
    {
        $attachment = $this->get_lead_attachments('', $id);
        $deleted    = false;

        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('lead') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Lead Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('lead') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('lead') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('lead') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    // Sources

    /**
     * Get leads sources
     * @param  mixed $id Optional - Source ID
     * @return mixed object if id passed else array
     */
    public function get_source($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'leads_sources')->row();
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'leads_sources')->result_array();
    }

    /**
     * Add new lead source
     * @param mixed $data source data
     */
    public function add_source($data)
    {
        $this->db->insert(db_prefix() . 'leads_sources', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Leads Source Added [SourceID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update lead source
     * @param  mixed $data source data
     * @param  mixed $id   source id
     * @return boolean
     */
    public function update_source($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads_sources', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Leads Source Updated [SourceID: ' . $id . ', Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete lead source from database
     * @param  mixed $id source id
     * @return mixed
     */
    public function delete_source($id)
    {
        $current = $this->get_source($id);
        // Check if is already using in table
        if (is_reference_in_table('source', db_prefix() . 'leads', $id) || is_reference_in_table('lead_source', db_prefix() . 'leads_email_integration', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'leads_sources');
        if ($this->db->affected_rows() > 0) {
            if (get_option('leads_default_source') == $id) {
                update_option('leads_default_source', '');
            }
            log_activity('Leads Source Deleted [SourceID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Statuses

    /**
     * Get lead statuses
     * @param  mixed $id status id
     * @return mixed      object if id passed else array
     */
    public function get_status($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where($where);
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'leads_status')->row();
        }

        $whereKey = md5(serialize($where));
      
        $statuses = $this->app_object_cache->get('leads-all-statuses-'.$whereKey);

        if (!$statuses) {
            $this->db->where($where);
            $this->db->order_by('statusorder', 'asc');

            $statuses = $this->db->get(db_prefix() . 'leads_status')->result_array();
            $this->app_object_cache->add('leads-all-statuses-'.$whereKey, $statuses);
        }

        return $statuses;
    }

    /**
     * Add new lead status
     * @param array $data lead status data
     */
    public function add_status($data)
    {
        if (isset($data['color']) && $data['color'] == '') {
            $data['color'] = hooks()->apply_filters('default_lead_status_color', '#757575');
        }

        if (!isset($data['statusorder'])) {
            $data['statusorder'] = total_rows(db_prefix() . 'leads_status') + 1;
        }

        $this->db->insert(db_prefix() . 'leads_status', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Leads Status Added [StatusID: ' . $insert_id . ', Name: ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_status($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads_status', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Leads Status Updated [StatusID: ' . $id . ', Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete lead status from database
     * @param  mixed $id status id
     * @return boolean
     */
    public function delete_status($id)
    {
        $current = $this->get_status($id);
        // Check if is already using in table
        if (is_reference_in_table('status', db_prefix() . 'leads', $id) || is_reference_in_table('lead_status', db_prefix() . 'leads_email_integration', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'leads_status');
        if ($this->db->affected_rows() > 0) {
            if (get_option('leads_default_status') == $id) {
                update_option('leads_default_status', '');
            }
            log_activity('Leads Status Deleted [StatusID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Update canban lead status when drag and drop
     * @param  array $data lead data
     * @return boolean
     */
    public function update_lead_status($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['leadid']);
        $_old = $this->db->get(db_prefix() . 'leads')->row();

        $old_status = '';

        if ($_old) {
            $old_status = $this->get_status($_old->status);
            if ($old_status) {
                $old_status = $old_status->name;
            }
        }

        $affectedRows   = 0;
        $current_status = $this->get_status($data['status'])->name;

        $this->db->where('id', $data['leadid']);
        $this->db->update(db_prefix() . 'leads', [
            'status' => $data['status'],
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message    = 'not_lead_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);

                hooks()->do_action('lead_status_changed', [
                    'lead_id'    => $data['leadid'],
                    'old_status' => $old_status,
                    'new_status' => $current_status,
                ]);
            }
            $this->db->where('id', $data['leadid']);
            $this->db->update(db_prefix() . 'leads', [
                'last_status_change' => date('Y-m-d H:i:s'),
            ]);
        }

        if (isset($data['order'])) {
            AbstractKanban::updateOrder($data['order'], 'leadorder', 'leads', $data['status']);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }

            $this->log_lead_activity($data['leadid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }

    /* Ajax */

    /**
     * All lead activity by staff
     * @param  mixed $id lead id
     * @return array
     */
    public function get_lead_activity_log($id)
    {
        $sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'ASC');

        $this->db->where('leadid', $id);
        $this->db->order_by('date', $sorting);

        return $this->db->get(db_prefix() . 'lead_activity_log')->result_array();
    }

    public function staff_can_access_lead($id, $staff_id = '')
    {
        $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;

        if (has_permission('leads', $staff_id, 'view')) {
            return true;
        }

        $CI = &get_instance();

        if (total_rows(db_prefix() . 'leads', 'id="' . $CI->db->escape_str($id) . '" AND (assigned=' . $CI->db->escape_str($staff_id) . ' OR is_public=1 OR addedfrom=' . $CI->db->escape_str($staff_id) . ')') > 0) {
            return true;
        }

        return false;
    }

    /**
     * Add lead activity from staff
     * @param  mixed  $id          lead id
     * @param  string  $description activity description
     */
    public function log_lead_activity($id, $description, $integration = false, $additional_data = '')
    {
        $log = [
            'date'            => date('Y-m-d H:i:s'),
            'description'     => $description,
            'leadid'          => $id,
            'staffid'         => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];
        if ($integration == true) {
            $log['staffid']   = 0;
            $log['full_name'] = '[CRON]';
        }

        $this->db->insert(db_prefix() . 'lead_activity_log', $log);

        return $this->db->insert_id();
    }

    /**
     * Get email integration config
     * @return object
     */
    public function get_email_integration()
    {
        $this->db->where('id', 1);

        return $this->db->get(db_prefix() . 'leads_email_integration')->row();
    }

    /**
     * Get lead imported email activity
     * @param  mixed $id leadid
     * @return array
     */
    public function get_mail_activity($id)
    {
        $this->db->where('leadid', $id);
        $this->db->order_by('dateadded', 'asc');

        return $this->db->get(db_prefix() . 'lead_integration_emails')->result_array();
    }

    /**
     * Update email integration config
     * @param  mixed $data All $_POST data
     * @return boolean
     */
    public function update_email_integration($data)
    {
        $this->db->where('id', 1);
        $original_settings = $this->db->get(db_prefix() . 'leads_email_integration')->row();

        $data['create_task_if_customer']        = isset($data['create_task_if_customer']) ? 1 : 0;
        $data['active']                         = isset($data['active']) ? 1 : 0;
        $data['delete_after_import']            = isset($data['delete_after_import']) ? 1 : 0;
        $data['notify_lead_imported']           = isset($data['notify_lead_imported']) ? 1 : 0;
        $data['only_loop_on_unseen_emails']     = isset($data['only_loop_on_unseen_emails']) ? 1 : 0;
        $data['notify_lead_contact_more_times'] = isset($data['notify_lead_contact_more_times']) ? 1 : 0;
        $data['mark_public']                    = isset($data['mark_public']) ? 1 : 0;
        $data['responsible']                    = !isset($data['responsible']) ? 0 : $data['responsible'];

        if ($data['notify_lead_contact_more_times'] != 0 || $data['notify_lead_imported'] != 0) {
            if (isset($data['notify_type']) && $data['notify_type'] == 'specific_staff') {
                if (isset($data['notify_ids_staff'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_staff']);
                    unset($data['notify_ids_staff']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_staff']);
                }
                if (isset($data['notify_ids_roles'])) {
                    unset($data['notify_ids_roles']);
                }
            } else {
                if (isset($data['notify_ids_roles'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_roles']);
                    unset($data['notify_ids_roles']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_roles']);
                }
                if (isset($data['notify_ids_staff'])) {
                    unset($data['notify_ids_staff']);
                }
            }
        } else {
            $data['notify_ids']  = serialize([]);
            $data['notify_type'] = null;
            if (isset($data['notify_ids_staff'])) {
                unset($data['notify_ids_staff']);
            }
            if (isset($data['notify_ids_roles'])) {
                unset($data['notify_ids_roles']);
            }
        }

        // Check if not empty $data['password']
        // Get original
        // Decrypt original
        // Compare with $data['password']
        // If equal unset
        // If not encrypt and save
        if (!empty($data['password'])) {
            $or_decrypted = $this->encryption->decrypt($original_settings->password);
            if ($or_decrypted == $data['password']) {
                unset($data['password']);
            } else {
                $data['password'] = $this->encryption->encrypt($data['password']);
            }
        }

        $this->db->where('id', 1);
        $this->db->update(db_prefix() . 'leads_email_integration', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function change_status_color($data)
    {
        $this->db->where('id', $data['status_id']);
        $this->db->update(db_prefix() . 'leads_status', [
            'color' => $data['color'],
        ]);
    }

    public function update_status_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update(db_prefix() . 'leads_status', [
                'statusorder' => $status[1],
            ]);
        }
    }

    public function get_form($where)
    {
        $this->db->where($where);

        return $this->db->get(db_prefix() . 'web_to_lead')->row();
    }

    public function add_form($data)
    {
        $data                       = $this->_do_lead_web_to_form_responsibles($data);
        $data['success_submit_msg'] = nl2br($data['success_submit_msg']);
        $data['form_key']           = app_generate_hash();

        $data['create_task_on_duplicate'] = (int) isset($data['create_task_on_duplicate']);
        $data['mark_public']              = (int) isset($data['mark_public']);

        if (isset($data['allow_duplicate'])) {
            $data['allow_duplicate']           = 1;
            $data['track_duplicate_field']     = '';
            $data['track_duplicate_field_and'] = '';
            $data['create_task_on_duplicate']  = 0;
        } else {
            $data['allow_duplicate'] = 0;
        }

        $data['dateadded'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'web_to_lead', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Web to Lead Form Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_form($id, $data)
    {
        $data                       = $this->_do_lead_web_to_form_responsibles($data);
        $data['success_submit_msg'] = nl2br($data['success_submit_msg']);

        $data['create_task_on_duplicate'] = (int) isset($data['create_task_on_duplicate']);
        $data['mark_public']              = (int) isset($data['mark_public']);

        if (isset($data['allow_duplicate'])) {
            $data['allow_duplicate']           = 1;
            $data['track_duplicate_field']     = '';
            $data['track_duplicate_field_and'] = '';
            $data['create_task_on_duplicate']  = 0;
        } else {
            $data['allow_duplicate'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'web_to_lead', $data);

        return ($this->db->affected_rows() > 0 ? true : false);
    }

    public function delete_form($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'web_to_lead');

        $this->db->where('from_form_id', $id);
        $this->db->update(db_prefix() . 'leads', [
            'from_form_id' => 0,
        ]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Lead Form Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    private function _do_lead_web_to_form_responsibles($data)
    {
        if (isset($data['notify_lead_imported'])) {
            $data['notify_lead_imported'] = 1;
        } else {
            $data['notify_lead_imported'] = 0;
        }

        if ($data['responsible'] == '') {
            $data['responsible'] = 0;
        }
        if ($data['notify_lead_imported'] != 0) {
            if ($data['notify_type'] == 'specific_staff') {
                if (isset($data['notify_ids_staff'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_staff']);
                    unset($data['notify_ids_staff']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_staff']);
                }
                if (isset($data['notify_ids_roles'])) {
                    unset($data['notify_ids_roles']);
                }
            } else {
                if (isset($data['notify_ids_roles'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_roles']);
                    unset($data['notify_ids_roles']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_roles']);
                }
                if (isset($data['notify_ids_staff'])) {
                    unset($data['notify_ids_staff']);
                }
            }
        } else {
            $data['notify_ids']  = serialize([]);
            $data['notify_type'] = null;
            if (isset($data['notify_ids_staff'])) {
                unset($data['notify_ids_staff']);
            }
            if (isset($data['notify_ids_roles'])) {
                unset($data['notify_ids_roles']);
            }
        }

        return $data;
    }

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Leads_model::do_kanban_query', '2.9.2', 'LeadsKanban class');

        $kanBan = (new LeadsKanban($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }
	//New Code
	public function get_branches(){
		//$this->db->where($where);

        return $this->db->get(db_prefix() . 'customers_groups')->result_array();
	}
	public function get_treatments(){
		//$this->db->where($where);
        return $this->db->get(db_prefix() . 'treatment')->result_array();
	}
	public function get_patient_response(){
		//$this->db->where($where);
        return $this->db->get(db_prefix() . 'patient_response')->result_array();
	}
	public function get_appointment_type(){
		//$this->db->where($where);
        return $this->db->get(db_prefix() . 'appointment_type')->result_array();
	}
	public function get_doctors() {
		$this->db->select('staff.*');
		$this->db->from(db_prefix() . 'staff as staff');
		$this->db->join(db_prefix() . 'roles as roles', 'staff.role = roles.roleid', 'left');
		$this->db->where('LOWER(roles.name)', 'doctor'); // Case-insensitive match
		return $this->db->get()->result_array();
	}

	public function get_lead_with_doctor($id){
		$this->db->select("a.*, branch.name as branch_name, i.description");
		$this->db->from(db_prefix() . 'clients as c');
		$this->db->join(db_prefix() . 'appointment as a', "a.userid=c.userid", "left");
		$this->db->join(db_prefix() . 'items as i', "i.id=a.treatment_id", "left");
		$this->db->join(db_prefix() . 'customers_groups as branch', "branch.id=a.branch_id", "left");
		
		$this->db->where(array("c.leadid"=>$id));
        return $this->db->get()->row();
	}
	
    public function get_state($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('state_id', $id);

            return $this->db->get(db_prefix() . 'state')->row();
        }

        $this->db->order_by('state_name', 'asc');

        return $this->db->get(db_prefix() . 'state')->result_array();
    }
    public function get_city($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('city_id', $id);

            return $this->db->get(db_prefix() . 'city')->row();
        }

        $this->db->order_by('city_name', 'asc');

        return $this->db->get(db_prefix() . 'city')->result_array();
    }
	
    public function get_consultation_fee($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('consultation_fee_id', $id);

            return $this->db->get(db_prefix() . 'consultation_fee')->row();
        }

        return $this->db->get(db_prefix() . 'consultation_fee')->result_array();
    }
	
	public function get_pincodes(){
		return $this->db->get(db_prefix() . 'pincode')->result_array();
	}
	
	public function get_staff_list()
	{
		$this->db->select('staffid as id, CONCAT(firstname, " ", lastname) as name');
		$this->db->from(db_prefix() . 'staff');
		$this->db->where('active', 1);
		return $this->db->get()->result_array();
	}

	public function get_patient_list()
	{
		$this->db->select('userid as id, company as name');
		$this->db->from(db_prefix() . 'clients');
		$this->db->where('active', 1);
		return $this->db->get()->result_array();
	}

	
}
