<?php
// File: modules/auto_lead_assign/controllers/Lead_rollercoaster.php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_rollercoaster extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('lead_rollercoaster_model');
    }

    public function settings()
    {
        if ($this->input->post()) {
			$data = [
				'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
				'selected_roles' => json_encode($this->input->post('selected_roles') ?? []),
				'strategy' => $this->input->post('strategy'),
				'fallback_option' => $this->input->post('fallback_option'),
				'fallback_employee_id' => json_encode($this->input->post('fallback_employee_id') ?? []),
				'business_timing_from' => $this->input->post('business_timing_from'),
				'business_timing_to' => $this->input->post('business_timing_to'),
				'selected_sources' => json_encode($this->input->post('selected_sources') ?? []),

				// ✅ New Fields
				'avoid_empty_leads' => $this->input->post('avoid_empty_leads') ? 1 : 0,
				'auto_junk_leads' => $this->input->post('auto_junk_leads') ? 1 : 0,
				'junk_lead_rules' => json_encode($this->input->post('junk_lead_rules') ?? [])
			];

			$this->lead_rollercoaster_model->save_settings($data);
			set_alert('success', 'Settings updated successfully');
			redirect(admin_url('lead_rollercoaster/settings'));
		}


		$data['title'] = 'Lead Rollercoaster';
        $data['settings'] = $this->lead_rollercoaster_model->get_settings();
        $data['roles'] = $this->lead_rollercoaster_model->get_roles();
        $data['employees'] = $this->lead_rollercoaster_model->get_staff();
        $data['sources'] = $this->lead_rollercoaster_model->get_sources();

		$this->load->view('lead_rollercoaster/settings', $data);
	}
    
}
