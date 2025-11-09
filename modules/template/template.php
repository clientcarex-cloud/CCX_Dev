<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: SMS Spark
Description: Start sending bulk SMS and API SMS Integration
Version: 2.3.0
Author: IncraSoft Private Limited
Author URI: https://incrasoft.com
Requires at least: 2.3.0
*/


define('TEMPLATE_MODULE_NAME', 'template');

/**
 * Database backups folder
 */
define('TEMPLATE_FOLDER', FCPATH . 'backups' . '/');



hooks()->add_action('admin_init', 'template_register_permissions');
hooks()->add_action('admin_init', 'template_module_init_menu_items');


/**
* Register activation module hook
*/
register_activation_hook(TEMPLATE_MODULE_NAME, 'template_module_activation_hook');

function template_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

$CI = &get_instance();
    require_once(__DIR__ . '/install.php');

/**
 * Init backup module menu items in setup in admin_init hook
 * @return null
 */
function template_module_init_menu_items()
{
    /**
    * If the logged in user is administrator, add custom menu in Setup
    */
        	$CI = &get_instance(); 
        	if (staff_can('view_own', 'template') || staff_can('view', 'template')) {
        $CI->app_menu->add_sidebar_menu_item('TEMPLATE', [
                'name'     =>'SMS Spark',
                'icon'     => 'fa-sharp fa-solid fa-comment-sms',
                'position' => 10,
            ]);

        $CI->app_menu->add_sidebar_children_item('TEMPLATE', [
				'slug'     => 'sms',
                'name'     => 'Open SMS',
                'href'     => admin_url().'template/sms/index',
               'position' => 1,
      ]);
      $CI->app_menu->add_sidebar_children_item('TEMPLATE', [
                'slug'     => 'smslogs',
                'name'     => 'SMS Logs',
                'href'     => admin_url().'template/smslogs/index',
                'position' => 3,
        ]); 
        	}
        if(staff_can('dlt', 'template') || staff_can('view', 'template')){
        /* $CI->app_menu->add_sidebar_children_item('TEMPLATE', [
                'slug'     => 'templates',
                'name'     => 'DLT Templates',
                'href'     => admin_url().'template/templates/index',
                'position' => 2,
        ]); */
}
        /* 	
        if(staff_can('view', 'template') || staff_can('view', 'template')){
        $CI->app_menu->add_sidebar_children_item('TEMPLATE', [
                'slug'     => 'balance',
                'name'     => 'Balance',
                'href'     => admin_url().'template/balance/index',
                'position' => 4,
        ]);
        
       
    } */
}

function template_register_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'     => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        //'dlt' => _l('DLT Templates'),
    ];

    register_staff_capabilities('template', $capabilities, _l('SMS Spark'));
}
