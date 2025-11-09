<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Registering the hooks
hooks()->add_action('after_lead_lead_tabs', 'client_module_add_custom_lead_tab');
hooks()->add_action('lead_profile_tab_content', 'client_module_add_custom_lead_tab_content');

// Custom hook functions
function client_module_add_custom_lead_tab($lead)
{
    // Render your custom tab button (can also pass lead data if needed)
    echo view('client/lead_custom_tab_button', ['lead' => $lead]);
}

function client_module_add_custom_lead_tab_content($lead)
{
    // Render custom content for the new tab
    echo view('client/lead_custom_tab_content', ['lead' => $lead]);
}
