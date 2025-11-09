<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Lead Rollercoaster
Description: Lead Rollercoaster
Version: 1.0.0
*/

define('LEAD_ROLLERCOASTER_NAME', 'lead_rollercoaster');


register_language_files(LEAD_ROLLERCOASTER_NAME, [LEAD_ROLLERCOASTER_NAME]);


$CI = &get_instance();

// Register module activation hook
register_activation_hook(LEAD_ROLLERCOASTER_NAME, 'lead_rollercoaster_module_activate');
register_deactivation_hook(LEAD_ROLLERCOASTER_NAME, 'lead_rollercoaster_module_deactivate');

hooks()->add_action('admin_init', LEAD_ROLLERCOASTER_NAME.'_init_menu_items');

function lead_rollercoaster_init_menu_items()
{
    $CI = &get_instance();
	if (staff_can('lead_rollercoaster', 'rollercoaster')) {
		$CI->app_menu->add_setup_menu_item('rollercoaster', [
			'slug'     => 'lead_rollercoaster',
			'name'     => _l('lead_rollercoaster'),
			'href'     => admin_url('lead_rollercoaster/settings'),
			'position' => 80,
			'badge'    => [],
		]);
	}
	
}

function lead_rollercoaster_module_activate()
{
	$CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    lead_rollercoaster_install();
}

function lead_rollercoaster_module_deactivate()
{
	$CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    lead_rollercoaster_uninstall();
    
}


