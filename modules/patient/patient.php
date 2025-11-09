<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Patient
Description: Patient Sample Module
Version: 1.0.0
*/

define('PATIENT_MODULE_NAME', 'patient');

hooks()->add_action('admin_init', PATIENT_MODULE_NAME.'_init_menu_items');

function patient_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('patient', [
        'name'     => _l('patient'),
        'icon'     => 'fa fa-user',
        'href'     => admin_url('patient'),
        'position' => 10,
    ]);
    
}

register_activation_hook(PATIENT_MODULE_NAME, 'patient_module_activation_hook');

function patient_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    patient_install();
}

register_deactivation_hook(PATIENT_MODULE_NAME, 'patient_module_uninstall_hook');

function patient_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    patient_uninstall();
}

register_language_files(PATIENT_MODULE_NAME, [PATIENT_MODULE_NAME]);


/* hooks()->add_action('app_init', function () {
    hooks()->add_filter('customer_profile_tabs', 'add_medicine_custom_tabs');
});


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
} */

