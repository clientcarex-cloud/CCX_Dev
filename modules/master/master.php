<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Master
Description: Master Sample Module
Version: 1.0.0
*/

define('MASTER_MODULE_NAME', 'master');

hooks()->add_action('admin_init', MASTER_MODULE_NAME.'_init_menu_items');

function master_init_menu_items()
{
    $CI = &get_instance();
	if (is_admin()) {
		$CI->app_menu->add_sidebar_menu_item('master', [
			'name'     => _l('master'),
			'icon'     => 'fa fa-plus',
			//'href'     => admin_url('master'),
			'position' => 10,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
		'slug'     => 'enquiry_type',
		'name'     => _l('Enquiry Type'),
		'href'     => admin_url('master/enquiry_type'),
		'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'patient_response',
			'name'     => _l('Patient Response'),
			'href'     => admin_url('master/patient_response'),
			'position' => 2,
		]);

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'speaking_language',
			'name'     => _l('Speaking Language'),
			'href'     => admin_url('master/speaking_language'),
			'position' => 3,
		]); */

		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'patient_priority',
			'name'     => _l('Patient Priority'),
			'href'     => admin_url('master/patient_priority'),
			'position' => 4,
		]);

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'branch',
			'name'     => _l('Branch'),
			'href'     => admin_url('master/branch'),
			'position' => 5,
		]); */

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'assign_doctor',
			'name'     => _l('Assign Doctor'),
			'href'     => admin_url('master/assign_doctor'),
			'position' => 6,
		]); */

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'slots',
			'name'     => _l('Slots'),
			'href'     => admin_url('master/slots'),
			'position' => 7,
		]); */

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'treatment',
			'name'     => _l('Treatment'),
			'href'     => admin_url('master/treatment'),
			'position' => 8,
		]); */

		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'consultation_fee',
			'name'     => _l('Consultation Fee'),
			'href'     => admin_url('master/consultation_fee'),
			'position' => 9,
		]); */

		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'patient_source',
			'name'     => _l('Patient Source'),
			'href'     => admin_url('master/patient_source'),
			'position' => 10,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medicine',
			'name'     => _l('medicine'),
			'href'     => admin_url('master/medicine'),
			'position' => 2,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medicine_potency',
			'name'     => _l('medicine_potency'),
			'href'     => admin_url('master/medicine_potency'),
			'position' => 3,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medicine_dose',
			'name'     => _l('medicine_dose'),
			'href'     => admin_url('master/medicine_dose'),
			'position' => 4,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medicine_timing',
			'name'     => _l('medicine_timing'),
			'href'     => admin_url('master/medicine_timing'),
			'position' => 5,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'patient_status',
			'name'     => _l('patient_status'),
			'href'     => admin_url('master/patient_status'),
			'position' => 10,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'appointment_type',
			'name'     => _l('appointment_type'),
			'href'     => admin_url('master/appointment_type'),
			'position' => 11,
		]); 
		
		/* $CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'call_type',
			'name'     => _l('call_type'),
			'href'     => admin_url('master/call_type'),
			'position' => 11,
		]); */ 
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'criteria',
			'name'     => _l('call_type'),
			'href'     => admin_url('master/criteria'),
			'position' => 12,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'specialization',
			'name'     => _l('specialization'),
			'href'     => admin_url('master/specialization'),
			'position' => 12,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'shift',
			'name'     => _l('shift'),
			'href'     => admin_url('master/shift'),
			'position' => 12,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'state',
			'name'     => _l('state'),
			'href'     => admin_url('master/state'),
			'position' => 12,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'city',
			'name'     => _l('city'),
			'href'     => admin_url('master/city'),
			'position' => 12,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'pincode',
			'name'     => _l('pincode'),
			'href'     => admin_url('master/pincode'),
			'position' => 12,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'master_settings',
			'name'     => _l('settings'),
			'href'     => admin_url('master/master_settings'),
			'position' =>13,
		]);
		
		//TOOT
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'chief_complaint',
			'name'     => _l('chief_complaint'),
			'href'     => admin_url('master/chief_complaint'),
			'position' =>13,
		]);
		
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medical_problem',
			'name'     => _l('medical_problem'),
			'href'     => admin_url('master/medical_problem'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'medical_investigation',
			'name'     => _l('medical_investigation'),
			'href'     => admin_url('master/medical_investigation'),
			'position' =>13,
		]);
		
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'dental_investigation',
			'name'     => _l('dental_investigation'),
			'href'     => admin_url('master/dental_investigation'),
			'position' =>13,
		]);
		
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'treatment_type',
			'name'     => _l('treatment_type'),
			'href'     => admin_url('master/treatment_type'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'treatment_sub_type',
			'name'     => _l('treatment_sub_type'),
			'href'     => admin_url('master/treatment_sub_type'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'treatment_procedure',
			'name'     => _l('treatment_procedure'),
			'href'     => admin_url('master/treatment_procedure'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'lab',
			'name'     => _l('lab'),
			'href'     => admin_url('master/lab'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'lab_work',
			'name'     => _l('lab_work'),
			'href'     => admin_url('master/lab_work'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'lab_followup',
			'name'     => _l('lab_followup'),
			'href'     => admin_url('master/lab_followup'),
			'position' =>13,
		]);
		
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'suggested_diagnostics',
			'name'     => _l('suggested_diagnostics'),
			'href'     => admin_url('master/suggested_diagnostics'),
			'position' =>13,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'case_remark',
			'name'     => _l('case_remark'),
			'href'     => admin_url('master/case_remark'),
			'position' =>13,
		]);
		$CI->app_menu->add_sidebar_children_item('master', [
			'slug'     => 'languages',
			'name'     => _l('languages'),
			'href'     => admin_url('master/languages'),
			'position' =>13,
		]);
		
	}

}

register_activation_hook(MASTER_MODULE_NAME, 'master_module_activation_hook');

function master_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    master_install();
}

register_deactivation_hook(MASTER_MODULE_NAME, 'master_module_uninstall_hook');

function master_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    master_uninstall();
}

register_language_files(MASTER_MODULE_NAME, [MASTER_MODULE_NAME]);



