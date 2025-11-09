<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_model extends App_Model
{
    protected $table = 'flextestimonial';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all testimonials
     * @param array $conditions
     * @return array
     */
    public function get($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get testimonial by id
     * @param int $id
     * @return array
     */
    public function get_by_id($id)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Add testimonial
     * @param array $data
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
    }

    /**
     * Update testimonial
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);
    }

    /**
     * Delete testimonial
     * @param int $id
     */
    public function delete($conditions)
    {
        $this->db->where($conditions);
        $this->db->delete(db_prefix() . $this->table);
    }
    
}