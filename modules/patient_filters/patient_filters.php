<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Patient Filters
Description: Module will Generate Filters for Patient and save filters as Templates for future use.
Author: Fahad Ahmed
Version: 1.2.0
Requires at least: 2.3.*
*/

define('PATIENT_FILTERS_MODULE_NAME', 'patient_filters');
define('PATIENT_FILTERS_MAX_CUSTOM_FIELDS', 10);

$CI = &get_instance();

hooks()->add_action('admin_init', 'patient_filters_init_menu_items');
hooks()->add_action('admin_init', 'patient_filters_permissions');

/**
* Load the module helper
*/
$CI->load->helper(PATIENT_FILTERS_MODULE_NAME . '/patient_filters');

/**
* Load the module Model
*/
$CI->load->model(PATIENT_FILTERS_MODULE_NAME . '/patient_filter_model');

/**
* Register activation module hook
*/
register_activation_hook(PATIENT_FILTERS_MODULE_NAME, 'patient_filters_activation_hook');

function patient_filters_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

/**
 * Register Uninstall module hook
 */
register_uninstall_hook(PATIENT_FILTERS_MODULE_NAME, 'patient_filters_uninstall_hook');

function patient_filters_uninstall_hook()
{
    $CI = &get_instance();
	//require_once(__DIR__ . '/uninstall.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(PATIENT_FILTERS_MODULE_NAME, [PATIENT_FILTERS_MODULE_NAME]);

/**
 * Init menu setup module menu items in setup in admin_init hook
 * @return null
 */
function patient_filters_init_menu_items()
{
	$CI = &get_instance();
	
	/**Sidebar menu */
	if (is_admin() || has_permission('patient_filters', '', 'view')) {

		//get Default Template for first time load
		$default_template_id = $CI->patient_filter_model->get_default_template(get_staff_user_id());

		$CI->app_menu->add_sidebar_menu_item('patient-filters', [
			'collapse'	=> true,
			'icon'		=> 'fa fa-filter',
			'name'		=> _l('patient_filters_menu'),
			'position'	=> 35,
		]);
		$CI->app_menu->add_sidebar_children_item('patient-filters', [
			'slug'		=> 'patient-filter-options',
			'name'		=> _l('patient_submenu_lead_filters'),
			'href'		=> admin_url('patient_filters/patient_filter' . ($default_template_id > 0 ? '?filter_id='.$default_template_id : "")),
			'position'	=> 1,
		]);
		$CI->app_menu->add_sidebar_children_item('patient-filters', [
			'slug'		=> 'patient-tmplate-options',
			'name'		=> _l('patient_submenu_filter_templates'),
			'href'		=> admin_url('patient_filters/list_filters'),
			'position'	=> 2,
		]);
		if(has_permission('patient_filters', '', 'view')){
			$CI->app_menu->add_sidebar_children_item('patient-filters', [
				'slug'		=> 'patient-settings-options',
				'name'		=> _l('settings'),
				'href'		=> admin_url('patient_filters/settings'),
				'position'	=> 3,
			]);
		}
	}
}
function patient_filters_permissions()
{
	$capabilities = [];
	$capabilities['capabilities'] = [
		'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'settings' => _l('settings'),
	];
	register_staff_capabilities('patient_filters', $capabilities, _l('patient_filters'));
}
