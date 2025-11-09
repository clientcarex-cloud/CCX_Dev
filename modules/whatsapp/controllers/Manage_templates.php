<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Manage_templates extends AdminController {
    public function __construct() {
        parent::__construct();
        $this->load->model('whatsapp/Message_model');
    }

    public function index() {
        if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path('whatsapp', 'tables/message_config_table'));
		}


        $data['title'] = 'WhatsApp Message Templates';
        $this->load->view('whatsapp/manage', $data);
    }

    public function save() {
        $data = $this->input->post();
        if (isset($data['id']) && $data['id']) {
            $this->db->where('id', $data['id']);
            $this->db->update(db_prefix().'message_config', $data);
        } else {
            $this->db->insert(db_prefix().'message_config', $data);
        }
        redirect(admin_url('whatsapp/manage_templates'));
    }
}