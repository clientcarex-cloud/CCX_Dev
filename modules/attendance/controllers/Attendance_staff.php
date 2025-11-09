<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Attendance_staff extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance/attendance_staff_model');
    }
	
	public function attendance_staff()
    {
		if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $slug = 'attendance_staff';
        $title = 'Attendance Staff';
        $field_name = 'staff_id';

        if ($this->input->post()) {
            $id = $this->input->post('id');
            $field_value = $this->input->post($field_name);
            $punch_id = $this->input->post('punch_id');

            $data = [
                'staff_id' => $field_value,
                'punch_id' => $punch_id,
            ];

            $this->attendance_staff_model->upsert($slug, $data, $id);
            set_alert('success', 'Saved successfully');
			
            redirect(base_url('attendance/attendance_staff'));
        }

        $data['slug'] = $slug;
        $data['title'] = $title;
        $data['field_name'] = $field_name;
        $data['records'] = $this->attendance_staff_model->get_all($slug);
        $data['staff'] = $this->staff_model->get();
        $this->load->view('attendance_staff', $data);
    }

    public function auth_token()
    {
		if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $slug = 'attendance_auth_token';
        $title = 'Auth Token';
        $field_name = 'token';

        if ($this->input->post()) {
            $id = $this->input->post('id');
            $field_value = $this->input->post($field_name);

            $data = ['token' => $field_value];

            $this->attendance_staff_model->upsert($slug, $data, $id);
            set_alert('success', 'Saved successfully');
            redirect(base_url('attendance/auth_token'));
        }

        $data['slug'] = $slug;
        $data['title'] = $title;
        $data['field_name'] = $field_name;
        $data['records'] = $this->attendance_staff_model->get_all($slug);

        $this->load->view('auth_token', $data);
    }

    public function delete($slug, $id)
    {
		if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $this->attendance_staff_model->delete($slug, $id);
        set_alert('success', 'Deleted successfully');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function get_record_by_id($slug)
    {
		if (!is_admin()) {
            access_denied('enquiry_type');
        }
        $id = $this->input->post('id');
        $data = $this->attendance_staff_model->get_by_id($slug, $id);
        echo json_encode($data);
    }
}
