<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Lead Call Log
Description: Adds Call Log Tab in Lead Profile
Version: 1.0.0
*/

define('LEAD_CALL_LOG_MODULE_NAME', 'lead_call_log');

register_activation_hook(LEAD_CALL_LOG_MODULE_NAME, 'lead_call_log_module_activation_hook');
function lead_call_log_module_activation_hook() {
    require_once(__DIR__ . '/install.php');
    lead_call_log_install();
}

register_deactivation_hook(LEAD_CALL_LOG_MODULE_NAME, 'lead_call_log_module_deactivation_hook');
function lead_call_log_module_deactivation_hook() {
    require_once(__DIR__ . '/install.php');
    lead_call_log_uninstall();
}

register_language_files(LEAD_CALL_LOG_MODULE_NAME, [LEAD_CALL_LOG_MODULE_NAME]);

hooks()->add_action('after_lead_lead_tabs', 'call_log_add_custom_lead_tab');
hooks()->add_action('after_lead_lead_tabs', 'appointment_add_custom_lead_tab');
hooks()->add_action('after_lead_lead_tabs', 'message_log_add_custom_lead_tab');
hooks()->add_action('after_lead_lead_tabs', 'feedback_add_custom_lead_tab');
hooks()->add_action('after_lead_tabs_content', 'call_log_add_custom_lead_tab_content');
hooks()->add_action('after_lead_tabs_content', 'appointment_add_custom_lead_tab_content');
hooks()->add_action('after_lead_tabs_content', 'message_log_add_custom_lead_tab_content');
hooks()->add_action('after_lead_tabs_content', 'feedback_add_custom_lead_tab_content');

// Custom hook functions
function call_log_add_custom_lead_tab($lead)
{
    echo '<li role="presentation">
            <a href="#call_log" aria-controls="call_log" role="tab" data-toggle="tab">
                <i class="fa-solid fa-phone menu-icon"></i> '._l('call_log').'
            </a>
        </li>';
}

// Custom hook functions
function appointment_add_custom_lead_tab($lead)
{
    echo '<li role="presentation">
            <a href="#appointment" aria-controls="appointment" role="tab" data-toggle="tab">
                <i class="fa-solid fa-calendar menu-icon"></i> '._l('appointment').'
            </a>
        </li>';
}
// Custom hook functions
function message_log_add_custom_lead_tab($lead)
{
    echo '<li role="presentation">
            <a href="#message_log" aria-controls="message_log" role="tab" data-toggle="tab">
                <i class="fa-solid fa-envelope menu-icon"></i> '._l('message_log').'
            </a>
        </li>';
}

// Custom hook functions
function feedback_add_custom_lead_tab($lead)
{
    echo '<li role="presentation">
            <a href="#feedback" aria-controls="feedback" role="tab" data-toggle="tab">
                <i class="fa-solid fa-comments menu-icon"></i> '._l('feedback').'
            </a>
        </li>';
}

function call_log_add_custom_lead_tab_content($lead)
{
	if($lead){
		$id = $lead->id;
	}else{
		$id = "";
	}
	$data['title'] = "Call Log";
	$CI = &get_instance();
	$CI->load->model('lead_call_log/lead_call_log_model');
	$CI->db->select("l.*, branch.name as branch_name, res.patient_response_name");
	$CI->db->from(db_prefix() . 'lead_call_logs as l');
	$CI->db->join(db_prefix() . 'customers_groups as branch', "branch.id=l.branch_id", "LEFT");
	$CI->db->join(db_prefix() . 'patient_response as res', "res.patient_response_id=l.patient_response_id", "LEFT");
	$CI->db->where(array("leads_id"=>$id));
	$data['lead_call_log'] =  $CI->db->get()->result_array();
	$data['branches'] = $CI->lead_call_log_model->get_branches();
	$data['treatments'] = $CI->lead_call_log_model->get_treatments();
	$data['doctors'] = $CI->lead_call_log_model->get_doctors();
	$data['patient_response'] = $CI->lead_call_log_model->get_patient_responses();
	$CI->load->model('invoice_items_model');
		
	$data['items_groups'] = $CI->invoice_items_model->get_groups();
	$data['items'] = $CI->invoice_items_model->get_grouped();
	$CI->load->model('payment_modes_model');
	$data['payment_modes'] = $CI->payment_modes_model->get('', [
		'expenses_only !=' => 1,
	]);
	//$branches = $CI->lead_call_log_model->get_branches();
	//$treatments =  $CI->lead_call_log_model->get_treatments();
	//$doctors =  $CI->lead_call_log_model->get_doctors();
	$data['patient_response_setting']  = $CI->lead_call_log_model->get_patient_response_setting();
	echo $CI->load->view('lead_call_log/tab_content', $data, true);
}


function appointment_add_custom_lead_tab_content($lead)
{
	if($lead){
		$id = $lead->id;
	}else{
		$id = "";
	}
	$data['title'] = "Appointment";
	$CI = &get_instance();
	$CI->load->model('lead_call_log/lead_call_log_model');
	$CI->db->select("l.*, branch.name as branch_name, res.patient_response_name");
	$CI->db->from(db_prefix() . 'lead_call_logs as l');
	$CI->db->join(db_prefix() . 'customers_groups as branch', "branch.id=l.branch_id", "LEFT");
	$CI->db->join(db_prefix() . 'patient_response as res', "res.patient_response_id=l.patient_response_id", "LEFT");
	$CI->db->where(array("leads_id"=>$id));
	$data['lead_call_log'] =  $CI->db->get()->result_array();
	$data['branches'] = $CI->lead_call_log_model->get_branches();
	$data['treatments'] = $CI->lead_call_log_model->get_treatments();
	$data['doctors'] = $CI->lead_call_log_model->get_doctors();
	$data['patient_response'] = $CI->lead_call_log_model->get_patient_responses();
	//$branches = $CI->lead_call_log_model->get_branches();
	//$treatments =  $CI->lead_call_log_model->get_treatments();
	//$doctors =  $CI->lead_call_log_model->get_doctors();
	$data['patient_response_setting']  = $CI->lead_call_log_model->get_patient_response_setting();
	echo $CI->load->view('lead_call_log/tab_appointment', $data, true);
}


function message_log_add_custom_lead_tab_content($lead)
{
	if($lead){
		$id = $lead->id;
	}else{
		$id = "";
	}
	$data['title'] = "Message Log";
	$CI = &get_instance();
	echo $CI->load->view('lead_call_log/tab_message_log', $data, true);
}


function feedback_add_custom_lead_tab_content($lead)
{
	if($lead){
		$id = $lead->id;
	}else{
		$id = "";
	}
	$data['title'] = "Feedback";
	$CI = &get_instance();
	echo $CI->load->view('lead_call_log/tab_feedback', $data, true);
}


