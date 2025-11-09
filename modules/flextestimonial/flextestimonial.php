<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Flexible Testimonial
Description: Testimonial Module allows you to gather testimonials from your Guests, Customers, Clients and display them on your website as Social Proof
Version: 1.0.1
*/


define('FLEXTESTIMONIAL_MODULE_NAME', 'flextestimonial');
register_merge_fields("flextestimonial/merge_fields/flextestimonial_submission_notification_merge_fields");
register_merge_fields("flextestimonial/merge_fields/flextestimonial_thank_you_merge_fields");
register_merge_fields("flextestimonial/merge_fields/flextestimonial_testimonial_request_tickets_merge_fields");
register_merge_fields("flextestimonial/merge_fields/flextestimonial_testimonial_request_projects_merge_fields");
register_merge_fields("flextestimonial/merge_fields/flextestimonial_testimonial_request_invoices_merge_fields");
define('FLEXTESTIMONIAL_FOLDER', FCPATH . 'uploads/flextestimonial' . '/');

hooks()->add_action('admin_init', FLEXTESTIMONIAL_MODULE_NAME . '_module_init_menu_items');
hooks()->add_action('admin_init', FLEXTESTIMONIAL_MODULE_NAME . '_permissions');
//automat
hooks()->add_action('project_status_changed', FLEXTESTIMONIAL_MODULE_NAME . '_project_status_changed');
hooks()->add_action('after_ticket_status_changed', FLEXTESTIMONIAL_MODULE_NAME . '_ticket_status_changed');
hooks()->add_action('invoice_status_changed', FLEXTESTIMONIAL_MODULE_NAME . '_invoice_status_changed');

register_activation_hook(FLEXTESTIMONIAL_MODULE_NAME, FLEXTESTIMONIAL_MODULE_NAME . '_module_activation_hook');

function flextestimonial_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FLEXTESTIMONIAL_MODULE_NAME, [FLEXTESTIMONIAL_MODULE_NAME]);
function flextestimonial_module_init_menu_items(){
    $CI = &get_instance();
    if (has_permission('flextestimonial', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item(FLEXTESTIMONIAL_MODULE_NAME, [
            'name' => _flextestimonial_lang('testimonials'),
            'icon' => 'fa fa-comments',
            'href' => admin_url('flextestimonial'),
            'position' => 25,
        ]);
    }
}

function flextestimonial_permissions(){
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('flextestimonial', $capabilities, _l('flextestimonial_module_name'));
}


function _flextestimonial_lang($key){
   return  _l(FLEXTESTIMONIAL_MODULE_NAME . '_' . $key);
}

function flextestimonial_create_storage_directory(){
    if (!is_dir(FLEXTESTIMONIAL_FOLDER)) {
        mkdir(FLEXTESTIMONIAL_FOLDER, 0755);
        $fp = fopen(rtrim(FLEXTESTIMONIAL_FOLDER, '/') . '/' . 'index.html', 'w');
        fclose($fp);
    }
}
//Automate
function flextestimonial_project_status_changed($data){
    if($data['status'] == 4){
        $CI = &get_instance();
        $testimonial_id  = get_option('flextestimonial_for_projects') ? get_option('flextestimonial_for_projects') : 0;
        if(!$testimonial_id){
            return;
        }
        //load the testimonial model
        $CI->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $CI->flextestimonial_model->get(['id' => $testimonial_id]);
        if(!$testimonial){
            return;
        }
        $testimonial = $testimonial[0];
        $CI->load->library(FLEXTESTIMONIAL_MODULE_NAME.'/Flextestimonial_module');
        $project_id = $data['project_id'];
        $CI->load->model('projects_model');
        $CI->load->model('clients_model');
        $project = $CI->projects_model->get($project_id);
        $contacts = $CI->clients_model->get_contacts_for_project_notifications($project_id, 'project_emails');
        foreach($contacts as $contact){
            $email = $contact['email'];
            $CI->flextestimonial_module->send_testimonial_request_email_projects($email, $testimonial,$project);
            //log activity
            log_activity('Sent testimonial request email to ' . $email . ' for Project ' . $project->name);
        }
    }
}

function flextestimonial_ticket_status_changed($data){
    if($data['status'] == 5){
        $CI = &get_instance();
        $testimonial_id  = get_option('flextestimonial_for_tickets') ? get_option('flextestimonial_for_tickets') : 0;
        if(!$testimonial_id){
            return;
        }
        $CI->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $CI->flextestimonial_model->get(['id' => $testimonial_id]);
        if(!$testimonial){
            return;
        }
        $testimonial = $testimonial[0];
        $CI->load->library(FLEXTESTIMONIAL_MODULE_NAME.'/Flextestimonial_module');
        $ticket_id = $data['id'];
        $CI->load->model('tickets_model');
        $ticket = $CI->tickets_model->get($ticket_id);
        if ($ticket->userid != 0 && $ticket->contactid != 0) {
            $email     = $CI->clients_model->get_contact($ticket->contactid)->email;
        } else {
            $email = $ticket->email;
        }
        $CI->flextestimonial_module->send_testimonial_request_email_tickets($email, $testimonial,$ticket);
        //log activity
        log_activity('Sent testimonial request email to ' . $email . ' for Ticket ' . $ticket->subject);
    }
}

function flextestimonial_invoice_status_changed($data){
    if($data['status'] == Invoices_model::STATUS_PAID){
        $CI = &get_instance();  
        $testimonial_id  = get_option('flextestimonial_for_invoices') ? get_option('flextestimonial_for_invoices') : 0;
        if(!$testimonial_id){
            return;
        }
        $CI->load->model('flextestimonial/flextestimonial_model');
        $testimonial = $CI->flextestimonial_model->get(['id' => $testimonial_id]);
        if(!$testimonial){
            return;
        }
        $testimonial = $testimonial[0];
        $CI->load->library(FLEXTESTIMONIAL_MODULE_NAME.'/Flextestimonial_module');
        $invoice_id = $data['invoice_id'];
        $CI->load->model('invoices_model');
        $invoice = $CI->invoices_model->get($invoice_id);
        $contacts =  $CI->clients_model->get_contacts($invoice->clientid, [
            'active' => 1, 'invoice_emails' => 1,
        ]);
        foreach($contacts as $contact){ 
            $email = $contact['email'];
            $CI->flextestimonial_module->send_testimonial_request_email_invoices($email, $testimonial,$invoice);
            //log activity
            log_activity('Sent testimonial request email to ' . $email . ' for invoice ' . format_invoice_number($invoice->id));
        }
    }
}
function _flextestimonial_darken_color($hex, $percent) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Darken
    $r = round($r * (100 - $percent) / 100);
    $g = round($g * (100 - $percent) / 100);
    $b = round($b * (100 - $percent) / 100);
    
    // Convert back to hex
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

function flextestimonial_get_testimonial_side_panel_items(){
   return [
        'general' => [
            'label' => _flextestimonial_lang('general'),
            'icon' => 'fa-cog',
            'key' => 'general',
        ],
        'design' => [
            'label' => _flextestimonial_lang('design'),
            'icon' => 'fa-brush',
            'key' => 'design',
        ],
        'welcome_page' => [
            'label' => _flextestimonial_lang('welcome_page'),
            'icon' => 'fa-door-open',
            'key' => 'welcome_page',
        ],
        'response_prompt' => [
            'label' => _flextestimonial_lang('response_prompt'),
            'icon' => 'fa-comment',
            'key' => 'response_prompt',
        ],
        'customer_details' => [
            'label' => _flextestimonial_lang('customer_details'),
            'icon' => 'fa-user',
            'key' => 'customer_details',
        ],
        'thankyou_page' => [
            'label' => _flextestimonial_lang('thankyou_page'),
            'icon' => 'fa-check-circle',
            'key' => 'thankyou_page',
        ],
        'word_of_mouth' => [
            'label' => _flextestimonial_lang('word_of_mouth'),
            'icon' => 'fa-share-alt',
            'key' => 'word_of_mouth',
        ],
        'custom_labels' => [
            'label' => _flextestimonial_lang('custom_labels'),
            'icon' => 'fa-pencil',
            'key' => 'custom_labels',
        ],
    ];
}

function flextestimonial_display_url($slug){
    return site_url('flextestimonial/vt/' . $slug);
}

function flextestimonial_response_display_url($slug){
    return site_url('flextestimonial/r/' . $slug);
}

function flextestimonial_perfect_serialize($string)
{
    return base64_encode(serialize($string));
}

function flextestimonial_perfect_unserialize($string)
{
    if (base64_decode($string, true) == true) {
        return @unserialize(base64_decode($string));
    } else {
        return @unserialize($string);
    }
}

function flextestimonial_media_url($url){
    //if the url is not a full url, add the base url
    if(strpos($url, 'http') === false){
        $url = site_url('uploads/flextestimonial/' . $url);
    }
    return $url;
}

function flextestimonial_media_path($path){
    return FLEXTESTIMONIAL_FOLDER . $path;
}
