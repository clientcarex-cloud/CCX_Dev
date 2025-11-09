<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Eod_model extends App_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'eod';
    }

    public function get_all()
    {
        return $this->db->get($this->table)->result();
    }

    public function my_eod()
    {
		$this->db->select("eod.*, staff.firstname, staff.lastname, branch.name as branch, roles.name as designation");
		$this->db->from(db_prefix() . 'eod as eod');
		$this->db->join(db_prefix() . 'staff staff', 'staff.staffid = eod.staffid', 'left');
		$this->db->join(db_prefix() . 'customers_groups branch', 'branch.id = eod.staffid', 'left');
		$this->db->join(db_prefix() . 'roles roles', 'roles.roleid = staff.role', 'left');
		
		$this->db->where(array("eod.staffid"=>get_staff_user_id()));
		
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
