<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_call_log_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get all logs for a specific lead
    public function get_logs_by_lead($lead_id)
    {
        $this->db->where('leads_id', $lead_id);
        $this->db->order_by('created_date', 'DESC');
        return $this->db->get(db_prefix() . 'lead_call_logs')->result_array();
    }

    // Add a new call log entry
    public function add_lead_call_log($data)
    {
        $insert = [
            'created_date'        => date('Y-m-d H:i:s'),
            'branch_id'           => $data['branch_id'] ?? 0,
            'enquired_by'         => get_staff_user_id(),
            'appointment_date'    => date("Y-m-d H:i:s", strtotime($data['appointment_date'])),
            'doctor_id'           => $data['doctor_id'],
            'consultation_fee'    => $data['item_select'],
            'paid_amount'    	=> $data['payment_amount'],
            'followup_date'       => $data['followup_date'],
            'patient_response_id' => $data['patient_response_id'],
            'leads_id'            => $data['leads_id'],
            'comments'            => $data['comments']
        ];
		
		
        $this->db->insert(db_prefix() . 'lead_call_logs', $insert);
		$lead_call_log_id = $this->db->insert_id();
		
		$this->db->where('id', $data['leads_id']);
        $this->db->update(db_prefix() . 'leads', [
            'status'             => $data['patient_response_id'],
            'last_status_change' => date('Y-m-d H:i:s'),
        ]);
		
		$this->load->model('leads_model');
		
       $this->leads_model->log_lead_activity($data['leads_id'], 'Call Log Added');
	   
		$get_assigned_staff = $this->db->get_where(db_prefix() . 'leads', array('id'=>$data['leads_id']))->row();
		if($get_assigned_staff){
			$assigned_staff = $get_assigned_staff->assigned;
		}else{
			$assigned_staff = 1;
		}
		$staff_name = get_staff_full_name($assigned_staff);
	   //Sending SMS based on the Status only (Enquiry, No Response)
	   $branch_address = get_option('invoice_company_address');
	   if($data['doctor_id']){
		   $assigned_doctor = get_staff_full_name($data['doctor_id']);
	   }
	   
		$communication_data = array(
			"followup_date" => date("d-m-Y", strtotime($data['followup_date'])),
			"staff_name" => $staff_name,
			"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
			"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
			"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
			"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
			"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
		);

		$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("id"=>$data['patient_response_id']))->row();
		$status_name = "Patient";
		if($check_status){
			$status_name = $check_status->name;
			//if($status_name != "Paid Appointment"){
				$this->leads_model->lead_journey_log_event($data['leads_id'], $status_name, $data['patient_response_id'], $communication_data);
				
			//}
			
		}
		
	   if(!empty($data['followup_date'])){
			$this->load->model('misc_model');
			$reminder_data = array(
			"rel_type" => "lead",
			"rel_id" => $data['leads_id'],
			"date" => $data['followup_date']." 10:00:00",
			"staff" => get_staff_user_id(),
			"notify_by_email" => 1,
			"description" => $data['comments']
			);
			$this->misc_model->add_reminder($reminder_data, $data['leads_id']);
			
		}
		
		if($data['doctor_id'] != NULL AND $data['appointment_date'] != NULL AND $data['leads_id'] != NULL){
			$status_id = $data['patient_response_id'];
			$data['branch'] = $data['branch_id'];
			
			$this->convert_patient($data, $lead_call_log_id, $status_id, $status_name);
			
			$this->leads_model->log_lead_activity($data['leads_id'], 'Appointment Created');
			
		}else{
			$this->load->model('client/client_model');
			$add_lead_patient_status = array(
				"leadid" => $data['leads_id'],
				"status" => $data['patient_response_id'],
				"datetime" => date('Y-m-d H:i:s')
			);
			$this->client_model->add_lead_patient_status($add_lead_patient_status);
			return $this->db->insert_id();
		}
		 
    }

    // Delete a specific call log entry
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'lead_call_logs');
        return $this->db->affected_rows() > 0;
    }
	
	public function get_branches($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'customers_groups');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
	public function get_doctors($id = '')
	{
		$this->db->select('d.*, role.*'); // Add more fields if needed
		$this->db->from(db_prefix() . 'staff d');
		$this->db->join(db_prefix() . 'roles role', 'role.roleid = d.role', 'left');
	
		// Optional additional where filter
		if (!empty($where)) {
			$this->db->where($where);
		}
        $this->db->where(array("LOWER(role.name)"=>'doctor', "active"=>1));
        if($id){
            $this->db->where(array("d.staffid"=>$id));
            return $this->db->get()->row();
        }else{
            return $this->db->get()->result_array();
        }
		
		
	}
	public function get_patient_responses()
    {
		$this->db->select('*');
        $this->db->from(db_prefix() . 'patient_response');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
	}
	
	public function get_treatments()
    {
		$this->db->select('*');
        $this->db->from(db_prefix() . 'treatment');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
	}
	
	public function get_patient_response_setting(){
		 return $this->db->get_where(db_prefix() . 'master_settings', array("title"=>"lead_call_log_patient_response"))->row();
	}
	
	public function convert_patient($request, $lead_call_log_id, $status_id = NULL, $status_name = NULL){
		
		$this->load->model('client/client_model');
		$branch_id = $request['branch'];
		
		$get_leads_data = $this->db->get_where(db_prefix() . 'leads', array("id"=>$request['leads_id']))->row();
		
		if($get_leads_data){
			$company = $get_leads_data->name;
			$phonenumber = $get_leads_data->phonenumber;
			$area = $get_leads_data->area;
			$lead_age = $get_leads_data->lead_age;
			$lead_gender = $get_leads_data->lead_gender;
			$email = $get_leads_data->email;
			$country = $get_leads_data->country;
			$zip = $get_leads_data->zip;
			$city = $get_leads_data->city;
			$state = $get_leads_data->state;
			$patient_source_id = $get_leads_data->source;
			
			$check_user = $this->db->get_where(db_prefix() . 'clients', array("phonenumber"=>$phonenumber, "company"=>"$company"))->row();
			if(!$check_user){
				$data = array(
					"company" => $company,
					"phonenumber" => $phonenumber,
					"address" => $area,
					"country" => $country,
					"city" => $city,
					"zip" => $zip,
					"state" => $state,
					"datecreated" => date('Y-m-d H:i:s'),
				);
				
				$data['leadid'] = $request['leads_id'];
				$this->db->insert(db_prefix() . 'clients', $data);
				$client_id = $this->db->insert_id();
				
				
				$update_lead = array(
				"date_converted" => date('Y-m-d H:i:s')
				);
				
				//$update_lead['status']
				$this->db->where(array("id"=>$request['leads_id']));
				$this->db->update(db_prefix() . 'leads', $update_lead);
				
				$add_lead_patient_status = array(
					"userid" => $client_id,
					"leadid" => $request['leads_id'],
					"status" => $status_id,
					"datetime" => date('Y-m-d H:i:s')
				);
				$this->client_model->add_lead_patient_status($add_lead_patient_status);
				
				
				$return = 1;
				$description = "new_patient_added";
				$this->client_model->log_patient_activity($client_id, $description);
				
				
				
				//Inserting patient other fields
				$clients_new_fields_data = array(
					'userid'      => $client_id,
					'email_id'  	=> $email,
					'area'  		=> $area,
					'age'         	=> $lead_age,
					'gender'        => $lead_gender,
					'patient_status'=> 'Active',
					'whatsapp_number'=> $phonenumber,
					'patient_source_id'=> $patient_source_id,
					'pincode'=> $zip,
					'current_status' => $status_name
				);
			
				$this->db->insert(db_prefix() . 'clients_new_fields', $clients_new_fields_data);
				
				$group_data = array(
                "groupid" => $branch_id,
                "customer_id"=> $client_id
				);
				$table_suffix = "customer_groups";
				$this->db->insert(db_prefix() . $table_suffix, $group_data);
				
				
				$attachment_path = null;

				if (!empty($_FILES['attachment']['name'])) {
					$this->load->library('upload');
					$upload_path = 'uploads/appointment_attachments/';

					if (!is_dir($upload_path)) {
						mkdir($upload_path, 0755, true);
					}

					$_FILES['file']['name']     = $_FILES['attachment']['name'];
					$_FILES['file']['type']     = $_FILES['attachment']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['attachment']['tmp_name'];
					$_FILES['file']['error']    = $_FILES['attachment']['error'];
					$_FILES['file']['size']     = $_FILES['attachment']['size'];

					$config['upload_path']   = $upload_path;
					$config['allowed_types'] = '*'; // or set to 'jpg|jpeg|png|pdf|doc|docx'
					$config['file_name']     = uniqid();

					$this->upload->initialize($config);

					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$attachment_path = $upload_path . $upload_data['file_name'];
					}
				}

				// Now assign to DB field
				$attachment = $attachment_path;

				
				// Now assign to DB field
				$attachment = $attachment_path;

				$patient_response_id = $request['patient_response_id'] ?? null;
				$doctor_id           = $request['doctor_id'] ?? null;
				$appointment_type_id = $request['appointment_type_id'] ?? null;
				$comments            = $request['comments'] ?? '';
				$followup_date       = $request['followup_date'] ?? null;
				$appointment_date    = $request['appointment_date'] ?? null;
				$paymentmode         = $request['paymentmode'] ?? null;
				$payment_amount      = $request['payment_amount'] ?? null;
				$total_amount        = $request['item_select'] ?? null;

				// Safely convert dates if available
				$next_calling_date = $followup_date ? date('Y-m-d', strtotime($followup_date)) : null;
				$formatted_appointment_date = $appointment_date ? date('Y-m-d H:i:s', strtotime($appointment_date)) : null;

				// Create appointment data array
				$appointment_data = array(
					'userid'              => $client_id,
					'patient_response_id' => $patient_response_id,
					'branch_id'           => $branch_id,
					'treatment_id'        => $this->input->post('treatment_id'),
					'consultation_fee_id' => $this->input->post('item_select'),
					'enquiry_doctor_id'   => $doctor_id,
					'unit_doctor_id'      => $doctor_id,
					'appointment_type_id' => $appointment_type_id,
					'remarks'             => $comments,
					'attachment'          => $attachment,
					'next_calling_date'   => $next_calling_date,
					'appointment_date'    => $formatted_appointment_date,
					'created_by'          => get_staff_user_id(),
					'created_at'          => date('Y-m-d H:i:s')
				);

				// ✅ Duplicate restriction check
				if (staff_can('multiple_appointments_restriction', 'customers')) {
					$appointment_date_only = date('Y-m-d', strtotime($formatted_appointment_date));

					$this->db->where('userid', $client_id);
					$this->db->where('branch_id', $branch_id);
					$this->db->where('DATE(appointment_date) =', $appointment_date_only);

					$duplicate = $this->db->get(db_prefix() . 'appointment')->row();

					if ($duplicate) {
						// stop insert
						$appointment_id = 0;
						return 3;
					}
				}

			// ✅ Insert only if no duplicate found
			$this->db->insert(db_prefix() . 'appointment', $appointment_data);
            $insert_id = $this->db->insert_id();
			$appointment_id = 0;
			$assigned_doctor = get_staff_full_name($doctor_id);
			if ($insert_id) {
				$appointment_id = $insert_id;
				$update_call_log = array(
				"appointment_id" => $appointment_id
				);
				$this->db->where(array("id"=>$lead_call_log_id));
				$this->db->update(db_prefix() . 'lead_call_logs', $update_call_log);
				
				$appointment_date = date('d-m-Y', strtotime($appointment_date));
				$appointment_time = date('h:i A', strtotime($appointment_date)); // 12-hour format with AM/PM
				$date_time = date('d-m-Y h:i A', strtotime($appointment_date));   // Full date-time with AM/PM

				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"consult_paid" => $payment_amount,
				"assigned_doctor" => $assigned_doctor,
				"appointment_date" => $appointment_date,
				"appointment_time" => $appointment_time,
				"date_time" => $date_time,
				"branch_address" => $branch_address,
				);
				$this->client_model->patient_journey_log_event($client_id, 'appointment_created', 'Appointment Created', $communication_data, $patient_response_id);
				
				
			}

			$this->load->model('invoices_model');
		
			$year = date('Y');

			$this->db->from('tblinvoices');
			$this->db->where('YEAR(date)', $year);
			$count = $this->db->count_all_results();

			$next_number = $count + 1;
			$invoice_number = 'INV-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
			
			
			if($payment_amount){
				$paying_amount = $payment_amount;
			}else{
				$paying_amount = 0;
			}

			if($total_amount){
				$due_amount = $total_amount - $paying_amount;
			}else{
				$due_amount = 0;
			}
			
			$invoice_data['formatted_number'] = $invoice_number;
			$invoice_data['number'] = $next_number;
			$invoice_data['clientid'] = $client_id;
			$invoice_data['show_shipping_on_invoice'] = 1;
			$invoice_data['date'] = date('Y-m-d');
			$invoice_data['duedate'] = date('Y-m-d');
			$invoice_data['currency'] = 1;
			$invoice_data['addedfrom'] = get_staff_user_id();
			$invoice_data['subtotal'] = $paying_amount + $due_amount;
			$invoice_data['total'] = $paying_amount + $due_amount;
			$invoice_data['prefix'] = "INV-";
			$invoice_data['number_format'] = 1;
			$invoice_data['billing_street'] = 0;
			
			
			$invoice_data['allowed_payment_modes'] = 'a:1:{i:0;s:1:"1";}';
			
			$invoice_data['datecreated'] = date('Y-m-d H:i:s');
			
			$id = $this->invoices_model->add($invoice_data); 
			
			if($paying_amount>=0){
				if($due_amount == 0){
					$status = 2;
				}else{
					$status = 3;
				}
			}else{
				$status = 1;
			}
			
			$update = array(
			'allowed_payment_modes' => 'a:1:{i:0;s:1:"1";}',
			'status' => $status,
			);
			$this->db->where(array("id"=>$id));
			$this->db->update(db_prefix() . 'invoices', $update);
			
			if($due_amount == 0){
				//$this->client_model->confirm_booking($appointment_id);
			}
			
			
		   $itemable= array(
			"rel_id" => $id,
			"rel_type" => "invoice",
			"description" => "Consultation Fee",
			"qty" => 1,
			"item_order" => 1,
			"rate"=>$paying_amount + $due_amount
			);
			
			$this->db->insert(db_prefix() . 'itemable', $itemable);
			
			$appointment_invoice_data = array(
			"invoice_id" => $id
			);
			$this->db->where(array("appointment_id"=>$appointment_id));
			$this->db->update(db_prefix() . 'appointment', $appointment_invoice_data);
			
			
			if($paying_amount>0){
			   $invoicepaymentrecords= array(
				"invoiceid" => $id,
				"amount" => $paying_amount,
				"paymentmode" => $paymentmode,
				"date" => date('Y-m-d'),
				"received_by" =>  get_staff_user_id(),
				"daterecorded" => date('Y-m-d H:i:s'),
				);
				
				$this->db->insert(db_prefix() . 'invoicepaymentrecords', $invoicepaymentrecords);
				
			}

			
			
            $description = "new_appointment_added";
            $this->client_model->log_patient_activity($insert_id, $description);
				
			$branch_address = get_option('invoice_company_address');
			$communication_data = array(
			"consult_paid" => $payment_amount,
			"assigned_doctor" => $assigned_doctor,
			"appointment_date" => $appointment_date,
			"appointment_time" => $appointment_time,
			"date_time" => $date_time,
			"branch_address" => $branch_address,
			"consult_paid" => $payment_amount,
			);
			if($payment_amount>0){
				$this->client_model->patient_journey_log_event($client_id, 'paid_appointment', 'Appointment Created', $communication_data, $patient_response_id);
			}else{
				$this->client_model->patient_journey_log_event($client_id, 'on_appointment', 'Appointment Created', $communication_data, $patient_response_id);
			}
			
			}else{
				$client_id = $check_user->userid;
				
				$patient_response_id = $request['patient_response_id'] ?? null;
				$doctor_id           = $request['doctor_id'] ?? null;
				$comments            = $request['comments'] ?? null;
				$followup_date       = $request['followup_date'] ?? null;
				$appointment_date    = $request['appointment_date'] ?? null;
				$paymentmode         = $request['paymentmode'] ?? null;
				$payment_amount      = $request['payment_amount'] ?? null;
				$total_amount        = $request['item_select'] ?? null;

				
				$attachment_path = null;

				if (!empty($_FILES['attachment']['name'])) {
					$this->load->library('upload');
					$upload_path = 'uploads/appointment_attachments/';

					if (!is_dir($upload_path)) {
						mkdir($upload_path, 0755, true);
					}

					$_FILES['file']['name']     = $_FILES['attachment']['name'];
					$_FILES['file']['type']     = $_FILES['attachment']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['attachment']['tmp_name'];
					$_FILES['file']['error']    = $_FILES['attachment']['error'];
					$_FILES['file']['size']     = $_FILES['attachment']['size'];

					$config['upload_path']   = $upload_path;
					$config['allowed_types'] = '*'; // or set to 'jpg|jpeg|png|pdf|doc|docx'
					$config['file_name']     = uniqid();

					$this->upload->initialize($config);

					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$attachment_path = $upload_path . $upload_data['file_name'];
					}
				}

				// Now assign to DB field
				$attachment = $attachment_path;

				$patient_response_id = $request['patient_response_id'] ?? null;
				$appointment_type_id = $request['appointment_type_id'] ?? null;
				$doctor_id           = $request['doctor_id'] ?? null;
				$comments            = $request['comments'] ?? '';
				$followup_date       = $request['followup_date'] ?? null;
				$appointment_date    = $request['appointment_date'] ?? null;
				$paymentmode         = $request['paymentmode'] ?? null;
				$payment_amount      = $request['payment_amount'] ?? null;
				$total_amount        = $request['item_select'] ?? null;

				// Safely convert dates if available
				$next_calling_date = $followup_date ? date('Y-m-d', strtotime($followup_date)) : null;
				$formatted_appointment_date = $appointment_date ? date('Y-m-d H:i:s', strtotime($appointment_date)) : null;

				// Create appointment data array
				$appointment_data = array(
					'userid'                => $client_id,
					'patient_response_id'   => $patient_response_id,
					'appointment_type_id'   => $appointment_type_id,
					'branch_id'             => $branch_id,
					'treatment_id'          => $this->input->post('treatment_id'),
					'consultation_fee_id'   => $this->input->post('item_select'),
					'enquiry_doctor_id'     => $doctor_id,
					'unit_doctor_id'        => $doctor_id,
					'remarks'               => $comments,
					'attachment'            => $attachment,
					'next_calling_date'     => $next_calling_date,
					'appointment_date'      => $formatted_appointment_date,
					'created_by'            => get_staff_user_id(),
					'created_at'            => date('Y-m-d H:i:s')
				);
			if (staff_can('multiple_appointments_restriction', 'customers')) {
				$appointment_date_only = date('Y-m-d', strtotime($formatted_appointment_date));

				$this->db->where('userid', $client_id);
				$this->db->where('branch_id', $branch_id);
				$this->db->where('DATE(appointment_date) =', $appointment_date_only);

				$duplicate = $this->db->get(db_prefix() . 'appointment')->row();

				if ($duplicate) {
					// stop insert
					$appointment_id = 0;
					return 3;
				}
			}
            $this->db->insert(db_prefix() . 'appointment', $appointment_data);
            $insert_id = $this->db->insert_id();
			$appointment_id = 0;
			
			if ($insert_id) {
				$appointment_id = $insert_id;
				$update_call_log = array(
				"appointment_id" => $appointment_id
				);
				$this->db->where(array("id"=>$lead_call_log_id));
				$this->db->update(db_prefix() . 'lead_call_logs', $update_call_log);
				
				
				$assigned_doctor = get_staff_full_name($doctor_id);
				$appointment_date = date('d-m-Y', strtotime($appointment_date));
				$appointment_time = date('h:i A', strtotime($appointment_date)); // 12-hour format with AM/PM
				$date_time = date('d-m-Y h:i A', strtotime($appointment_date));   // Full date-time with AM/PM

				$branch_address = get_option('invoice_company_address');
				$communication_data = array(
				"consult_paid" => $payment_amount,
				"assigned_doctor" => $assigned_doctor,
				"appointment_date" => $appointment_date,
				"appointment_time" => $appointment_time,
				"date_time" => $date_time,
				"branch_address" => $branch_address,
				);
				
				if($payment_amount>0){
					$this->client_model->patient_journey_log_event($client_id, 'paid_appointment', 'Appointment Created', $communication_data, $patient_response_id);
				}else{
					$this->client_model->patient_journey_log_event($client_id, 'on_appointment', 'Appointment Created', $communication_data, $patient_response_id);
				}
				
				
				
			}

			$this->load->model('invoices_model');
		
			$year = date('Y');

			$this->db->from('tblinvoices');
			$this->db->where('YEAR(date)', $year);
			$count = $this->db->count_all_results();

			$next_number = $count + 1;
			$invoice_number = 'INV-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
			
			
			if($payment_amount){
				$paying_amount = $payment_amount;
			}else{
				$paying_amount = 0;
			}

			if($total_amount){
				$due_amount = $total_amount - $paying_amount;
			}else{
				$due_amount = 0;
			}
			$invoice_data['formatted_number'] = $invoice_number;
			$invoice_data['number'] = $next_number;
			$invoice_data['clientid'] = $client_id;
			$invoice_data['show_shipping_on_invoice'] = 1;
			$invoice_data['date'] = date('Y-m-d');
			$invoice_data['duedate'] = date('Y-m-d');
			$invoice_data['currency'] = 1;
			$invoice_data['addedfrom'] = get_staff_user_id();
			$invoice_data['subtotal'] = $paying_amount + $due_amount;
			$invoice_data['total'] = $paying_amount + $due_amount;
			$invoice_data['prefix'] = "INV-";
			$invoice_data['number_format'] = 1;
			$invoice_data['billing_street'] = 0;
			
			
			$invoice_data['allowed_payment_modes'] = 'a:1:{i:0;s:1:"1";}';
			
			$invoice_data['datecreated'] = date('Y-m-d H:i:s');
			
			$id = $this->invoices_model->add($invoice_data); 
			
			if($paying_amount>=0){
				if($due_amount == 0){
					$status = 2;
				}else{
					$status = 3;
				}
			}else{
				$status = 1;
			}
			
			$update = array(
			'allowed_payment_modes' => 'a:1:{i:0;s:1:"1";}',
			'status' => $status,
			);
			$this->db->where(array("id"=>$id));
			$this->db->update(db_prefix() . 'invoices', $update);
			
			if($due_amount == 0){
				//$this->client_model->confirm_booking($appointment_id);
			}
			
			$appointment_invoice_data = array(
			"invoice_id" => $id
			);
			$this->db->where(array("appointment_id"=>$appointment_id));
			$this->db->update(db_prefix() . 'appointment', $appointment_invoice_data);
			
		   $itemable= array(
			"rel_id" => $id,
			"rel_type" => "invoice",
			"description" => "Consultation Fee",
			"qty" => 1,
			"item_order" => 1,
			"rate"=>$paying_amount + $due_amount
			);
			
			$this->db->insert(db_prefix() . 'itemable', $itemable);
			
			if($paying_amount>0){
			   $invoicepaymentrecords= array(
				"invoiceid" => $id,
				"amount" => $paying_amount,
				"paymentmode" => $paymentmode,
				"date" => date('Y-m-d'),
				"received_by" =>  get_staff_user_id(),
				"daterecorded" => date('Y-m-d H:i:s'),
				);
				
				$this->db->insert(db_prefix() . 'invoicepaymentrecords', $invoicepaymentrecords);
				
			}

            $description = "new_appointment_added";
            $this->client_model->log_patient_activity($insert_id, $description);
			//$this->client_model->patient_journey_log_event($client_id, 'patient_created', 'New Patient Created');
			}
			
			$patient_response_id = $request['patient_response_id'];
			
			$this->load->model('leads_model');
			$statuses = $this->leads_model->get_status();
			$status_name = '';
			$status_id = '';
			foreach ($statuses as $status) {
				if ($patient_response_id == $status['id']) {
					$status_name = $status['name'];
					$status_id = $status['id'];
				
				}
			}
			
			$add_lead_patient_status = array(
				"userid" => $client_id,
				"leadid" => $request['leads_id'],
				"status" => $status_id,
				"datetime" => date('Y-m-d H:i:s')
			);
			$this->client_model->add_lead_patient_status($add_lead_patient_status);
		}
	}
	
	public function get_appointment_type(){
		//$this->db->where($where);
        return $this->db->get(db_prefix() . 'appointment_type')->result_array();
	}
}
