<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Attendance
Description: API module for recording and updating attendance entries from punch machines into the task system.
Version: 1.0.0
*/

define('ATTENDANCE_MODULE_NAME', 'attendance');

hooks()->add_action('admin_init', ATTENDANCE_MODULE_NAME.'_init_menu_items');

function attendance_init_menu_items()
{
    $CI = &get_instance();
	if (is_admin()) {
     $CI->app_menu->add_setup_menu_item('attendance', [
            'collapse' => true,
            'name'     => _l('attendance'),
            'position' => 25,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('attendance', [
            'slug'     => 'Staff Attendance',
            'name'     => _l('staff_attendance'),
            'href'     => base_url('attendance/attendance_staff'),
            'position' => 1,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('attendance', [
            'slug'     => 'attendance_auth_token',
            'name'     => _l('auth_token'),
            'href'     => base_url('attendance/auth_token'),
            'position' => 1,
            'badge'    => [],
        ]);
	}
  
} 

register_activation_hook(ATTENDANCE_MODULE_NAME, 'attendance_module_activation_hook');

function attendance_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    attendance_install();
}

register_deactivation_hook(ATTENDANCE_MODULE_NAME, 'attendance_module_uninstall_hook');

function attendance_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    attendance_uninstall();
}

register_language_files(ATTENDANCE_MODULE_NAME, [ATTENDANCE_MODULE_NAME]);



