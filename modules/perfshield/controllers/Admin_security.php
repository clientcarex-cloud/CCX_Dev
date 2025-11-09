<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_security extends App_Controller 
{
	public function __construct()
    {
        parent::__construct();

        if ($this->app->is_db_upgrade_required()) {
            redirect(admin_url());
        }

        load_admin_language();
        $this->load->model('Authentication_model');
        $this->load->library('form_validation');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));

        hooks()->do_action('admin_auth_init');

        if (is_staff_logged_in() || get_option('prevent_user_from_login_more_than_once') !== '1') {
            redirect(admin_url());
        }
    }

	public function reset_session()
    {
    	$this->form_validation->set_rules('password', _l('admin_auth_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|valid_email');

        if ($this->input->post()) {
        	if ($this->form_validation->run() !== false) {
	            $posted_data = $this->input->post();

	            $this->db->where('email', $posted_data['email']);
	            $user = $this->db->get(db_prefix() . 'staff')->row();

	            if ($user) {
	                if (!app_hasher()->CheckPassword($posted_data['password'], $user->password)) {
	                    // Password failed, return
	                    set_alert('danger', _l('incorrect_password'));
	                    redirect(admin_url('perfshield/admin_security/reset_session'));
	                }

	                $deviceDetails = [
				        'ip_address' => getClientIP(),
				        'platform'   => get_instance()->agent->platform,
				        'browser'    => get_instance()->agent->browser
				    ];

				    $updateData = [
				        'device_details' => serialize($deviceDetails),
				        'is_logged_in'   => '0'
				    ];

	                $this->db->update(db_prefix() . 'staff', $updateData, ['staffid' => $user->staffid]);

	                set_alert('success', _l('session_reset'));
	                redirect(admin_url('authentication'));
	            }

	            set_alert('danger', _l('user_not_found'));
	            redirect(admin_url('perfshield/admin_security/reset_session'));
        	}
        }

        $this->load->view('reset_session_admin');
    }
}