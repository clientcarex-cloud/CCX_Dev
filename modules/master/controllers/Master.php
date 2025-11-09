<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Master extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('master_model');
    }

    private function check_customer_permissions()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('customers');
            }
        }
    }

    public function enquiry_type()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('enquiry_type');
    }

    public function state()
    {
        if (!is_admin()) {
            access_denied('state');
        }
        $this->_handle_crud('state');
    }

    public function city()
    {
        if (!is_admin()) {
            access_denied('city');
        }
        $this->_handle_crud('city');
    }
    public function pincode()
    {
        if (!is_admin()) {
            access_denied('pincode');
        }
        $this->_handle_crud('pincode');
    }

    public function patient_response()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_response');
    }

    public function patient_priority()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_priority');
    }

    public function slots()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('slots');
    }

    public function patient_source()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_source');
    }

    public function treatment()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('treatment');
    }
    public function languages()
    {
        if (!is_admin()) {
            access_denied('languages');
        }
        $this->_handle_crud('languages');
    }

    public function medicine()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('medicine');
    }

    public function consultation_fee()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('consultation_fee');
    }

    public function medicine_potency()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('medicine_potency');
    }

    public function medicine_dose()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('medicine_dose');
    }

    public function medicine_timing()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('medicine_timing');
    }

    public function patient_status()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('patient_status');
    }

    public function appointment_type()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('appointment_type');
    }
    public function call_type()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('call_type');
    }
    public function criteria()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('criteria');
    }
    public function specialization()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('specialization');
    }
	
	public function chief_complaint()
	{
		if (!is_admin()) {
			access_denied('chief_complaint');
		}
		$this->_handle_crud('chief_complaint');
	}

	public function medical_problem()
	{
		if (!is_admin()) {
			access_denied('medical_problem');
		}
		$this->_handle_crud('medical_problem');
	}

	public function medical_investigation()
	{
		if (!is_admin()) {
			access_denied('medical_investigation');
		}
		$this->_handle_crud('medical_investigation');
	}

	public function dental_investigation()
	{
		if (!is_admin()) {
			access_denied('dental_investigation');
		}
		$this->_handle_crud('dental_investigation');
	}

	public function treatment_type()
	{
		if (!is_admin()) {
			access_denied('treatment_type');
		}
		$this->_handle_crud('treatment_type');
	}

	public function treatment_sub_type()
	{
		if (!is_admin()) {
			access_denied('treatment_sub_type');
		}
		$this->_handle_crud('treatment_sub_type');
	}

	public function treatment_procedure()
	{
		if (!is_admin()) {
			access_denied('treatment_procedure');
		}
		$this->_handle_crud('treatment_procedure');
	}

	public function lab()
	{
		if (!is_admin()) {
			access_denied('lab');
		}
		$this->_handle_crud('lab');
	}

	public function lab_work()
	{
		if (!is_admin()) {
			access_denied('lab_work');
		}
		$this->_handle_crud('lab_work');
	}

	public function lab_followup()
	{
		if (!is_admin()) {
			access_denied('lab_followup');
		}
		$this->_handle_crud('lab_followup');
	}

	public function case_remark()
	{
		if (!is_admin()) {
			access_denied('case_remark');
		}
		$this->_handle_crud('case_remark');
	}

	public function suggested_diagnostics()
	{
		if (!is_admin()) {
			access_denied('suggested_diagnostics');
		}
		$this->_handle_crud('suggested_diagnostics');
	}


    public function shift()
    {
        if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->_handle_crud('shift');
    }

    private function _handle_crud($table)
	{
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data($table);
		} else {
			if ($this->input->post()) {
				if (!is_admin()) {
					access_denied($table);
				}
				$data = $this->input->post();
				$id_field = $table . '_id';

				if (isset($data[$id_field]) && $data[$id_field] != '') {
					$success = $this->master_model->update($table, $data[$id_field], $data);
					if ($success) {
						set_alert('success', _l('updated_successfully'));
					}
				} else {
					$id = $this->master_model->add($table, $data);
					if ($id) {
						set_alert('success', _l('added_successfully'));
					}
				}

				redirect(admin_url('master/' . $table));
			}

			$data['title'] = _l($table);
			$data['slug'] = $table;
			$data['field_name'] = $table . '_name';
			$data['records'] = $this->master_model->get_all($table);

			// Inject states only for city
			if ($table === 'pincode') {
				$data['states'] = $this->master_model->get_all('state');
				$data['cities'] = $this->master_model->get_all('city');
			}
			
			if ($table === 'city') {
				$data['states'] = $this->master_model->get_all('state');
			}
			if ($table === 'treatment_sub_type') {
				$data['treatment_type'] = $this->master_model->get_all('treatment_type');
			}

			$this->load->view('master', $data);
		}
	}


    public function get_record_by_id($slug)
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->load->model('master_model');
            $record = $this->master_model->get_by_id($slug, $id);

            echo json_encode($record);
        } else {
            show_404();
        }
    }



    public function delete($table, $id)
    {
        $this->check_customer_permissions();
        if (!$id) {
            redirect(admin_url('master/' . $table));
        }
        $success = $this->master_model->delete($table, $id);
        if ($success) {
            set_alert('success', _l('deleted'));
        }
        redirect(admin_url('master/' . $table));
    }
	
	public function master_settings(){
		$data['title'] = _l('master_settings');
		$data['slug'] = 'master_settings';
		$data['patient_response'] = $this->master_model->get_all("patient_response");
		$data['branches'] = $this->master_model->get_branches();
		$data['results'] = $this->master_model->get_master_settings();
		function get_options($table){
			$CI = &get_instance();
			return $CI->master_model->get_options($table);
		}
		$this->load->view('master_settings', $data);
	}
	public function save_master_settings(){
		$this->master_model->save_master_settings();
        redirect(admin_url('master/master_settings'));
	}
	
	public function get_cities_by_state() {
		$state_id = $this->input->post('state_id');
		$cities = $this->db->where('state_id', $state_id)->get(db_prefix() . 'city')->result_array();
		echo json_encode($cities);
	}

}
