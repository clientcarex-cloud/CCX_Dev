<?php

defined('BASEPATH') or exit('No direct script access allowed');

log_message('debug', '✅ custom_tabs_helper.php loaded');

hooks()->add_action('after_lead_lead_tabs', 'client_module_add_custom_lead_tab');
hooks()->add_action('lead_profile_tab_content', 'client_module_add_custom_lead_tab_content');

function client_module_add_custom_lead_tab($lead)
{
    log_message('debug', '✅ client_module_add_custom_lead_tab executed');
    echo view('client/lead_custom_tab_button', ['lead' => $lead]);
}

function client_module_add_custom_lead_tab_content($lead)
{
    log_message('debug', '✅ client_module_add_custom_lead_tab_content executed');
    echo view('client/lead_custom_tab_content', ['lead' => $lead]);
}
