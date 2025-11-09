<?php

defined('BASEPATH') or exit('No direct script access allowed');
   
    class Smslogs extends AdminController
    {
        
        public function __construct()
    {
    parent::__construct();
    $this->load->model('Smslogs_models');
    
    }
    
    public function index()
    {
		
        $data['title']= 'SMS Logs';
        if ($this->input->is_ajax_request()) {
        $this->app->get_table_data(module_views_path('template', 'tables/sms_table'),$data);
        }
        
        $this->load->view('template/smslogs/index',$data);
    }
    
}
