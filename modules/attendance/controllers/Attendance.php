<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Attendance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance/attendance_model');
        header('Content-Type: application/json');
    }
	
    public function punch()
	{
		//Checking module enabled
		$check_module = $this->db->get_where(db_prefix() . 'modules', array("module_name"=>"attendance", "active"=>1))->row();
		if($check_module){
			$is_module_enabled = 1;
		}else{
			$is_module_enabled = 0;
		}
		if ($is_module_enabled == 0) {
			echo json_encode(['status' => false, 'message' => 'Attendance Module is not enabled']);
			return;
		}
		
		// Read Authorization header
		$headers = apache_request_headers();
		$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
		
		$get_token = $this->db->get_where(db_prefix() . 'attendance_auth_token')->row();
		
		if ($get_token) {
			$auth_token = $get_token->token;
		}else{
			echo json_encode(['status' => false, 'message' => 'Auth Token is not configured']);
			return;
		}
		
		// Replace with your actual token or token validation method
		$valid_token = 'Bearer '.$auth_token;

		if ($authHeader !== $valid_token) {
			echo json_encode(['status' => false, 'message' => 'Unauthorized']);
			return;
		}


		// Process punch logic
		$data = json_decode(file_get_contents('php://input'), true);

		if (!isset($data['punch_id'])) {
			echo json_encode(['status' => false, 'message' => 'Missing staff ID (punch_id).']);
			return;
		}

		$punch_id = $data['punch_id'];
		
		$get_staff_id = $this->db->get_where(db_prefix() . 'attendance_staff', array("punch_id"=>$punch_id))->row();
		if($get_staff_id){
			$staff_id = $get_staff_id->staff_id;
		}else{
			echo json_encode(['status' => false, 'message' => 'The staff and punch ID are not properly mapped.']);
			return;
		}
		$today = date('Y-m-d');

		$existing = $this->attendance_model->get_today_attendance($staff_id, $today);

		if ($existing) {
			$this->attendance_model->update_punch_out($existing['id']);
			echo json_encode(['status' => true, 'message' => 'Punch-out time updated.']);
		} else {
			$this->attendance_model->insert_punch_in($staff_id);
			echo json_encode(['status' => true, 'message' => 'Punch-in time recorded.']);
		}
	}
	
	
}
