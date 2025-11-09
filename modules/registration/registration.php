<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Registration
Description: Registration Module
Version: 1.0.0
*/

 define('REGISTRATION_MODULE_NAME', 'registration');

hooks()->add_action('admin_init', REGISTRATION_MODULE_NAME.'_init_menu_items');

function registration_init_menu_items()
{
   $CI = &get_instance();
    /*  $CI->app_menu->add_sidebar_menu_item('registration', [
        'name'     => _l('registration'),
        'icon'     => 'fa fa-plus',
        'href'     => admin_url('registration'),
        'position' => 10,
    ]); */
    
    

}

register_activation_hook(REGISTRATION_MODULE_NAME, 'registration_module_activation_hook');

function registration_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    registration_install();
}

register_deactivation_hook(REGISTRATION_MODULE_NAME, 'registration_module_uninstall_hook');

function registration_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    registration_uninstall();
}

register_language_files(REGISTRATION_MODULE_NAME, [REGISTRATION_MODULE_NAME]);



