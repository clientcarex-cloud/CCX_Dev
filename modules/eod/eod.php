<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: EOD Module
Description: EOD Module For HR
Version: 1.0.0
*/

define('EOD_MODULE_NAME', 'eod');

hooks()->add_action('admin_init', EOD_MODULE_NAME.'_init_menu_items');

function eod_init_menu_items()
{
    $CI = &get_instance();
	if (staff_can('create', 'eod')) {
    $CI->app_menu->add_sidebar_menu_item('eod', [
        'name'     => _l('eod'),
        'icon'     => 'fa fa-calendar',
        //'href'     => admin_url('eod'),
        'position' => 10,
    ]);
	}
    
    $CI->app_menu->add_sidebar_children_item('eod', [
    'slug'     => 'my_eod',
    'name'     => _l('my_eod'),
    'href'     => admin_url('eod/my_eod'),
    'position' => 1,
	]);
	
    $CI->app_menu->add_sidebar_children_item('eod', [
    'slug'     => 'all_eod',
    'name'     => _l('all_eod'),
    'href'     => admin_url('eod/all_eod'),
    'position' => 1,
	]);
	
 
}

hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName = _l('permission_view') . ' (' . _l('permission_global') . ')';
    $allPermissionsArray = [
        'view' => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $permissions['eod'] = [
        'name' => _l('eod'),
        'capabilities' => [
			'view_own' => _l('permission_view_own'),
			'view_all'     => $viewGlobalName,
			'create'   => _l('permission_create'),
			'edit'     => _l('permission_edit'),
        ]
    ];

    return $permissions;
});

register_activation_hook(EOD_MODULE_NAME, 'eod_module_activation_hook');

function eod_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    eod_install();
}

register_deactivation_hook(EOD_MODULE_NAME, 'eod_module_uninstall_hook');

function eod_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    eod_uninstall();
}

register_language_files(EOD_MODULE_NAME, [EOD_MODULE_NAME]);



