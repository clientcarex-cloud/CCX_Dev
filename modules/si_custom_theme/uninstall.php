<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_custom_theme_list')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_custom_theme_list");
}
if($CI->db->table_exists(db_prefix() . 'si_custom_theme_staff')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_custom_theme_staff");
}
if($CI->db->table_exists(db_prefix() . 'si_custom_theme_client')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_custom_theme_client");
}
//settings
delete_option('si_custom_theme_activated');
delete_option('si_custom_theme_activation_code');
delete_option('si_custom_theme_customer_login_header'); 
delete_option('si_custom_theme_customer_login_footer');
delete_option('si_custom_theme_enable_staff_theme');
delete_option('si_custom_theme_enable_client_theme');
delete_option('si_custom_theme_default_clients_theme');
delete_option('si_custom_theme_bg_img_customer_pages');
delete_option('si_custom_theme_bg_img_admin_pages');
delete_option('si_custom_theme_bg_img_admin_menu');
delete_option('si_custom_theme_bg_img_customer_login');
delete_option('si_custom_theme_bg_img_admin_login');
delete_option('si_custom_theme_default_theme');
delete_option('si_custom_theme_custom_clients_and_admin_area');
delete_option('si_custom_theme_custom_clients_area');
delete_option('si_custom_theme_custom_admin_area');
delete_option('si_custom_theme_style');
