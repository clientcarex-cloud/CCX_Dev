<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Registration extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('registration_model');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
		$this->load->helper('invoice');
		$this->lang->load('registration_lang', 'english', FALSE, TRUE, FCPATH . 'modules/registration/');

    }

    public function index()
    {
		
        $client = $this->session->userdata('client');
        $status = $this->session->userdata('status');
	
		if (isset($_SESSION['client'])) {
			$userid = $this->session->userdata('client')['userid'];
			$data['invoice'] = $this->db->get_where(db_prefix() . 'invoices', ['clientid' => $userid])->row();
		}
        $data['client'] = $client;
        $data['status'] = $status;
		
		$data['title'] = "Registration Form";
		
		$data['doctors'] = $this->registration_model->get_doctors();
		$data['treatments'] = $this->registration_model->get_treatments();
		
		extract($data);
		require_once(APPPATH . '../modules/registration/views/registration_form.php');
		//$this->load->view(VIEWPATH . 'modules/registration/views/registration_form.php', $data);
    }

    public function check_mobile()
    {
        $mobile = $this->input->post('mobile');
        $client = $this->registration_model->is_registered($mobile);

        if ($client) {
            //$otp = rand(1000, 9999); // Simulated OTP
            $otp = 1234; // Simulated OTP
            $this->registration_model->save_otp($mobile, $otp);

            // You may send the OTP via SMS here in real app
            $this->session->set_userdata([
                'otp_mobile' => $mobile,
                'status' => 'otp_pending'
            ]);

            echo json_encode([
                'status' => 'otp',
                'name' => $client['company'],
                'mobile' => $mobile
            ]);
        } else {
            $this->session->set_userdata([
                'register_mobile' => $mobile,
                'status' => 'register'
            ]);

            echo json_encode([
                'status' => 'register',
                'mobile' => $mobile
            ]);
        }
    }

    public function verify_otp()
    {
        $mobile = $this->input->post('mobile');
        $otp = $this->input->post('otp');

        if ($this->registration_model->verify_otp($mobile, $otp)) {
            $client = $this->registration_model->is_registered($mobile);

            $this->session->set_userdata([
                'client' => $client,
                'status' => 'verified'
            ]);

            echo json_encode([
                'status' => 'success',
                'name' => $client['company']
            ]);
        } else {
            echo json_encode(['status' => 'fail']);
        }
    }

    public function logout()
    {
        $this->session->unset_userdata(['client', 'status', 'otp_mobile', 'register_mobile']);
        
		//$this->session->sess_destroy();
		echo json_encode(['status' => 'logged_out']);
    }
	
	public function save_new_client()
	{
		$mobile = $this->input->post('mobile');
		$name = $this->input->post('name');
		

		$inserted = $this->registration_model->insert_new_client();

		if ($inserted) {
			// Set session as verified immediately after registration
			$this->session->set_userdata('client', ['company' => $name, 'mobile' => $mobile, 'userid' => $inserted]);
			$this->session->set_userdata('status', 'verified');

			echo json_encode(['status' => 'success', 'name' => $name]);
		} else {
			echo json_encode(['status' => 'fail']);
		}
	}
	
	public function save_appointment()
	{

		$userid = $this->session->userdata('client')['userid'];
		$treatment_id = $this->input->post('treatment_id');
		$doctor_id = $this->input->post('doctor_id');

		// Check if there's already an invoice
		$invoice = $this->db->get_where(db_prefix() . 'invoices', ['clientid' => $userid])->row();

		if (!$invoice) {
			$this->db->order_by("number", "DESC");
			$get_number = $this->db->get_where(db_prefix() . 'invoices')->row();
			if($get_number){
				$number = $get_number->number + 1;
			}else{
				$number = 1;
			}
			$number_format = 1;
			
			$formatted_number = 'INV-' . str_pad($number, 6, '0', STR_PAD_LEFT);
			$datecreated = date("Y-m-d H:i:s");
			$subtotal = 300;
			$total = 300;
			$status = 1;
			$prefix = "INV-";
			$hash = md5(rand() . microtime() . time() . uniqid());
			
			// Prepare invoice data
			$invoice_data = array(
				'clientid' => $userid,
				'date' => date('Y-m-d'),
				'duedate' => date('Y-m-d', strtotime('+7 days')),
				'currency' => 1, // Default currency
				'number' => $number,
				'number_format' => $number_format,
				'formatted_number' => $formatted_number,
				'formatted_number' => $formatted_number,
				'datecreated' => $datecreated,
				'subtotal' => $subtotal,
				'total' => $total,
				'status' => $status,
				'prefix' => $prefix,
				'hash' => $hash,
				'addedfrom' => 0,
				'sent' => 0,
				'recurring' => 0,
				'custom_recurring' => 0,
			);

			// Call the proper add method
			$this->db->insert(db_prefix() . 'invoices', $invoice_data);
			$invoice_id = $this->db->insert_id();
			if($invoice_id){
				$itemable = array(
				"rel_id" => $invoice_id,
				"rel_type" => "invoice",
				"description" => "Consultation Fee",
				"qty" => 1,
				"rate" => 300,
				"item_order" => 1,
				);
				$this->db->insert(db_prefix() . 'itemable', $itemable);
			}else{
				echo json_encode(['status' => 'error', 'message' => 'Invoice creation failed']);
				return;
			}
		}


		// Save appointment
		$appointment_data = [
			'userid' => $userid,
			'appointment_date' => date('Y-m-d H:i:s', strtotime($this->input->post('appointment_date'))),
			'treatment_id' => $treatment_id,
			'unit_doctor_id' => $doctor_id,
			'visit_id' => uniqid('VIS'),
			'visit_status' => 'Scheduled',
			'consultation_fee_id' => 1,
		];
		$this->db->insert('tblappointment', $appointment_data);

		echo json_encode(['status' => 'success']);
	}
	

}
