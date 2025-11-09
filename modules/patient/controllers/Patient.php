<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Patient extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('patient_model');
        $this->load->helper('patient_menu');

    }

    /*public function index($id = '')
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('customers');
            }
        }
        $data['title']          = _l('clients');
       
        $this->load->view('patient_manage', $data);
    }*/

    public function index()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('customers');
            }
        }

        $this->load->model('contracts_model');
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $data['groups']         = $this->clients_model->get_groups();
        $data['title']          = _l('clients');

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $this->load->model('invoices_model');
        $data['invoice_statuses'] = $this->invoices_model->get_statuses();

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('projects_model');
        $data['project_statuses'] = $this->projects_model->get_project_statuses();

        $data['customer_admins'] = $this->clients_model->get_customers_admin_unique_ids();

        $whereContactsLoggedIn = '';
        if (staff_cant('view', 'customers')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }

        $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

        $data['countries'] = $this->clients_model->get_clients_distinct_countries();
        $data['table'] = App_table::find('clients');
        $this->load->view('admin/clients/manage', $data);
    }

    public function add_patient()
    {
        $data['title']= 'patient';
        $this->load->view('patient_form', $data);
    }
    

    public function save_patient(){
    
        $table = db_prefix().'patient';
        $data['patient_name'] = $this->input->post('patient_name');
        
        $this->patient_model->add_patient($table,$data);
        set_alert('success', _l('added_successfully', _l('patient')));
        
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function update_patient()
    {
        $id = $this->input->post('id');
        $patient_name = $this->input->post('patient_name');

        // Validate if needed
        if (empty($id) || empty($patient_name)) {
            set_alert('danger', _l('patient_name_required'));
            redirect(admin_url('patient'));
        }

        $this->load->model('patient_model');

        $updated = $this->patient_model->update_patient($id, ['patient_name' => $patient_name]);

        if ($updated) {
            set_alert('success', _l('updated_successfully', _l('patient')));
        } else {
            set_alert('warning', _l('updated_failed', _l('patient')));
        }

        redirect(admin_url('patient'));
    }

    public function delete($id) {
        // Check if the ID exists and delete the patient record
        if ($id) {
            // Call the delete method from the model
            $this->patient_model->delete_patient($id);
            
            // Set success message in session
            set_alert('success', _l('deleted', _l('patient')));
            
            // Redirect back to the patient page (or wherever you want to redirect)
            redirect(admin_url('patient'));
        } else {
            // Handle the case where the ID is invalid
            set_alert('warning', _l('delete_failed', _l('patient')));
            redirect(admin_url('patient'));
        }
    }

    public function get_patient_by_id()
    {
        $id = $this->input->post('id');
        $patient = $this->patient_model->get_patient_by_id($id);
        echo json_encode($patient);
    }

    
}
