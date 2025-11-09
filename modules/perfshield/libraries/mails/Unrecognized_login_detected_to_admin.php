<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Unrecognized_login_detected_to_admin extends App_mail_template
{
    public $staffID;

    public $adminEmail;

    public $loginDetails;

    public $slug = 'unrecognized-login-detected-to-admin';

    public $rel_type = 'staff';

    public function __construct($staffID, $adminEmail, $loginDetails)
    {
        parent::__construct();
        $this->staffID      = $staffID;
        $this->adminEmail   = $adminEmail;
        $this->loginDetails = $loginDetails;
    }

    public function build()
    {
        $this->ci->load->library(PERFSHIELD_MODULE . '/merge_fields/perfshield_merge_fields');
        
        $res = $this->to($this->adminEmail)->set_rel_id($this->staffID)->set_merge_fields('staff_merge_fields', $this->staffID)->set_merge_fields('perfshield_merge_fields', $this->loginDetails);
    }
}