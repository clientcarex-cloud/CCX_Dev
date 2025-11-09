<?php
defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('token_system_navigation', 'add_master_custom_tabs');

/* function add_token_system_custom_tabs($customer_id)
{
    echo '<script>console.log("Hook Executed for Client ID: ' . $customer_id . '");</script>';
} */

