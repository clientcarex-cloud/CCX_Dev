<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Unrecognized_login_detected extends App_mail_template
{
    public $staffID;

    public $staffEmail;

    public $loginDetails;

    public $slug = 'unrecognized-login-detected';

    public $rel_type = 'staff';

    public function __construct($staffID, $staffEmail, $loginDetails)
    {
        parent::__construct();
        $this->staffID    = $staffID;
        $this->staffEmail = $staffEmail;
        $this->loginDetails = $loginDetails;
    }

    public function build()
    {
        $this->ci->load->library(PERFSHIELD_MODULE . '/merge_fields/perfshield_merge_fields');

        $res = $this->to($this->staffEmail)->set_rel_id($this->staffID)->set_merge_fields('staff_merge_fields', $this->staffID)->set_merge_fields('perfshield_merge_fields', $this->loginDetails);
    }
}