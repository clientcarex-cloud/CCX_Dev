<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Medicine_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single medicine
     */
    
    public function get_all_medicine($exclude_notified = true)
    {

       $goals = $this->db->get(db_prefix() . 'medicine')->result_array();
        return $goals;
        
    }

    public function add_medicine($table,$data){

		$this->db->insert($table,$data);
    
    }

    public function get_medicine_by_id($id)
    {
        return $this->db->get_where(db_prefix() . 'medicine', ['id' => $id])->row_array();
    }

    public function update_medicine($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'medicine', $data);
    }

    public function delete_medicine($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'medicine');
    }


   
}
