<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CCX Creator
Description: Low-code builder for bespoke pages, forms, and workflows inside Perfex CRM.
Version: 1.0.0
Author: CCX Team
*/

define('CCX_CREATOR_MODULE_NAME', 'ccx_creator');
define('CCX_CREATOR_MODULE_VERSION', '1.0.0');

register_activation_hook(CCX_CREATOR_MODULE_NAME, 'ccx_creator_module_activation_hook');
register_deactivation_hook(CCX_CREATOR_MODULE_NAME, 'ccx_creator_module_deactivation_hook');

hooks()->add_action('admin_init', 'ccx_creator_register_permissions');
hooks()->add_action('admin_init', 'ccx_creator_register_menu');
hooks()->add_action('app_init', 'ccx_creator_maybe_run_migrations');

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
 * Ensure schema is up to date even if the module was installed before Stage 2.
 */
function ccx_creator_maybe_run_migrations(): void
{
    if (! function_exists('ccx_creator_run_migrations')) {
        require_once __DIR__ . '/install.php';
    }

    ccx_creator_run_migrations();
}

/**
 * Register the CCX Creator menu item inside the admin sidebar.
 */
function ccx_creator_register_menu(): void
{
    if (! is_staff_logged_in()) {
        return;
    }

    if (! (is_admin() || staff_can('view', 'ccx_creator') || staff_can('view_own', 'ccx_creator'))) {
        return;
    }

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('ccx-creator', [
        'name'     => 'CCX Creator',
        'icon'     => 'fa fa-cubes',
        'href'     => admin_url('ccx_creator'),
        'position' => 35,
    ]);

    $CI->app_menu->add_sidebar_children_item('ccx-creator', [
        'slug'     => 'ccx-creator-builders',
        'name'     => 'Builders',
        'href'     => admin_url('ccx_creator'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('ccx-creator', [
        'slug'     => 'ccx-creator-dashboards',
        'name'     => 'Dashboards',
        'href'     => admin_url('ccx_creator/dashboards'),
        'position' => 2,
    ]);
}

/**
 * Register CCX Creator permissions.
 */
function ccx_creator_register_permissions(): void
{
    if (! function_exists('register_staff_capabilities')) {
        return;
    }

    $capabilities = [
        'capabilities' => [
            'view'     => _l('permission_view'),
            'view_own' => _l('permission_view_own'),
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities('ccx_creator', $capabilities, 'CCX Creator');
}
