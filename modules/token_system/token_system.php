<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Token System
Description: Token System Module
Version: 1.0.0
*/

define('TOKEN_SYSTEM_MODULE_NAME', 'token_system');

hooks()->add_action('admin_init', TOKEN_SYSTEM_MODULE_NAME.'_init_menu_items');

function token_system_init_menu_items()
{
    $CI = &get_instance();
	if (staff_can('create', 'token_system')) {
    $CI->app_menu->add_sidebar_menu_item('token_system', [
        'name'     => _l('token_system'),
        'icon'     => 'fa fa-ticket',
        //'href'     => admin_url('token_system'),
        'position' => 10,
    ]);
	}
    
    $CI->app_menu->add_sidebar_children_item('token_system', [
    'slug'     => 'display',
    'name'     => _l('Display'),
    'href'     => admin_url('token_system/display'),
    'position' => 1,
	]);
	
    $CI->app_menu->add_sidebar_children_item('token_system', [
    'slug'     => 'counter',
    'name'     => _l('Counter'),
    'href'     => admin_url('token_system/counters'),
    'position' => 1,
	]);
	
    $CI->app_menu->add_sidebar_children_item('token_system', [
    'slug'     => 'tokens',
    'name'     => _l('tokens'),
    'href'     => admin_url('token_system/tokens'),
    'position' => 1,
	]);
	
    $CI->app_menu->add_sidebar_children_item('token_system', [
    'slug'     => 'call',
    'name'     => _l('Call'),
    'href'     => admin_url('token_system/call'),
    'position' => 1,
	]);
	
    /* $CI->app_menu->add_sidebar_children_item('token_system', [
    'slug'     => 'smart_queue',
    'name'     => _l('smart_queue'),
    'href'     => admin_url('token_system/smart_queue'),
    'position' => 1,
	]); */

	

}

hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName = _l('permission_view') . ' (' . _l('permission_global') . ')';
    $allPermissionsArray = [
        'view' => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    // For customers, this variable should be defined as it is used in array_merge
    $withNotApplicableViewOwn = [
        'view_own' => _l('permission_view_own'),
        'view'     => $viewGlobalName,
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    $permissions['token_system'] = [
        'name' => _l('token_system'),
        'capabilities' => array_merge($allPermissionsArray, [
            'create_display' => _l('create_display'),
            'view_display' => _l('view_display'),
            'edit_display' => _l('edit_display'),
            'delete_display' => _l('delete_display'),
            'create_counter' => _l('create_counter'),
            'view_counter' => _l('view_counter'),
            'edit_counter' => _l('edit_counter'),
            'delete_counter' => _l('delete_counter'),
            'create_token' => _l('create_token'),
            'view_token' => _l('view_token'),
            'edit_token' => _l('edit_token'),
            'delete_token' => _l('delete_token'),
            'create_call' => _l('create_call'),
            'view_call' => _l('view_call'),
            'edit_call' => _l('edit_call'),
            'delete_call' => _l('delete_call'),
        ]),
    ];

    return $permissions;
});

register_activation_hook(TOKEN_SYSTEM_MODULE_NAME, 'token_system_module_activation_hook');

function token_system_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    token_system_install();
}

register_deactivation_hook(TOKEN_SYSTEM_MODULE_NAME, 'token_system_module_uninstall_hook');

function token_system_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    token_system_uninstall();
}

register_language_files(TOKEN_SYSTEM_MODULE_NAME, [TOKEN_SYSTEM_MODULE_NAME]);



