<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Doctor extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('client_model');
        $this->load->model('master_model');
        $this->load->model('doctor_model');
        $this->load->helper('custom'); // loads custom_helper.php
        $this->load->helper('file_upload');

    }
    /* List all clients */
    public function index()
    {   
         if (staff_cant('view',  'doctor')) {
            access_denied('doctors');
        }
		
        $data['title'] = "Doctors";
		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('client', 'tables/doctor_table'), $data);
        }
        $this->load->view('doctors', $data);
    }
    public function add_doctor(){
		 if (staff_cant('create',  'doctor')) {
            access_denied('doctors');
        }
        $data['title'] = "Doctor";
        $data['departments'] = $this->doctor_model->get_departments();
        $data['specialization'] = $this->doctor_model->get_specialization();
        $data['shift'] = $this->doctor_model->get_shift();
        $data['role'] = $this->doctor_model->get_role();
        $data['branch'] = $this->doctor_model->get_branch();
        $this->load->view('add_doctor', $data);
    }
    public function save_doctor()
    {
		 if (staff_cant('create',  'doctor')) {
            access_denied('doctors');
        }
        $data = $this->input->post();
        if (!empty($data)) {
           $result = $this->doctor_model->save_doctor($data);
            if ($result) {
                set_alert('success', _l('added_successfully'));
                redirect('client/doctor');
            } else {
                set_alert('danger', _l('something_went_wrong'));
                redirect('client/doctor');
            }
        } else {
            show_404();
        }
    }

    public function edit_doctor($id)
    {
		if (staff_cant('edit',  'doctor')) {
            access_denied('doctors');
        }
        $data['title'] = 'Edit Doctor';
        $data['doctor'] = $this->doctor_model->get_doctors($id);
        $data['doctor_new_fields'] = $this->doctor_model->get_doctor_new_fields($id);
        $data['doctor_time_slots'] = $this->doctor_model->get_doctor_time_slots($id);
        $data['departments'] = $this->doctor_model->get_departments();
        $data['specialization'] = $this->doctor_model->get_specialization();
        $data['role'] = $this->doctor_model->get_role();
        $data['branch'] = $this->doctor_model->get_branch();
        $data['shift'] = $this->doctor_model->get_shift();
        $this->load->view('edit_doctor', $data);
    }
    public function update_doctor()
    {
		if (staff_cant('edit',  'doctor')) {
            access_denied('doctors');
        }
        $data = $this->input->post();
        $doctor_id = $data['doctor_id'];

        // Check if the data is not empty and the doctor_id is valid
        if (!empty($data) && !empty($doctor_id)) {
            // Pass the data and doctor_id to the model for updating
            $result = $this->doctor_model->update_doctor($doctor_id, $data);

            if ($result['success']) {
                // Success message
                set_alert('success', _l('updated_successfully'));
            } else {
                // Failure message, include the error message from the model
                set_alert('danger', _l($result['message']));
            }
            // Redirect back to doctor listing
            redirect('client/doctor');
        } else {
            // If no data is provided, show 404 error
            show_404();
        }
    }
  
	public function get_appointments_json()
    {
        $appointments = $this->doctor_model->get_appointments();

        $events = [];
        foreach ($appointments as $a) {
            // Format date for FullCalendar - just date part or datetime as ISO8601
            $start = $a['appointment_date'];

            // Compose a title with info available - e.g. appointment id and user id (or patient id)
            $title =  $a['patient_name'];

            $events[] = [
                'id' => $a['appointment_id'],
                'title' => $title,
                'start' => $start,
                'extendedProps' => [
                    'userid' => $a['userid'],
                    'remarks' => $a['remarks'],
                    'visit_status' => $a['visit_status'],
                ]
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

	// Show availability list and add form
	public function availability($staff_id = '')
	{
		if (!$staff_id) {
			set_alert('warning', 'No doctor selected');
			redirect(admin_url('staff'));
		}

		$doctor = $this->doctor_model->get_doctors($staff_id);
		$availability = $this->doctor_model->get_doctor_availability($staff_id);

		$data['staff_id'] = $staff_id;
		$data['doctor'] = $doctor;
		$data['availability'] = $availability;
		$data['title'] = 'Doctor Availability';
		$this->load->view('availability', $data);
	}

	// Save or update
	public function save_availability()
	{
		$data = $this->input->post();
		$data['start_time'] = $data['start_time'] ?? '00:00:00';
		$data['end_time'] = $data['end_time'] ?? '00:00:00';
		$data['time_gap_minutes'] = $data['time_gap_minutes'] ?? 15;

		$id = $this->doctor_model->save_or_update_availability($data);
		if ($id) {
			set_alert('success', 'Availability saved.');
		} else {
			set_alert('danger', 'Error saving availability.');
		}
		redirect(admin_url('client/doctor/availability/' . $data['staff_id']));
	}

	// Edit form
	public function edit_availability($id)
	{
		$record = $this->doctor_model->get_availability_by_id($id);
		if (!$record) {
			set_alert('warning', 'Availability not found');
			redirect(admin_url('staff'));
		}

		$doctor = $this->doctor_model->get_doctors($record['staff_id']);
		$data['record'] = $record;
		$data['doctor'] = $doctor;
		$data['title'] = 'Edit Doctor Availability';
		$this->load->view('edit_availability', $data);
	}

	// Delete
	public function delete_availability($id)
	{
		$record = $this->doctor_model->get_availability_by_id($id);
		if ($record && $this->doctor_model->delete_availability($id)) {
			set_alert('success', 'Deleted successfully');
		} else {
			set_alert('warning', 'Unable to delete');
		}
		redirect(admin_url('client/doctor/availability/' . $record['staff_id']));
	}


}
