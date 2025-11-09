<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Registration_model extends CI_Model 
{
    public function __construct()
    {
        parent::__construct();
    }

	public function is_registered($mobile) {
        return $this->db->get_where(db_prefix() . 'clients', ['phonenumber' => $mobile])->row_array();
    }

    public function save_otp($mobile, $otp) {
        $data = ['phonenumber' => $mobile, 'otp_code' => $otp, 'created_at' => date('Y-m-d H:i:s')];
        $this->db->insert(db_prefix() . 'registration_otp', $data);
    }

    public function verify_otp($mobile, $otp) {
        $this->db->where('phonenumber', $mobile);
        $this->db->where('otp_code', $otp);
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')));
        return $this->db->get(db_prefix() . 'registration_otp')->row_array();
    }
	public function insert_new_client()
	{
		$data = [
			'phonenumber'  => $this->input->post('mobile'),
			'company'    => $this->input->post('name'),
			'address' => $this->input->post('address'),
		];

		// Handle file upload if any
		if (!empty($_FILES['reports']['name'])) {
			$config['upload_path']   = './uploads/reports/';
			$config['allowed_types'] = 'jpg|jpeg|png|pdf';
			$config['max_size']      = 2048;

			$this->load->library('upload', $config);
			if ($this->upload->do_upload('reports')) {
				$fileData = $this->upload->data();
				$data['report_file'] = $fileData['file_name'];
			}
		}
		$this->db->insert(db_prefix() . 'clients', $data); // Make sure 'clients' table exists
		$userid = $this->db->insert_id();
		
		$data = [
			'userid'  => $userid,
			'dob'     => $this->input->post('dob'),
			'email_id'   => $this->input->post('email'),
		];
		$this->db->insert(db_prefix() . 'clients_new_fields', $data);
		return $userid;
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
        $this->db->where(array("d.role"=>2, "active"=>1));
       
            return $this->db->get()->result_array();
       
	}
	
	public function get_treatments(){
		return $this->db->get_where(db_prefix() . 'treatment')->result_array();
	}



   
}
