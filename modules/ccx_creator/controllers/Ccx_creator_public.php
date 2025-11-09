<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator_public extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('ccx_creator/ccx_creator_model');
    }

    public function dashboard($token)
    {
        $payload = $this->ccx_creator_model->get_dashboard_data_by_token($token);
        if (empty($payload)) {
            show_404();
        }

        $this->load->view('ccx_creator/dashboards/embed', $payload);
    }

    public function api($token)
    {
        $apiToken = $this->ccx_creator_model->validate_api_token($token, 'read');
        if (! $apiToken) {
            show_404();
        }

        $limit = (int) $this->input->get('limit');
        if ($limit <= 0 || $limit > 200) {
            $limit = 50;
        }

        $records = $this->ccx_creator_model->get_form_records_for_api((int) $apiToken['form_id'], $limit);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'form_id' => $apiToken['form_id'],
                'label'   => $apiToken['label'],
                'records' => $records,
            ]));
    }
}
