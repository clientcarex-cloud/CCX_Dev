<?php
defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('customers_navigation', 'add_patient_custom_tabs');

function add_patient_custom_tabs($customer_id)
{
    echo '<script>console.log("Hook Executed for Client ID: ' . $customer_id . '");</script>';
}


function add_patient_custom_tabs($customer_id)
{
echo '<li class="customer_tab_custom"><a href="' . admin_url('your_module/custom_tab1/' . $customer_id) . '">Custom Tab 1</a></li>';
echo '<li class="customer_tab_custom"><a href="' . admin_url('your_module/custom_tab2/' . $customer_id) . '">Custom Tab 2</a></li>';
echo '<li class="customer_tab_custom"><a href="' . admin_url('your_module/custom_tab3/' . $customer_id) . '">Custom Tab 3</a></li>';
}
