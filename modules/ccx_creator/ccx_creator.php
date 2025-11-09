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
        'icon'     => 'fa-regular fa-lightbulb',
        'href'     => admin_url('ccx_creator'),
        'position' => 35,
    ]);

    foreach (ccx_creator_sections() as $section) {
        $CI->app_menu->add_sidebar_children_item('ccx-creator', [
            'slug'     => $section['slug'],
            'name'     => $section['label'],
            'href'     => $section['href'],
            'icon'     => $section['icon'],
            'position' => $section['position'],
        ]);
    }
}

/**
 * Central definition for module sub-sections/menu entries.
 *
 * @return array<int, array<string, mixed>>
 */
function ccx_creator_sections(): array
{
    $baseUrl = 'ccx_creator';

    return [
        [
            'key'      => 'menus',
            'slug'     => 'ccx_creator_menus',
            'label'    => 'Menus',
            'icon'     => 'fa-regular fa-rectangle-list',
            'href'     => admin_url($baseUrl . '/menus'),
            'position' => 1,
        ],
        [
            'key'      => 'pages',
            'slug'     => 'ccx_creator_pages',
            'label'    => 'Pages',
            'icon'     => 'fa-regular fa-file-lines',
            'href'     => admin_url($baseUrl . '/pages'),
            'position' => 2,
        ],
        [
            'key'      => 'forms',
            'slug'     => 'ccx_creator_forms',
            'label'    => 'Forms',
            'icon'     => 'fa-regular fa-clipboard',
            'href'     => admin_url($baseUrl . '/forms'),
            'position' => 3,
        ],
        [
            'key'      => 'pop_up',
            'slug'     => 'ccx_creator_pop_up',
            'label'    => 'Pop-up',
            'icon'     => 'fa-regular fa-message',
            'href'     => admin_url($baseUrl . '/pop_up'),
            'position' => 4,
        ],
        [
            'key'      => 'charts',
            'slug'     => 'ccx_creator_charts',
            'label'    => 'Charts',
            'icon'     => 'fa-regular fa-chart-bar',
            'href'     => admin_url($baseUrl . '/charts'),
            'position' => 5,
        ],
        [
            'key'      => 'dashboard',
            'slug'     => 'ccx_creator_dashboard',
            'label'    => 'Dashboard',
            'icon'     => 'fa-regular fa-window-maximize',
            'href'     => admin_url($baseUrl . '/dashboard'),
            'position' => 6,
        ],
        [
            'key'      => 'master_data',
            'slug'     => 'ccx_creator_master_data',
            'label'    => 'Master Data',
            'icon'     => 'fa-regular fa-folder-open',
            'href'     => admin_url($baseUrl . '/master_data'),
            'position' => 7,
        ],
    ];
}
