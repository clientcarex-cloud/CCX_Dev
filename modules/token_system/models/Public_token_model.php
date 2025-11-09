<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Public_token_model extends CI_Model
{
    
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
	
	public function queued_patients($id = "", $status = "") {
    $this->db->select('
        t.token_number,
        t.token_status,
        p.userid,
        p.company,
        p.phonenumber,
        cn.salutation,
        d.staffid,
        CONCAT(d.firstname, " ", d.lastname) AS doctor_name
    ');
    $this->db->from(db_prefix() . 'tokens t');
    $this->db->join(db_prefix() . 'clients p', 'p.userid = t.patient_id', 'left');
    $this->db->join(db_prefix() . 'clients_new_fields cn', 'cn.userid = p.userid', 'left');
    $this->db->join(db_prefix() . 'staff d', 'd.staffid = t.doctor_id', 'left');
    $this->db->join(db_prefix() . 'counter c', "c.doctor_id = t.doctor_id", 'left');
    $this->db->where("c.counter_id", $id);
    $this->db->where("date", date("Y-m-d"));

    if ($status) {
        if ($status == 'Pending') {
            $this->db->where_in('t.token_status', ['Pending', 'Recall']);
        } else {
            $this->db->where('t.token_status', $status);
        }
    }

    $this->db->order_by("t.token_id", "ASC");
    return $this->db->get()->result_array();
}

	public function get_display_config($display_id){
		 // Update counter data in the database
        $this->db->where('id', $display_id);
        return $this->db->get(db_prefix() .'display_config')->row();
	}
}
