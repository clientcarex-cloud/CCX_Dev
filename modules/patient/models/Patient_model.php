<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Patient_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single patient
     */
    
    public function get_all_patient($exclude_notified = true)
    {

       $goals = $this->db->get(db_prefix() . 'patient')->result_array();
        return $goals;
        
    }

    public function add_patient($table,$data){

		$this->db->insert($table,$data);
    
    }

    public function get_patient_by_id($id)
    {
        return $this->db->get_where(db_prefix() . 'patient', ['id' => $id])->row_array();
    }

    public function update_patient($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'patient', $data);
    }

    public function delete_patient($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'patient');
    }


   
}
