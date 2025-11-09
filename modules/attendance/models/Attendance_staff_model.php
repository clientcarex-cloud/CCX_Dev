<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Attendance_staff_model extends CI_Model
{
    
	public function get_all($table)
    {
        return $this->db->get(db_prefix() . $table)->result_array();
    }

    public function get_by_id($table, $id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . $table)->row_array();
    }

    public function upsert($table, $data, $id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . $table, $data);
        } else {
            $this->db->insert(db_prefix() . $table, $data);
        }
    }

    public function delete($table, $id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $table);
    }
}
