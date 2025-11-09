<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_111 extends App_module_migration
{
	public function up()
	{  
		$CI = &get_instance();

		$CI->db->query('UPDATE `' . db_prefix() . 'si_custom_theme_list` SET default_style=\'[{"id":"admin-menu","color":"#232731"},{"id":"admin-menu-submenu-open","color":"#2f333e"},{"id":"admin-menu-links","color":"#ffffff"},{"id":"user-welcome-bg-color","color":"#2d3446"},{"id":"admin-menu-active-item","color":"#2d3446"},{"id":"admin-menu-active-item-color","color":"#c0c6d7"},{"id":"admin-menu-active-subitem","color":"#6c6c6f"},{"id":"admin-menu-submenu-links","color":"#c0c6d7"},{"id":"top-header","color":"#232731"},{"id":"top-header-links","color":"#c0c6d7"},{"id":"customer-login-background","color":"#2d3446"},{"id":"customers-navigation","color":"#232731"},{"id":"customers-footer-background","color":"#6c6c6f"},{"id":"btn-default","color":"#2d3446"},{"id":"tabs-bg","color":"#2d3446"},{"id":"tabs-links","color":"#aba8ac"},{"id":"tabs-links-active-hover","color":"#1597e6"},{"id":"tabs-active-border","color":"#fbf6f6"},{"id":"modal-heading","color":"#232731"},{"id":"modal-heading-color","color":"#ffffff"},{"id":"modal-body","color":"#2d3446"},{"id":"text-muted","color":"#c2c2c2"},{"id":"text-dark","color":"#ffffff"},{"id":"alert-danger","color":"#b62626"},{"id":"alert-warning","color":"#a16207"},{"id":"alert-info","color":"#245cf8"},{"id":"alert-success","color":"#15803d"},{"id":"alert-danger-background","color":"#362525"},{"id":"alert-warning-background","color":"#383333"},{"id":"alert-info-background","color":"#08232e"},{"id":"alert-success-background","color":"#1e2c1e"},{"id":"admin-login-background","color":"#2d3446"},{"id":"admin-page-background","color":"#2d3446"},{"id":"admin-page-text-color","color":"#fbf4f7"},{"id":"admin-inputs-background","color":"#2d3446"},{"id":"admin-panel-background","color":"#232731"},{"id":"admin-panel-color","color":"#03a9f4"},{"id":"table-headings","color":"#c0c6d7"},{"id":"table-items-heading","color":"#2a2e39"}]\' where id=2'); 
		
		//set for theme_style also if not already set
		$CI->db->where('id',2);//for dark theme
		$result = $CI->db->get(db_prefix() . 'si_custom_theme_list')->row();
		if($result){
			$theme_style = json_decode($result->theme_style);

			//add new fields alert related 	
			$theme_style[] = array('id'=>'text-dark','color'=>'#ffffff');

			$CI->db->query('UPDATE `' . db_prefix() . 'si_custom_theme_list` SET theme_style=\''.json_encode($theme_style).'\' where id=2');

		}
	}
}