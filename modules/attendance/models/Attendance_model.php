<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    public function get_today_attendance($staff_id, $today)
    {
        $this->db->where('addedfrom', $staff_id);
        $this->db->where('DATE(startdate)', $today);
        $query = $this->db->get(db_prefix() . 'tasks');
        return $query->row_array();
    }

    public function insert_punch_in($staff_id)
    {
		$get_staff = $this->db->get_where(db_prefix() . 'staff', array("staffid"=>$staff_id))->row();
		if($get_staff){
			$staff_name = $get_staff->firstname.' '.$get_staff->lastname; 
			$this->db->insert(db_prefix() . 'tasks', [
            'name'        => 'Attendance|'.$staff_name.'|'.date('d-m-Y H:i:s'),
            'description' => 'Auto punch-in from machine.',
            'dateadded'   => date('Y-m-d H:i:s'),
            'startdate'   => date('Y-m-d'),
            'addedfrom'   => $staff_id,
            'priority'      => 2,
            'status'      => 4,
			]);
			
			$task_id = $this->db->insert_id();
			
			$data = array(
			"task_id" => $task_id,
			"start_time" => time(),
			"staff_id" => $staff_id,
			);
			$this->db->insert(db_prefix() . 'taskstimers', $data);
		}
        
    }

    public function update_punch_out($task_id)
    {
		$get_task = $this->db->get_where(db_prefix() . 'tasks', array("id"=>$task_id))->row();
		if($get_task){
			$this->db->where('id', $task_id);
			$this->db->update(db_prefix() . 'tasks', [
				'datefinished' => date('Y-m-d H:i:s'),
				'status'       => 5, 
			]);
			
			$staff_id = $get_task->addedfrom;
			$this->db->where('task_id', $task_id);
			$this->db->where('staff_id', $staff_id);
			$this->db->where('end_time IS NULL', null, false);
			$get_task_time = $this->db->get(db_prefix() . 'taskstimers')->row();
			if($get_task_time){
				$id = $get_task_time->id;
				$time_update = array(
				"end_time" =>time()
				);
				$this->db->where('id', $id);
				$this->db->update(db_prefix() . 'taskstimers', $time_update);
			}else{
				$data = array(
				"task_id" => $task_id,
				"start_time" => time(),
				"staff_id" => $staff_id,
				);
				$this->db->insert(db_prefix() . 'taskstimers', $data);
			}
		}
    }
	
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
