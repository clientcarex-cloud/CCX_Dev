<?php

defined('BASEPATH') or exit('No direct script access allowed');
   
    class Balance extends AdminController
    {
        
     public function __construct()
    {
    parent::__construct();
    $this->load->model('Smscredits_models');
    
    }
    
    public function index()
    {
        if ($this->input->is_ajax_request()) {
        $this->app->get_table_data(module_views_path('template', 'tables/smscredits_table'),$data);
        }
        
        $data['title']= 'SMS Balance';
        $this->load->view('template/balance/index',$data);
    }
    
}
