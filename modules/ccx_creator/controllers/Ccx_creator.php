<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! is_staff_logged_in()) {
            access_denied('CCX Creator');
        }
    }

    /**
     * Simple landing page that renders "Hello World".
     */
    public function index(): void
    {
        $data['title'] = 'CCX Creator';
        $this->load->view('ccx_creator/index', $data);
    }
}
