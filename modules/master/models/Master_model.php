<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Master_model extends App_Model
{
	private $current_branch_id;

    public function __construct()
    {
        parent::__construct();

        // Automatically fetch and store branch_id of the logged-in staff
        $this->current_branch_id = $this->get_logged_in_staff_branch_id();
    }

    /**
     * Get all records from a master table
     */
    public function get_all($table_suffix)
    {
        $status_column = $table_suffix . '_status';
        $table = db_prefix() . $table_suffix;

        if ($this->db->table_exists($table)) {
            $fields = $this->db->list_fields($table);
			
			if ($table_suffix === 'pincode') {
				// Join with city to get city_name
				$this->db->select('p.*, c.city_name, s.state_name');
				$this->db->from($table . ' p');
				$this->db->join(db_prefix() . 'city c', 'c.city_id = p.city_id', 'left');
				$this->db->join(db_prefix() . 'state s', 's.state_id = c.state_id', 'left');
				if (in_array($status_column, $fields)) {
					$this->db->where('p.' . $status_column, 1);
				}
				return $this->db->get()->result_array();
			}

            if ($table_suffix === 'city') {
                // Join with state to get state_name
                $this->db->select('c.*, s.state_name');
                $this->db->from($table . ' c');
                $this->db->join(db_prefix() . 'state s', 's.state_id = c.state_id', 'left');
                if (in_array($status_column, $fields)) {
                    $this->db->where('c.' . $status_column, 1);
                }
                return $this->db->get()->result_array();
            }if ($table_suffix === 'treatment_sub_type') {
                $this->db->select('sub_type.*, type.treatment_type_name');
                $this->db->from($table . ' sub_type');
                $this->db->join(db_prefix() . 'treatment_type type', 'type.treatment_type_id = sub_type.treatment_type_id', 'left');
                if (in_array($status_column, $fields)) {
                    $this->db->where('sub_type.' . $status_column, 1);
                }
                return $this->db->get()->result_array();
            } else {
                // Normal master list
                if (in_array($status_column, $fields)) {
                    $this->db->where($status_column, 1);
                }
                return $this->db->get($table)->result_array();
            }
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
        $id_column = $table_suffix . '_id';

        if ($table_suffix === 'city') {
            // Join state for edit view
            $this->db->select('c.*, s.state_name');
            $this->db->from(db_prefix() . 'city c');
            $this->db->join(db_prefix() . 'state s', 's.state_id = c.state_id', 'left');
            $this->db->where('c.' . $id_column, $id);
            return $this->db->get()->row_array();
        }
		
		if ($table_suffix === 'pincode') {
			// Join city for edit view
			$this->db->select('p.*, c.city_name');
			$this->db->from(db_prefix() . 'pincode p');
			$this->db->join(db_prefix() . 'city c', 'c.city_id = p.city_id', 'left');
			$this->db->where('p.' . $id_column, $id);
			return $this->db->get()->row_array();
		}


        $this->db->where($id_column, $id);
        return $this->db->get(db_prefix() . $table_suffix)->row_array();
    }

    public function save_master_settings()
	{
        $branchId = $this->current_branch_id;
        if ($branchId === null) {
            $branchId = 0;
        }

		foreach ($_POST as $title => $values) {
			if (!is_array($values)) {
				$values = [$values];
			}

			$csv = !empty($values) ? implode(', ', array_map('trim', $values)) : '';

			$this->db->where('title', $title);
			$this->db->where('branch_id', $branchId);
			$this->db->update(db_prefix() . 'master_settings', [
				'options' => $csv,
			]);
		}
	}



    public function get_master_settings()
    {
        $branchId = $this->current_branch_id;
        if ($branchId === null) {
            $branchId = 0;
        }

        $settings = $this->db
            ->where('branch_id', $branchId)
            ->get(db_prefix() . 'master_settings')
            ->result_array();

        $requiredSettings = [
            [
                'title'        => 'medicine_period_mandatory_roles',
                'table'        => 'roles',
                'multi_select' => 1,
            ],
        ];

        $existingTitles = array_column($settings, 'title');
        $newSettingInserted = false;

        foreach ($requiredSettings as $required) {
            if (!in_array($required['title'], $existingTitles, true)) {
                $this->db->insert(
                    db_prefix() . 'master_settings',
                    [
                        'title'        => $required['title'],
                        'table'        => $required['table'],
                        'options'      => '',
                        'multi_select' => $required['multi_select'],
                        'branch_id'    => $branchId,
                    ]
                );
                $newSettingInserted = true;
            }
        }

        if ($newSettingInserted) {
            $settings = $this->db
                ->where('branch_id', $branchId)
                ->get(db_prefix() . 'master_settings')
                ->result_array();
        }

        return $settings;
    }

    public function get_options($table)
    {
        if ($table == 'patient_response') {
            $this->db->select("patient_response_name as name");
            return $this->db->get(db_prefix() . 'patient_response')->result_array();
        } elseif ($table == 'leads_status') {
            $this->db->select("name as name");
            return $this->db->get(db_prefix() . 'leads_status')->result_array();
        } elseif ($table == 'roles') {
            $this->db->select('roleid, name');
            return $this->db->get(db_prefix() . 'roles')->result_array();
        } elseif ($table == 'show_finance_in_doctor_reports') {
          return [
				['name' => 'Yes'],
				['name' => 'No']
			];
        }elseif ($table == 'clients') {
			return [
				['name' => 'salutation'],
				['name' => 'company'],
				['name' => 'gender'],
				['name' => 'age'],
				['name' => 'marital_status'],
				['name' => 'email_id'],
				['name' => 'contact_number'],
				['name' => 'alt_number1'],
				['name' => 'alt_number2'],
				['name' => 'city'],
				['name' => 'area'],
				['name' => 'pincode'],
				// add more fields here as needed
			];
		}

    }

    // Optional utility if you want states directly
    public function get_states()
    {
        return $this->db->get(db_prefix() . 'state')->result_array();
    }
    // Optional utility if you want states directly
    public function get_branches()
    {
        return $this->db->get(db_prefix() . 'customers_groups')->result_array();
    }
	
	private function get_logged_in_staff_branch_id()
    {
        $staff_id = get_staff_user_id();
        if ($staff_id) {
            $this->db->select('branch_id');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', $staff_id);
            $row = $this->db->get()->row();
            return $row ? $row->branch_id : null;
        }
        return null;
    }
}
