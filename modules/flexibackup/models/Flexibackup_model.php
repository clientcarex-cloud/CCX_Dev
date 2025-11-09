<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexibackup_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //add new backup
    public function add_backup($data){
        $datecreated = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'flexibackup', [
            'backup_name' => $data['name'],
            'backup_type' => $data['type'],
            'backup_data' => '',
            'datecreated'     => $datecreated,
            'note' => $data['note']
        ]);
        return $this->db->insert_id();
    }
    //updated backupdata
    public function update_backup($data, $id){
        $backup = $this->get_backup($id);
        $existing_backup_data_value = $backup->backup_data;
        $new_backup_data_value = $data['key'];
        $new_value = ($existing_backup_data_value == '') ? $new_backup_data_value : $existing_backup_data_value.','.$new_backup_data_value;
        $this->db->where('id', $id);
        $this->db->set('backup_data', $new_value);
        $this->db->update(db_prefix().'flexibackup');
        return $this->db->affected_rows();
    }

    public function update_uploaded_to_remote($id){
        $this->db->where('id', $id);
        $this->db->set('uploaded_to_remote', 1);
        $this->db->update(db_prefix().'flexibackup');
        return $this->db->affected_rows();
    }

    //get backup
    public function get_backup($id){
        $this->db->where('id', $id);
        return $this->db->get(db_prefix().'flexibackup')->row();
    }

    //get all backups
    public function get_backups(){
        $this->db->order_by('datecreated', 'DESC');
        return $this->db->get(db_prefix().'flexibackup')->result_array();
    }

    public function delete_backup($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'flexibackup');
        return $this->db->affected_rows();
    }

    public function remove_backup_data($backup, $key){
        $id = $backup->id;
        $existing_backup_data_value = $backup->backup_data;
        $existing_backup_data_array = explode(',', $existing_backup_data_value);
        $new_backup_data_array = array();
        foreach ($existing_backup_data_array as $existing_backup_data){
            if($existing_backup_data != $key){
                $new_backup_data_array[] = $existing_backup_data;
            }
        }
        $new_backup_data_value = implode(',', $new_backup_data_array);
        $this->db->where('id', $id);
        $this->db->set('backup_data', $new_backup_data_value);
        $this->db->update(db_prefix().'flexibackup');
        return $this->db->affected_rows();
    }

    //get deletable backups
    public function get_deletable_backups($days){
        $this->db->select('id, DATEDIFF(CURDATE(), datecreated) AS days', FALSE);
        $this->db->from(db_prefix().'flexibackup');
        $this->db->having([
            'days >' => $days
        ]);

        $query = $this->db->get();
        return $query->result_array();
    }
}