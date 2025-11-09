

<?php

defined('BASEPATH') or exit('No direct script access allowed');
   
    class Templates extends AdminController
    {
    public function __construct()
    {
    parent::__construct();
    $this->load->model('Office_models');
    
    }
    
    public function index(){
    
    
    $data['title']= 'DLT Templates';
	
    if ($this->input->is_ajax_request()) {
        $this->app->get_table_data(module_views_path('template', 'tables/template_table'),$data);
        }
    $this->load->view('template/template/index',$data);
    
    }
    
    public function add_template(){
    
    $table = db_prefix().'_templates';
    $data['template_id'] = $this->input->post('template_id');
    $data['template_name'] = $this->input->post('template_name');
    $data['template_body'] = $this->input->post('template_body');
    $data['constant'] = $this->input->post('constant');
    $data['created_at'] = date("Y-m-d h:i:s");
    
    $this->Office_models->add_template($table,$data);
    
   $this->session->set_userdata('add_template', '1');
    redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function delete_templates($id){
    
    $table = db_prefix().'_templates';
    $this->Office_models->delete_row($table,$id);
    $this->session->set_userdata('delete_templates', '1');
    redirect($_SERVER['HTTP_REFERER']);
    
    }
    
     public function edit_template(){
    $id = $this->input->post('id');
    $data['template_id'] = $this->input->post('template_id');
    $data['template_name'] = $this->input->post('template_name');
    $data['template_body'] = $this->input->post('template_body');
    $data['constant'] = $this->input->post('constant');
    
    $table = db_prefix().'_templates';
    $this->Office_models->edit($table,$data,$id);
    $this->session->set_userdata('edit_templates', '1');
    redirect($_SERVER['HTTP_REFERER']);
    
    }
	
	public function update_template_status() {
		$id     = $this->input->post('id');
		$status = $this->input->post('status');

		$this->db->where('id', $id);
		$updated = $this->db->update(db_prefix() . '_templates', ['template_status' => $status]);

		echo json_encode([
			'success' => $updated,
			'csrfHash' => $this->security->get_csrf_hash()
		]);
	}

    

}
