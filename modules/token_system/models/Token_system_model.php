<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Token_system_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
		$this->load->helper('token_display_upload');
    }
	public function get_all_displays()
	{
		return $this->db->get(db_prefix() . 'display_config')->result_array();
	}

    public function add_display($data, $files)
	{
		// Insert core display config
		$insert = [
			'display_name'        => $data['display_name'],
			'queue_type'          => $data['queue_type'],
			'number_of_list'      => $data['number_of_list'],
			'doctor_info'         => isset($data['doctor_info']) ? 1 : 0,
			'media_type'          => $data['media_type'],
			'display_features_logo'=> $data['display_logo'],
			'youtube_link'        => $data['youtube_link'] ?? null,
			'display_features_logo' => null, // Will be set after upload
			'display_patient_info' => isset($data['display_patient_info']) ? implode(',', $data['display_patient_info']) : null,
			'created_at'          => date('Y-m-d H:i:s'),
		];

		$this->db->insert(db_prefix() . 'display_config', $insert);
		$display_id = $this->db->insert_id();

		// Upload slider images if media_type is "Images"
		if ($data['media_type'] == 'Images' && !empty($_FILES['slider_images']['name'][0])) {
			token_display_upload_slider_images($display_id);
		}

		return $display_id;
	}
	public function update_display($id, $data, $files)
	{
		// Prepare data for updating
		$update = [
			'display_name'        => $data['display_name'],
			'queue_type'          => $data['queue_type'],
			'number_of_list'      => $data['number_of_list'],
			'doctor_info'         => isset($data['doctor_info']) ? 1 : 0,
			'media_type'          => $data['media_type'],
			'display_features_logo'=> $data['display_logo'],
			'youtube_link'        => $data['youtube_link'] ?? null,
			'display_patient_info' => isset($data['display_patient_info']) ? implode(',', $data['display_patient_info']) : null
		];

		// Update the display configuration
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'display_config', $update);

		// Handle slider images upload if media_type is "Images"
		if ($data['media_type'] == 'Images' && !empty($files['slider_images']['name'][0])) {
			token_display_upload_slider_images($id);
		}
	}



    // Insert new counter record
    public function insert_counter($data) {
        return $this->db->insert(db_prefix() . 'counter', $data);
    }

    // Get counter data by ID
    public function get_counter_by_id($counter_id) {
        // Query to fetch the counter by its ID
        /* $this->db->where('counter_id', $counter_id);
        $query = $this->db->get(db_prefix() . 'counter'); // Assuming the table name is 'counters'
		 */
		$this->db->select('c.*, CONCAT(d.firstname, " ", d.lastname) AS doctor_name,new.*, s.specialization_name, d.*, display.*');
        $this->db->from(db_prefix() . 'counter as c');
        $this->db->join(db_prefix() . 'staff as d', "d.staffid = c.doctor_id", "LEFT");
        $this->db->join(db_prefix() . 'display_config as display', "display.id = c.display_id", "LEFT");
        $this->db->join(db_prefix() . 'doctor_new_fields as new', "d.staffid = new.doctor_id", "LEFT");
        $this->db->join(db_prefix() . 'specialization as s', "s.specialization_id = new.specialization", "LEFT");
		$this->db->where('counter_id', $counter_id);
        $query = $this->db->get();

        return $query->row(); // Return the row (object) of the counter
    }

	public function get_all_counters() {
        // Retrieve all counters from the db_prefix() . 'counter' table
        $this->db->select('c.*, CONCAT(d.firstname, " ", d.lastname) AS doctor_name,d.*, dis.display_name');
        $this->db->from(db_prefix() . 'counter as c');
        $this->db->join(db_prefix() . 'staff as d', "d.staffid = c.doctor_id", 'left');
        $this->db->join(db_prefix() . 'display_config as dis', "dis.id = c.display_id", 'left');
        $query = $this->db->get();
        return $query->result_array();
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
        $this->db->where(array("role.name"=>'Doctor', "active"=>1));
        if($id){
            $this->db->where(array("d.staffid"=>$id));
            return $this->db->get()->row();
        }else{
            return $this->db->get()->result_array();
        }
		
		
	}
	public function get_patients($id = '')
	{
		$this->db->select('p.*'); // Add more fields if needed
		$this->db->from(db_prefix() . 'clients p');
		//$this->db->join(db_prefix() . 'roles role', 'role.roleid = d.role', 'left');
	
		// Optional additional where filter
		if (!empty($where)) {
			$this->db->where($where);
		}
        //$this->db->where(array("d.role"=>2, "active"=>1));
        if($id){
            $this->db->where(array("p.userid"=>$id));
            return $this->db->get()->row();
        }else{
            return $this->db->get()->result_array();
        }
		
		
	}
	public function get_appointments($id = '')
	{
		$this->db->select('p.*, a.*'); // Add more fields if needed
		$this->db->from(db_prefix() . 'appointment a');
		$this->db->join(db_prefix() . 'clients p', 'p.userid = a.userid', 'left');
		
		$date = date('Y-m-d');
		$this->db->like('a.appointment_date', $date, 'after');
          
        return $this->db->get()->result_array();
        
		
		
	}
	
	// Get all tokens
    public function get_all_tokens() {
		$this->db->select('t.*, p.company as patient_name,  CONCAT(d.firstname, " ", d.lastname) AS doctor_name'); // Add more fields if needed
		$this->db->from(db_prefix() . 'tokens t');
		$this->db->join(db_prefix() . 'clients p', 'p.userid = t.patient_id', 'left');
		$this->db->join(db_prefix() . 'staff d', 'd.staffid = t.doctor_id', 'left');
		$this->db->order_by("t.date", "DESC");
        return $this->db->get()->result_array();
    } 
	
	public function queued_patients($id = "", $status = "") {
		$this->db->select('t.*, p.*,p.company as patient_name, d.*, CONCAT(d.firstname, " ", d.lastname) AS doctor_name, cn.*'); // Add more fields if needed
		$this->db->from(db_prefix() . 'tokens t');
		$this->db->join(db_prefix() . 'clients p', 'p.userid = t.patient_id', 'left');
		$this->db->join(db_prefix() . 'clients_new_fields cn', 'cn.userid = p.userid', 'left');
		$this->db->join(db_prefix() . 'staff d', 'd.staffid = t.doctor_id', 'left');
		$this->db->join(db_prefix() . 'counter c', "c.doctor_id = t.doctor_id", 'left');
		$this->db->where(array("c.counter_id"=>"$id"));
		$this->db->order_by("t.token_id", "ASC");
		if ($status) {
			if ($status == 'Pending') {
				$this->db->where_in('t.token_status', ['Pending', 'Recall']);
			} else {
				$this->db->where('t.token_status', $status);
			}
		}

		$this->db->where(array("date"=>date("Y-m-d")));
        return $this->db->get()->result_array();
    }

    // Create a new token
    public function create_token($data) {
        return $this->db->insert(db_prefix() . 'tokens', $data);
    }

    // Update token status
    public function update_token_status($token_id, $status) {
        $this->db->where('token_id', $token_id);
        $this->db->update(db_prefix() . 'tokens', array('token_status' => $status));
    }

    // Get token by ID
    public function get_token_by_id($token_id) {
        $this->db->where('token_id', $token_id);
        return $this->db->get(db_prefix() . 'tokens')->row();
    }
	
	public function complete_current_serving()
    {
        $this->db->where('token_status', 'Serving');
        $this->db->update(db_prefix() . 'tokens', [
            'token_status' => 'Completed'
        ]);
    }

    public function start_next_patient($token_id)
    {
        $this->db->where('token_id', $token_id);
        $this->db->update(db_prefix() . 'tokens', [
            'token_status' => 'Serving'
        ]);
    }
	
	 // Update counter details
    public function update_counter($counter_id, $counter_data)
    {
        if (!$counter_id || empty($counter_data)) {
            return false; // Ensure data exists and counter_id is valid
        }

        // Update counter data in the database
        $this->db->where('counter_id', $counter_id);
        return $this->db->update(db_prefix() .'counter', $counter_data);  // Assuming 'counters' is the table
    }
	
	public function get_display_images($display_id){
		 // Update counter data in the database
        $this->db->where('display_id', $display_id);
        return $this->db->get(db_prefix() .'display_images')->result_array();
	}
   
	public function get_display_config($display_id){
		 // Update counter data in the database
        $this->db->where('id', $display_id);
        return $this->db->get(db_prefix() .'display_config')->row();
	}
   
}
