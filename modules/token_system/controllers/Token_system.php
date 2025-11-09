<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Token_system extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('token_system_model');
    }

    private function check_customer_permissions()
    {
        if (staff_cant('view', 'token_system')) {
            if (!have_assigned_customers() && staff_cant('create', 'token_system')) {
                access_denied('token_system');
            }
        }
    }

   public function display($id = '')
    {
		if (staff_cant('view_display', 'token_system')) {
			access_denied('token_system');
		}
        $data['title'] = 'Display Settings';
        $data['displays'] = $this->token_system_model->get_all_displays();
        $this->load->view('display', $data);
    }
	
	
	public function edit_display($id)
	{
		if (staff_cant('edit_display', 'token_system')) {
			access_denied('token_system');
		}
		// Fetch the existing display configuration
		$display = $this->db->get_where(db_prefix() . 'display_config', ['id' => $id])->row();

		if (!$display) {
			// Handle error if the display configuration doesn't exist
			set_alert('danger', 'Display configuration not found.');
			redirect(admin_url('token_system/display'));
		}

		// Check if form is submitted
		if ($this->input->post()) {
			// Prepare data for updating
			$data = $this->input->post();
			$files = $_FILES;

			// Call the model function to update the data
			$this->token_system_model->update_display($id, $data, $files);
			set_alert('success', 'Display configuration updated successfully');
			redirect(admin_url('token_system/display'));
		}

		// Pass the current display data to the view
		$data['display'] = $display;
		$data['title'] = 'Edit Display Configuration';
		$this->load->view('edit_display', $data);
	}

    public function add($id = '')
    {
		if (staff_cant('create_display', 'token_system')) {
			access_denied('token_system');
		}
        if ($this->input->post()) {
            $id = $this->token_system_model->add_display($this->input->post(), $_FILES);
            if ($id) {
                set_alert('success', 'Display added successfully');
                redirect(admin_url('token_system/display'));
            }
        }
        $data['title'] = 'Add Display';
        $this->load->view('display_form', $data);
    }
	
	public function counters($id = '')
    {
		if (staff_cant('view_counter', 'token_system')) {
			access_denied('token_system');
		}
        $data['title'] = 'Counters';
        $data['counters'] = $this->token_system_model->get_all_counters();
        $this->load->view('counters', $data);
    }
	
	public function call($id = '')
    {
		if (staff_cant('view_call', 'token_system')) {
			access_denied('token_system');
		}
        $data['title'] = 'Call';
		 $data['selected_counter_id'] = $id; // Pass it to view
		$data['counters'] = $this->token_system_model->get_all_counters();
        $data['queued_patients'] = $this->token_system_model->queued_patients($id, "Pending");
        $data['completed_patients'] = $this->token_system_model->queued_patients($id, "Completed");
        $data['current_patient'] = $this->token_system_model->queued_patients($id, "Serving");
        $this->load->view('call', $data);
    }
	
	public function next_call($next_token_id, $token_status, $counter_id = '')
	{
		// Load Library
		$this->load->library('token_system_lib');

		// Complete current serving patient
		$this->token_system_lib->complete_current_serving();

		// Update specific token_id to "Serving"
		$this->token_system_lib->update_token_status($next_token_id, $token_status);
		
		if($counter_id){
			redirect(admin_url('token_system/call/'.$counter_id));
		}else{
			// Redirect back to the call screen
			redirect(admin_url('token_system/call'));
		}
		
	}
	public function next_call_public($next_token_id, $token_status, $counter_id = '')
	{
		// Load Library
		$this->load->library('token_system_lib');

		// Complete current serving patient
		$this->token_system_lib->complete_current_serving();

		// Update specific token_id to "Serving"
		$this->token_system_lib->update_token_status($next_token_id, $token_status);
		
		if($counter_id){
			redirect(admin_url('token_system/create_public_view/'.$counter_id));
		}else{
			// Redirect back to the call screen
			redirect(admin_url('token_system/call'));
		}
		
	}
	public function add_counter() 
	{
		if (staff_cant('create_counter', 'token_system')) {
			access_denied('token_system');
		}
       
        // Prepare the view data
        $data['title'] = 'Add Counter';
		$data['doctors'] = $this->token_system_model->get_doctors();
		$data['displays'] = $this->token_system_model->get_all_displays();
        
        // Load the counter form view
        $this->load->view('counter_form', $data);
    }
	public function edit_counter($id = '') 
	{
       if (staff_cant('edit_counter', 'token_system')) {
			access_denied('token_system');
		}
        // Prepare the view data
        $data['title'] = 'Edit Counter';
		$data['doctors'] = $this->token_system_model->get_doctors();
		$data['displays'] = $this->token_system_model->get_all_displays();
		$data['counter'] = $this->token_system_model->get_counter_by_id($id);
        
        // Load the counter form view
        $this->load->view('counter_edit', $data);
    }
	public function create_counter() {
		if (staff_cant('create_counter', 'token_system')) {
			access_denied('token_system');
		}
        $counter_name = $this->input->post('counter_name');
        $doctor_id = $this->input->post('doctor_id');
        $display_id = $this->input->post('display_id');
        $counter_status = $this->input->post('counter_status');

        $auth_code = $this->generate_code();
        $counter_url = base_url("token_system/create_public_view/{$auth_code}");

        $data = array(
            'counter_name' => $counter_name,
            'doctor_id' => $doctor_id,
            'display_id' => $display_id,
            'counter_status' => $counter_status,
            'counter_url' => $counter_url,
            'auth_code' => $auth_code
        );

        // Save data to database
        $this->token_system_model->insert_counter($data);

        redirect(admin_url('token_system/counters'));
    }

    // Function to view the counter and ask for password
    public function create_public_view($counter_id  = null) {
        if (!$counter_id ) {
            show_404();
        }
        $counter = $this->token_system_model->get_counter_by_id($counter_id );
		$queued_patients    = $this->token_system_model->queued_patients($counter_id, "Pending");
		//$completed_patients = $this->token_system_model->queued_patients($counter_id, "Completed");
		$current_patient    = $this->token_system_model->queued_patients($counter_id, "Serving");
		
		function get_display_images($display_id){
			$ci = &get_instance();
			return $ci->token_system_model->get_display_images($display_id);
		}

        if (!$counter) {
            show_404();
        }

        $this->load->view('create_public_view', [
						'counter' => $counter,
						'queued_patients'   => $queued_patients,
						'current_patient'   => $current_patient,
		]);
    }
	
	// Function to view the counter and ask for password
    public function create_doctor_view($counter_id  = null) {
        if (!$counter_id ) {
            show_404();
        }
        $counter = $this->token_system_model->get_counter_by_id($counter_id );
		$queued_patients    = $this->token_system_model->queued_patients($counter_id, "Pending");
		//$completed_patients = $this->token_system_model->queued_patients($counter_id, "Completed");
		$current_patient    = $this->token_system_model->queued_patients($counter_id, "Serving");
		
		function get_display_images($display_id){
			$ci = &get_instance();
			return $ci->token_system_model->get_display_images($display_id);
		}

        if (!$counter) {
            show_404();
        }

        $this->load->view('create_doctor_view', [
						'counter' => $counter,
						'queued_patients'   => $queued_patients,
						'current_patient'   => $current_patient,
		]);
    }

    // Function to verify the password for the counter URL
    public function verify_password() {
		// Get the data from the post request
		$counter_id = $this->input->post('counter_id');
		$password = $this->input->post('password');

		// Verify the counter and password
		$counter = $this->token_system_model->get_counter_by_id($counter_id);

		if ($counter) {
			// Ensure the password field exists and is not empty
			if (isset($counter->auth_code) && !empty($counter->auth_code)) {
				// Check if the entered password matches the stored password
				if ($password == $counter->auth_code) {
					// Pass $counter_id to get patients
					$queued_patients    = $this->token_system_model->queued_patients($counter_id, "Pending");
					$completed_patients = $this->token_system_model->queued_patients($counter_id, "Completed");
					$current_patient    = $this->token_system_model->queued_patients($counter_id, "Serving");
					//print_r($queued_patients);
					// Send all data to view
					$this->load->view('create_public_view', [
						'counter'           => $counter,
						'queued_patients'   => $queued_patients,
						'completed_patients'=> $completed_patients,
						'current_patient'   => $current_patient,
						'success' => true
					]);
				} else {
					// Show error if the password is incorrect
					echo "Invalid password.";
				}
			} else {
				// Handle the case where the password field is missing or invalid
				echo "Password field is missing or invalid.";
			}
		} else {
			// Handle the case where the counter is not found
			echo "Counter not found.";
		}
	}


    // Generate a 4-digit random code
    private function generate_code() {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
	
	
	// Display the list of tokens
    public function tokens($id = '') {
		if (staff_cant('view_token', 'token_system')) {
			access_denied('token_system');
		}
		$data['title'] = "Tokens";
        $data['tokens'] = $this->token_system_model->get_all_tokens();
        $this->load->view('tokens', $data);
    }
	// Display the list of tokens
    public function add_token() {
		if (staff_cant('create_token', 'token_system')) {
			access_denied('token_system');
		}
        $data['title'] = "Add Token";
		$data['patients'] = $this->token_system_model->get_appointments();
		$data['doctors'] = $this->token_system_model->get_doctors();
        $this->load->view('add_token', $data);
    }

    // Create a new token
    public function create_token() {
		if (staff_cant('create_token', 'token_system')) {
			access_denied('token_system');
		}
        if ($this->input->post()) {
			$this->db->order_by("token_id", "DESC");
			$this->db->limit(1);
			$date = date("Y-m-d");
			$get_token_number = $this->db->get_where(db_prefix() . 'tokens', array("date"=>$date))->row();
			if($get_token_number){
				$token_number = $get_token_number->token_number + 1;
			}else{
				$token_number = 1;
			}
            $token_data = array(
                'token_number'   => $token_number,
                'patient_id'   => $this->input->post('patient_id'),
                'doctor_id'    => $this->input->post('doctor_id'),
                //'date'         => date('Y-m-d', strtotime($this->input->post('date'))),
                'date'         => date('Y-m-d'),
                'token_status'       => 'Pending'
            );
            $this->token_system_model->create_token($token_data);
            redirect(admin_url('token_system/tokens'));
        }

        $this->load->view('token_system/create_token');
    }


    // View token details
    public function view_token($token_id) {
		if (staff_cant('view_token', 'token_system')) {
			access_denied('token_system');
		}
        $data['token'] = $this->token_system_model->get_token_by_id($token_id);
        $this->load->view('view_token', $data);
    }

	// Function to edit counter
    public function update_counter($counter_id) {
		if (staff_cant('edit_counter', 'token_system')) {
			access_denied('token_system');
		}
		// Retrieve form data
		$counter_name = $this->input->post('counter_name');
		$doctor_id = $this->input->post('doctor_id');
		$display_id = $this->input->post('display_id');
		$counter_status = $this->input->post('counter_status');

		// Prepare the data to update
		$data = [
			'counter_name' => $counter_name,
			'doctor_id' => $doctor_id,
			'display_id' => $display_id,
			'counter_status' => $counter_status
		];

		// Update the counter in the database
		$this->token_system_model->update_counter($counter_id, $data);

		// Redirect to counters page
		redirect(admin_url('token_system/counters'));
	}
	public function displays_table()
	{
		if (!is_admin() && !staff_can('view_call_log', 'customers')) {
			access_denied('display');
		}

		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('token_system', 'tables/display_table'));
		}
	}
	

	public function get_counters_ajax()
	{
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('token_system', 'tables/counter_table'));
		}
	}

	public function display_tokens_view($counter_id = NULL)
	{
		// In future: you can fetch tokens dynamically like this
		// $data['tokens'] = $this->token_system_model->get_all_tokens();

		$data['title'] = 'Token Display'; // This will appear in the page title
		$data['counter'] = $this->token_system_model->get_counter_by_id($counter_id);
		$data['queued_patients']    = $this->token_system_model->queued_patients($counter_id, "Pending");
		$data['current_patient']    = $this->token_system_model->queued_patients($counter_id, "Serving");
		
		function get_display_config($display_id){
			$ci = &get_instance();
			return $ci->token_system_model->get_display_config($display_id);
		}
		$this->load->view('display_tokens_view', $data); // adjust path if needed
	}
	public function display_tokens_public_view($counter_id = NULL)
	{
		// In future: you can fetch tokens dynamically like this
		// $data['tokens'] = $this->token_system_model->get_all_tokens();

		$data['title'] = 'Token Display'; // This will appear in the page title
		$data['counter'] = $this->token_system_model->get_counter_by_id($counter_id);
		$data['queued_patients']    = $this->token_system_model->queued_patients($counter_id, "Pending");
		$data['current_patient']    = $this->token_system_model->queued_patients($counter_id, "Serving");
		
		function get_display_config($display_id){
			$ci = &get_instance();
			return $ci->token_system_model->get_display_config($display_id);
		}
		$this->load->view('display_tokens_public_view', $data); // adjust path if needed
	}

    
}
