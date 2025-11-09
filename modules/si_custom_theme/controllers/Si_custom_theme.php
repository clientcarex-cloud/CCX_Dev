<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Si_custom_theme extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		if (!is_admin() && !has_permission('si_custom_theme', '', 'view')) {
			access_denied(_l('si_custom_theme'));
		}
	}
	
	public function index()
	{
		if (!get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activated') || get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activation_code')=='') {
			access_denied(_l('si_custom_theme'));
		}
		$data['title'] = _l('si_custom_theme_title');
		$data['selected_theme'] = get_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_theme');
		$data['selected_client_theme'] = get_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_clients_theme');
		$data['theme_list'] = $this->si_custom_theme_model->get_themes();
		$this->load->view('si_custom_theme_view', $data);
	}
	
	public function reset_theme()
	{
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_style', '[]');
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_theme', 1);
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_clients_theme', 1);
		$success = $this->si_custom_theme_model->reset_theme();
		if ($success)
			set_alert('success', _l('reset', _l('si_custom_theme')));
		redirect(admin_url('si_custom_theme'));
	}
	
	public function get_theme()
	{
		$theme_id = $this->input->post('theme');
		if(is_numeric($theme_id)){
			echo si_custom_theme_render(['general', 'texts','alerts', 'tabs', 'buttons', 'admin', 'modals', 'tags'],$theme_id);
		}	
	}
	
	public function get_copy_theme()
	{
		$theme_id = $this->input->post('theme');
		if(is_numeric($theme_id)){
			echo json_encode(si_custom_theme_get_applied_styling_area($theme_id));
			die();
		}	
	}
	
	public function save()
	{
		hooks()->do_action('before_save_si_custom_theme');
		$admin_area = nl2br(trim($this->input->post('admin_area')));
		$clients_area = nl2br(trim($this->input->post('clients_area')));
		$clients_and_admin = nl2br(trim($this->input->post('clients_and_admin')));
		$default_theme = $this->input->post('default_theme');
		$default_clients_theme = $this->input->post('default_clients_theme');
		$theme_name = nl2br(trim($this->input->post('theme_name')));
		$edit_theme = $this->input->post('edit_theme');
		$new_theme = is_numeric($this->input->post('new_theme'));
		#add new theme
		if($this->input->post('theme_name')!='' && $this->input->post('data') !='[]' && $new_theme){
			$data = array('theme_name'=>$theme_name,
					'theme_style'=>$this->input->post('data'));
			$success = $this->si_custom_theme_model->add($data);
			if ($success)
				set_alert('success', _l('added_successfully', _l('si_custom_theme')));				
		}
		#edit existing theme
		if($edit_theme!='' && is_numeric($edit_theme) && !$new_theme){
			$data = array('theme_style'=>$this->input->post('data'));
			$success = $this->si_custom_theme_model->update($data,$edit_theme);
			if ($success) {
					set_alert('success', _l('updated_successfully', _l('si_custom_theme')));
			}
		}
		$theme = $this->si_custom_theme_model->get_themes($default_theme);
		if($theme)		
			update_option(SI_CUSTOM_THEME_MODULE_NAME.'_style', $theme['theme_style']);
			
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_theme', $default_theme);
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_default_clients_theme', $default_clients_theme);
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_custom_admin_area', $admin_area);
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_custom_clients_area', $clients_area);
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_custom_clients_and_admin_area', $clients_and_admin);
	}
	
	public function delete($theme_id='')
	{
		if(is_numeric($theme_id)){
			$success = $this->si_custom_theme_model->delete($theme_id);
			if ($success)
				set_alert('success', _l('deleted', _l('si_custom_theme')));
			else
				set_alert('danger', _l('si_ct_error_in_delete'));	
		}
		redirect(admin_url('si_custom_theme'));	
	}
	
	public function upload_bg()
	{
		//save other options
		$settings = $this->input->post('settings');
		foreach($settings as $key=>$value)
			update_option($key, $value);
		
		//upload image
		handle_si_custom_theme_image_upload();#save if any image is uploaded
		redirect(admin_url('si_custom_theme?tab=tab_settings'));
	}
	
	public function remove_bg_image($type='')
	{
		if($type=='')
			redirect(admin_url('si_custom_theme'));
		$fileName = get_option(SI_CUSTOM_THEME_MODULE_NAME.'_bg_img_'.$type);
		$path = SI_CUSTOM_THEME_UPLOAD_PATH. $fileName;
		if (file_exists($path)) {
			unlink($path);
		}
		update_option(SI_CUSTOM_THEME_MODULE_NAME.'_bg_img_'.$type, '');
		redirect(admin_url('si_custom_theme?tab=tab_settings'));
	}

	//get theme name and thumbnail colors for custom theme
	public function get_theme_name_data_ajax($theme_id)
	{
		if(is_numeric($theme_id)){
			$theme = $this->si_custom_theme_model->get_themes($theme_id);
			if($theme){
				$data['theme'] = $theme;
				echo $this->load->view('includes/_edit_custom_theme_name_modal', $data, true);
			}
		}
	}

	//save theme name and thumbnail colors for custom theme
	public function save_custome_theme_name($theme_id)
	{
		if(is_numeric($theme_id)){
			$theme = $this->si_custom_theme_model->get_themes($theme_id);
			if($theme){
				$data['theme_name'] = $this->input->post('theme_name');
				$data['class'] = implode('|',$this->input->post('color'));
				$success = $this->si_custom_theme_model->update($data, $theme_id);
			}
		}
		redirect(admin_url('si_custom_theme'));
	}
	
	public function validate()
	{
		if (!is_admin() && !has_permission('settings', '', 'view')) {
			ajax_access_denied();
		}
		try{
			$purchase_key   = trim($this->input->post('purchase_key', false));
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT      => 'curl',
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_URL            => SI_CUSTOM_THEME_VALIDATION_URL,
				CURLOPT_POST           => 1,
				CURLOPT_POSTFIELDS     => [
					'url' => site_url(),
					'module'     => SI_CUSTOM_THEME_KEY,
					'purchase_key'    => $purchase_key,
				],
			]);
			$result = curl_exec($curl);
			$error  = '';
			if (!$curl || !$result) {
				$error = 'Curl Error - Contact your hosting provider with the following error as reference: Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
			}
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if($code==404)
				$error = 'Server request unavailable, try after sometime.';
				
			curl_close($curl);
			if ($error != '') {
				echo json_encode([
					'success' => false,
					'message'=>$error,
				]);
				die();
			}
			echo ($result);
		}
		catch (Exception $e) {
			echo json_encode(array('success'=>false,'message'=>$e->getMessage()));
		}
	}
}