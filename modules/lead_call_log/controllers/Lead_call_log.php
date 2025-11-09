<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_call_log extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('lead_call_log_model');
    }

    // Show call logs for a lead
    public function view_logs($lead_id)
    {
        if (!has_permission('leads', '', 'view')) {
            access_denied('leads');
        }

        $data['title'] = _l('lead_call_logs');
        $data['logs'] = $this->lead_call_log_model->get_logs_by_lead($lead_id);
        $data['lead_id'] = $lead_id;

        $this->load->view('view_logs', $data);
    }

    // Handle call log form submission
    public function save()
    {
        $data = $this->input->post();

        if (!isset($data['leads_id'])) {
            set_alert('danger', _l('Lead ID is missing.'));
            redirect(admin_url('leads'));
        }

        $insert_id = $this->lead_call_log_model->add($data);

        if ($insert_id) {
            set_alert('success', _l('Call log added successfully.'));
        } else {
            set_alert('danger', _l('Failed to add call log.'));
        }

        redirect(admin_url('leads/profile/' . $data['leads_id'] . '?group=call_logs'));
    }

    // Optionally delete a log
    public function delete($id)
    {
        if (!has_permission('leads', '', 'delete')) {
            access_denied('leads');
        }

        $success = $this->lead_call_log_model->delete($id);

        if ($success) {
            set_alert('success', _l('Call log deleted'));
        } else {
            set_alert('danger', _l('Call log not found'));
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
	public function add_lead_call_log($rel_id)
	{
		$data = $this->input->post();
		$data['leads_id'] = $rel_id;
		$note_id = $this->lead_call_log_model->add_lead_call_log($data);
		redirect(admin_url('leads/index/' . $rel_id) . '#call_log');
	}
	
	public function get_tab_view($lead_id)
	{
		$CI =& get_instance();
		$data['lead'] = $CI->leads_model->get($lead_id);
		$data['lead_call_log'] = $CI->lead_call_log_model->get_logs_by_lead($lead_id);
		$data['branches'] = $CI->lead_call_log_model->get_branches();
		$data['doctors'] = $CI->lead_call_log_model->get_doctors();
		$data['treatments'] = $CI->lead_call_log_model->get_treatments();
		$data['appointment_type'] = $this->lead_call_log_model->get_appointment_type();
		$CI->load->model('invoice_items_model');
		
		$data['items_groups'] = $CI->invoice_items_model->get_groups();
		$data['items'] = $CI->invoice_items_model->get_grouped();
		$data['patient_response'] = $CI->lead_call_log_model->get_patient_responses();

		echo $CI->load->view(module_views_path('lead_call_log', 'tab_view'), $data, true); // return view as string
	}

	public function call_log_table($lead_id)
	{
		if (!staff_can('view_call_log', 'customers')) {
			ajax_access_denied();
		}
	$data['lead_id'] = $lead_id;
		
		echo $this->app->get_table_data(module_views_path('lead_call_log', 'tables/lead_call_log_table'), $data);
	}

	public function message_log_table($lead_id)
	{
		if (!staff_can('view_call_log', 'customers')) {
			ajax_access_denied();
		}
	$data['lead_id'] = $lead_id;
		
		echo $this->app->get_table_data(module_views_path('lead_call_log', 'tables/message_log_table'), $data);
	}

	public function get_feedback_table($lead_id)
	{
		if (!staff_can('view_call_log', 'customers')) {
			ajax_access_denied();
		}
	$data['lead_id'] = $lead_id;
		
		echo $this->app->get_table_data(module_views_path('lead_call_log', 'tables/feedback_table'), $data);
	}

	public function appointment_table($lead_id)
	{
		if (!staff_can('view_call_log', 'customers')) {
			ajax_access_denied();
		}
		$data['lead_id'] = $lead_id;
		
		echo $this->app->get_table_data(module_views_path('lead_call_log', 'tables/appointment_table'), $data);
	}
	
	public function change_status_direct($lead_id, $status_id)
	{
		if (!is_staff_logged_in()) {
			access_denied();
		}

		if (!$lead_id || !$status_id) {
			set_alert('danger', 'Invalid lead or status ID.');
			redirect(admin_url('leads'));
		}

		$this->db->where('id', $lead_id);
		$updated = $this->db->update(db_prefix() . 'leads', ['status' => $status_id]);
		
		$check_status = $this->db->get_where(db_prefix() . 'leads_status', array("id"=>$status_id))->row();
		if($check_status){
			$this->load->model('leads_model');
			$status_name = $check_status->name;
			
			$branch_address = get_option('invoice_company_address');
			$communication_data = array(
				"assigned_doctor"   => !empty($assigned_doctor)   ? $assigned_doctor   : 1,
				"appointment_date"  => !empty($appointment_date)  ? $appointment_date  : date('d-m-Y'),
				"appointment_time"  => !empty($appointment_time)  ? $appointment_time  : date('H:i A'),
				"date_time"         => !empty($date_time)         ? $date_time         : date('d-m-Y H:i A'),
				"branch_address"    => !empty($branch_address)    ? $branch_address    : 'Main Branch',
			);
			$this->leads_model->lead_journey_log_event($lead_id, $status_name, $status_id, $communication_data);
		
		}

		if ($updated) {
			set_alert('success', 'Lead status updated successfully.');
		} else {
			set_alert('danger', 'Failed to update lead status.');
		}

		// Redirect back to leads list or to the same lead
		redirect($_SERVER['HTTP_REFERER'] ?? admin_url('leads'));
	}
	
	public function send_payment_request($appointment_id, $lead_id){
		$get = $this->db->get_where(db_prefix() . 'appointment', array("appointment_id"=>$appointment_id))->row();
		if($get){
			$client_id = $get->userid;
			$assigned_doctor = get_staff_full_name($get->enquiry_doctor_id);
			$appointment_date = date('d-m-Y', strtotime($get->appointment_date));
			$appointment_time = date('h:i A', strtotime($get->appointment_date)); // 12-hour format with AM/PM
			$date_time = date('d-m-Y h:i A', strtotime($get->appointment_date));   // Full date-time with AM/PM

			$branch_address = get_option('invoice_company_address');
			$communication_data = array(
			"assigned_doctor" => $assigned_doctor,
			"appointment_date" => $appointment_date,
			"appointment_time" => $appointment_time,
			"date_time" => $date_time,
			"branch_address" => $branch_address,
			);
			$this->load->model('client/client_model');
			$this->client_model->patient_journey_log_event($client_id, 'appointment_created', 'Appointment Created', $communication_data);
			$this->leads_model->log_lead_activity($lead_id, 'Payment request sent.');
			set_alert('success', 'Request sent successfully.');
			redirect($_SERVER['HTTP_REFERER'] ?? admin_url('leads'));
		}else{
			set_alert('danger', 'Failed to send request.');
			$this->leads_model->log_lead_activity($lead_id, 'Payment request sent failed.');
			redirect($_SERVER['HTTP_REFERER'] ?? admin_url('leads'));
		}
		
	}


}
