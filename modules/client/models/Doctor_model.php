<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Doctor_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get Master
    */
    
	 
	public function get_doctors($id = '')
	{
		$this->db->select('d.*, role.*'); // Add more fields if needed
		$this->db->from(db_prefix() . 'staff d');
		$this->db->join(db_prefix() . 'roles role', 'role.roleid = d.role', 'left');
	
		// Optional additional where filter
		if (!empty($where)) {
			$this->db->where($where);
		}
        $this->db->group_start();
		$this->db->where('role.name', 'Doctor');
		$this->db->or_where('role.name', 'Service Doctor');
		$this->db->group_end();
		$this->db->where('active', 1);

        if($id){
            $this->db->where(array("d.staffid"=>$id));
            return $this->db->get()->row();
        }else{
            return $this->db->get()->result_array();
        }
		
		
	}
	public function save_doctor($data)
    {
        $this->db->trans_start();

        // 1. Save in tblstaff
        $staff = [
            'firstname' => $data['firstname'] ?? '',
            'email' => $data['email'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? null,
            'datecreated' => date('Y-m-d H:i:s'),
            'active' => 1,
            'admin' => 0,
            'role' => 2,
        ];

        // Optional password handling
        if (!empty($data['password'])) {
            $staff['password'] = app_hash_password($data['password']);
        }else{
			$staff['password'] = app_hash_password(123456);
		}

        $this->db->insert(db_prefix() . 'staff', $staff);
        $doctor_id = $this->db->insert_id();

        //Handle signature upload
        $signature = handle_doctor_signature_upload($doctor_id);

        if (!$doctor_id) {
            return ['success' => false, 'message' => 'Failed to save staff record.'];
        }

        // 2. Save in tbldoctor_new_fields
        $profile = [
            'doctor_id' => $doctor_id,
            'salutation' => $data['salutation'] ?? null,
            'gender' => $data['gender'] ?? null,
            'qualification' => $data['qualification'] ?? null,
            'signature' => $signature,
            'location' => $data['location'] ?? null,
            'licence_number' => $data['licence_number'] ?? null,
            'department' => $data['department'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'branch' => $data['branch'] ?? null,
            'shift_id' => $data['shift'] ?? null,
            'date_of_birth' => $data['dob'] ?? null,
            'experience_years' => $data['experience'] ?? null,
            'consultation_fee' => $data['consultation_fee'] ?? null,
        ];
        $this->db->insert(db_prefix() . 'doctor_new_fields', $profile);

        // 3. Save time slots
        if (!empty($data['slots']) && is_array($data['slots'])) {
            foreach ($data['slots'] as $slot) {
                $slot_data = [
                    'doctor_id' => $doctor_id,
                    'day_of_week' => $slot['day'] ?? null,
                    'shift_start_time' => $slot['shift_start_time'] ?? null,
                    'shift_end_time' => $slot['shift_end_time'] ?? null,
                    'avg_session_time' => $slot['avg_session_time'] ?? null,
                ];
                $this->db->insert(db_prefix() . 'doctor_time_slots', $slot_data);
            }
        }
        


        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            //return ['success' => false, 'message' => 'Transaction failed.'];
            return false;
        }

        return $doctor_id;
    }

    public function get_departments($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'departments');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
    

    public function get_doctor_new_fields($id)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'doctor_new_fields');
        
        $this->db->where(array("doctor_id"=>$id));
        
        return $this->db->get()->row();
    }

    public function get_doctor_time_slots($id)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'doctor_time_slots');
        
        $this->db->where(array("doctor_id"=>$id));
        
        return $this->db->get()->result_array();
    }
    

    public function get_specialization($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'specialization');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
    

    public function get_shift($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'shift');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
    

    public function get_role($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'roles');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
    

    public function get_branch($where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'customers_groups');
    
        if (!empty($where)) {
            $this->db->where($where);
        }
    
        return $this->db->get()->result_array();
    }
    public function update_doctor($doctor_id, $data)
    {
        $this->db->trans_start();

        // Update staff data in tblstaff
        $staff = [
            'firstname'   => $data['firstname'] ?? '',
            'email'       => $data['email'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? null,
        ];
        
        // Optional password handling for update
        if (!empty($data['password'])) {
            $staff['password'] = app_hash_password($data['password']);
        }

        $this->db->where('staffid', $doctor_id);
        $this->db->update(db_prefix() . 'staff', $staff);
        
        // Update doctor profile in tbldoctor_new_fields
        $profile = [
            'gender'          => $data['gender'] ?? null,
            'qualification'   => $data['qualification'] ?? null,
            'location'        => $data['location'] ?? null,
            'licence_number'  => $data['licence_number'] ?? null,
            'department'      => $data['department'] ?? null,
            'specialization'  => $data['specialization'] ?? null,
            'role'            => $data['role_title'] ?? null,
            'branch'          => $data['branch'] ?? null,
            'date_of_birth'   => $data['dob'] ?? null,
            'shift_id' => $data['shift'] ?? null,
            'experience_years'=> $data['experience'] ?? null,
            'consultation_fee'=> $data['consultation_fee'] ?? null,
        ];
        
        $this->db->where('doctor_id', $doctor_id);
        $this->db->update(db_prefix() . 'doctor_new_fields', $profile);

        // Update signature upload (if needed)
        if (isset($data['signature']) && !empty($data['signature'])) {
            $signature = handle_doctor_signature_upload($doctor_id);
            $this->db->where('doctor_id', $doctor_id);
            $this->db->update(db_prefix() . 'doctor_new_fields', ['signature' => $signature]);
        }

        // Update time slots
        if (!empty($data['slots']) && is_array($data['slots'])) {
            // Delete existing slots before updating
            $this->db->where('doctor_id', $doctor_id);
            $this->db->delete(db_prefix() . 'doctor_time_slots');

            // Insert updated time slots
            foreach ($data['slots'] as $slot) {
                $slot_data = [
                    'doctor_id'        => $doctor_id,
                    'day_of_week'      => $slot['day'] ?? null,
                    'shift_start_time' => $slot['shift_start_time'] ?? null,
                    'shift_end_time'   => $slot['shift_end_time'] ?? null,
                    'avg_session_time' => $slot['avg_session_time'] ?? null,
                ];
                $this->db->insert(db_prefix() . 'doctor_time_slots', $slot_data);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Transaction failed during doctor update.'];
        }

        return ['success' => true, 'message' => 'Doctor updated successfully.'];
    }

	// File: application/models/Doctor_model.php or modules/client/models/Doctor_model.php
	public function get_appointments()
	{
		$this->db->select('
			a.appointment_id,
			a.userid,
			a.appointment_date,
			a.remarks,
			a.visit_status,
			c.company as patient_name,
			c.phonenumber
		');
		$this->db->from('tblappointment a');
		$this->db->join('tblclients c', 'c.userid = a.userid', 'left');
		$this->db->order_by('a.appointment_date', 'ASC');

		$query = $this->db->get();
		return $query->result_array();
	}


	// Get single record by ID
	public function get_availability_by_id($id)
	{
		return $this->db->get_where('tbldoctor_availability', ['id' => $id])->row_array();
	}

	// Get single record by ID
	public function get_doctor_availability($id)
	{
		return $this->db->get_where('tbldoctor_availability', ['staff_id' => $id])->result_array();
	}

	// Insert or Update availability (based on presence of ID)
	public function save_or_update_availability($data)
	{
		if (!empty($data['id'])) {
			$this->db->where('id', $data['id']);
			$this->db->update('tbldoctor_availability', $data);
			return $data['id'];
		} else {
			$this->db->insert('tbldoctor_availability', $data);
			return $this->db->insert_id();
		}
	}

	// Delete availability record
	public function delete_availability($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('tbldoctor_availability');
		return $this->db->affected_rows() > 0;
	}










   
}
