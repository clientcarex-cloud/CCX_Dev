<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mailflowunsubscribe extends App_Controller
{
    public function __construct()
    {
        hooks()->do_action('after_clients_area_init', $this);

        parent::__construct();
        $this->load->model('mailflow_model');
    }

    public function index()
    {
        show_404();
    }

    public function opt_out($email = '')
    {
        if (empty($email)) {
            die('Invalid Email');
        }

        $decryptMail = mailflow_encryption($email, 1);

        $decryptMail = stripslashes($decryptMail);
        $decryptMail = htmlspecialchars($decryptMail, ENT_QUOTES, 'UTF-8');

        if (!filter_var($decryptMail, FILTER_VALIDATE_EMAIL)) {
            die('Invalid Email');
        }

        $unsubscribedEmail = $this->mailflow_model->getUnsubscribedEmail($decryptMail);

        if (!empty($unsubscribedEmail)) {
            die('Already unsubscribed!');
        }

        $unsubscribe = $this->mailflow_model->addUnsubscribedEmail(['email' => $decryptMail, 'created_at' => date('Y-m-d H:i:s')]);

        if ($unsubscribe) {
            die('Unsubscribed successfully!');
        }

        die('Failed to unsubscribe');
    }

}
