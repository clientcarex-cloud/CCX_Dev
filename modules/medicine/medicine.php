<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Medicine
Description: Medicine Sample Module
Version: 1.0.0
*/

define('MEDICINE_MODULE_NAME', 'medicine');

hooks()->add_action('admin_init', MEDICINE_MODULE_NAME.'_init_menu_items');

function medicine_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('medicine', [
        'name'     => _l('medicine'),
        'icon'     => 'fa fa-plus',
        //'href'     => admin_url('medicine'),
        'position' => 10,
    ]);
    
    $CI->app_menu->add_sidebar_children_item('medicine', [
        'slug'     => 'medicine',
         'name'     => _l('Medicine List'),
        'href'     => admin_url('medicine'),
        'position' => 1,
    ]); 
    
    $CI->app_menu->add_sidebar_children_item('medicine', [
        'slug'     => 'medicine',
         'name'     => _l('Add Medicine'),
        'href'     => admin_url('medicine'),
        'position' => 2,
    ]); 
}

register_activation_hook(MEDICINE_MODULE_NAME, 'medicine_module_activation_hook');

function medicine_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    medicine_install();
}

register_deactivation_hook(MEDICINE_MODULE_NAME, 'medicine_module_uninstall_hook');

function medicine_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    medicine_uninstall();
}

register_language_files(MEDICINE_MODULE_NAME, [MEDICINE_MODULE_NAME]);


hooks()->add_action('app_init', function () {
    hooks()->add_filter('customer_profile_tabs', 'add_medicine_custom_tabs');
});

/**
 * Function to add custom tabs in client profile
 */
function add_medicine_custom_tabs($tabs) {
    $tabs[] = [
        'slug'     => 'prescription',
        'name'     => 'Prescription',
        'url'      => admin_url('medicine/custom_tab1'),
        'icon'     => 'fa fa-file-medical', // Medical document icon
        'position' => 11
    ];

    $tabs[] = [
        'slug'     => 'case_sheet',
        'name'     => 'Case Sheet',
        'url'      => admin_url('medicine/custom_tab2'),
        'icon'     => 'fa fa-notes-medical', // Medical notes icon
        'position' => 10
    ];
    
    $tabs[] = [
        'slug'     => 'review_visits',
        'name'     => 'Review Visits',
        'url'      => admin_url('medicine/custom_tab2'),
        'icon'     => 'fa fa-calendar-check', // Appointment review icon
        'position' => 12
    ];

    return $tabs;
}

