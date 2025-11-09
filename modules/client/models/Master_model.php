<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Master_model extends App_Model
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
    
	 public function get_all($table_suffix)
	 {
		 $status_column = $table_suffix . '_status';
		 $table = db_prefix() . $table_suffix;
		 // Check if table and column exist
		 if ($this->db->table_exists($table)) {
			 $fields = $this->db->list_fields($table);
			 if (in_array($status_column, $fields)) {
				 $this->db->where($status_column, 1);
			 }
			 return $this->db->get($table)->result_array();
		 }
	 
		 return []; // Return empty array if table doesn't exist
	 }
	 

	public function add($table_suffix, $data)
	{
		return $this->db->insert(db_prefix() . $table_suffix, $data);
	}

	public function update($table_suffix, $id, $data)
	{
		$primary_key = $table_suffix . '_id';
		$this->db->where($primary_key, $id);
		return $this->db->update(db_prefix() . $table_suffix, $data);
	}


	public function delete($table_suffix, $id)
	{
		$primary_key = $table_suffix . '_id';
		$status_field = $table_suffix . '_status';

		$this->db->where($primary_key, $id);
		return $this->db->update(db_prefix() . $table_suffix, [$status_field => 0]);
	}

	public function get_by_id($table_suffix, $id)
	{
		$id_column = $table_suffix . '_id'; // e.g., enquiry_type_id
		$this->db->where($id_column, $id);
		return $this->db->get(db_prefix() . $table_suffix)->row_array();
	}




   
}
