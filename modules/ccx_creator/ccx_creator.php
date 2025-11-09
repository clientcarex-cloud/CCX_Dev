<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CCX Creator
Description: Adds a simple CCX Creator page to the Perfex CRM sidebar.
Version: 1.0.0
Author: CCX Team
*/

define('CCX_CREATOR_MODULE_NAME', 'ccx_creator');

register_activation_hook(CCX_CREATOR_MODULE_NAME, 'ccx_creator_module_activation_hook');
register_deactivation_hook(CCX_CREATOR_MODULE_NAME, 'ccx_creator_module_deactivation_hook');

hooks()->add_action('admin_init', 'ccx_creator_register_menu');

/**
 * Installation tasks for CCX Creator.
 */
function ccx_creator_module_activation_hook(): void
{
    require_once __DIR__ . '/install.php';
    ccx_creator_install();
}

/**
 * Cleanup tasks when the module is deactivated.
 */
function ccx_creator_module_deactivation_hook(): void
{
    require_once __DIR__ . '/install.php';
    ccx_creator_uninstall();
}

/**
 * Register the CCX Creator menu item inside the admin sidebar.
 */
function ccx_creator_register_menu(): void
{
    if (! is_staff_logged_in()) {
        return;
    }

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('ccx-creator', [
        'name'     => 'CCX Creator',
        'icon'     => 'fa fa-magic',
        'href'     => admin_url('ccx_creator'),
        'position' => 35,
    ]);
}
