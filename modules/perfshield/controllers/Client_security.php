<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Client_security extends ClientsController 
{
	public function __construct()
    {
        parent::__construct();
        hooks()->do_action('clients_authentication_constructor', $this);

        if (is_client_logged_in()) {
            redirect(site_url());
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
	            $user = $this->db->get(db_prefix() . 'contacts')->row();

	            if ($user) {
	                if (!app_hasher()->CheckPassword($posted_data['password'], $user->password)) {
	                    // Password failed, return
	                    set_alert('danger', _l('incorrect_password'));
	                    redirect(site_url('perfshield/client_security/reset_session'));
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

	                $this->db->update(db_prefix() . 'contacts', $updateData, ['id' => $user->id]);

	                set_alert('success', _l('session_reset'));
	                redirect(site_url('authentication'));
	            }

	            set_alert('danger', _l('user_not_found'));
	            redirect(site_url('perfshield/client_security/reset_session'));
        	}
        }

        $data['title'] = _l('reset_session');

        $this->data($data);
        $this->view('reset_session_client');
        $this->layout();
    }
}