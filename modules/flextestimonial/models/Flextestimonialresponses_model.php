<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonialresponses_model extends App_Model
{
    protected $table = 'flextestimonialresponses';

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

    public function get_featured($limit = 6, $offset = 0)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('featured', '1');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
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
     * Add testimonial
     * @param array $data
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        return $this->db->insert_id();
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