<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lead_rollercoaster_model extends App_Model
{
    public function get_settings()
    {
        return $this->db->get(db_prefix() . 'lead_rollercoaster_settings')->row();
    }

    public function save_settings($data)
    {
        if ($this->db->count_all(db_prefix() . 'lead_rollercoaster_settings') > 0) {
            $this->db->update(db_prefix() . 'lead_rollercoaster_settings', $data);
        } else {
            $this->db->insert(db_prefix() . 'lead_rollercoaster_settings', $data);
        }
    }

    public function get_roles()
    {
        return $this->db->get(db_prefix() . 'roles')->result_array();
    }

    public function get_staff()
    {
        return $this->db->get(db_prefix() . 'staff')->result_array();
    }

    public function get_sources()
    {
        return $this->db->get(db_prefix() . 'leads_sources')->result_array();
    }
}
