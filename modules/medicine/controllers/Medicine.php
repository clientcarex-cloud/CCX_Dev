<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Medicine extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('medicine_model');
    }

    public function index()
    {
        $data['title']= 'Medicine';
        $data['medicines'] = $this->medicine_model->get_all_medicine();
      
        $this->load->view('medicine', $data);
    }

    public function medicine_1()
    {
        $data['title']= 'Medicine';
        $data['medicines'] = $this->medicine_model->get_all_medicine();
      
        $this->load->view('medicine', $data);
    }
    

    public function add_medicine(){
    
        $table = db_prefix().'medicine';
        $data['medicine_name'] = $this->input->post('medicine_name');
        
        $this->medicine_model->add_medicine($table,$data);
        set_alert('success', _l('added_successfully', _l('medicine')));
        
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function update_medicine()
    {
        $id = $this->input->post('id');
        $medicine_name = $this->input->post('medicine_name');

        // Validate if needed
        if (empty($id) || empty($medicine_name)) {
            set_alert('danger', _l('medicine_name_required'));
            redirect(admin_url('medicine'));
        }

        $this->load->model('medicine_model');

        $updated = $this->medicine_model->update_medicine($id, ['medicine_name' => $medicine_name]);

        if ($updated) {
            set_alert('success', _l('updated_successfully', _l('medicine')));
        } else {
            set_alert('warning', _l('updated_failed', _l('medicine')));
        }

        redirect(admin_url('medicine'));
    }

    public function delete($id) {
        // Check if the ID exists and delete the medicine record
        if ($id) {
            // Call the delete method from the model
            $this->medicine_model->delete_medicine($id);
            
            // Set success message in session
            set_alert('success', _l('deleted', _l('medicine')));
            
            // Redirect back to the medicine page (or wherever you want to redirect)
            redirect(admin_url('medicine'));
        } else {
            // Handle the case where the ID is invalid
            set_alert('warning', _l('delete_failed', _l('medicine')));
            redirect(admin_url('medicine'));
        }
    }

    public function get_medicine_by_id()
    {
        $id = $this->input->post('id');
        $medicine = $this->medicine_model->get_medicine_by_id($id);
        echo json_encode($medicine);
    }

    
}
