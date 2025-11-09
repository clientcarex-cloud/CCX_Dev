<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp
Description: Start sending bulk WhatsApp messages and API Integration
Version: 1.0.0
*/

define('WHATSAPP_MODULE_NAME', 'whatsapp');

// Register activation hook
register_activation_hook(WHATSAPP_MODULE_NAME, 'whatsapp_module_activation_hook');
function whatsapp_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

// Also call install on load (if needed)
$CI = &get_instance();
require_once(__DIR__ . '/install.php');

// Add permissions and menu
hooks()->add_action('admin_init', 'whatsapp_register_permissions');
hooks()->add_action('admin_init', 'whatsapp_module_init_menu_items');

function whatsapp_module_init_menu_items()
{
    $CI = &get_instance(); 

    if (staff_can('view_own', 'whatsapp') || staff_can('view', 'whatsapp')) {
        $CI->app_menu->add_sidebar_menu_item('whatsapp', [
            'name'     => 'WhatsApp',
            'icon'     => 'fa-brands fa-whatsapp',
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('whatsapp', [
            'slug'     => 'sms',
            'name'     => 'Open WhatsApp',
            'href'     => admin_url() . 'whatsapp/index',
            'position' => 1,
        ]);

        
    }
	if (staff_can('message_settings', 'whatsapp')) {
		$CI->app_menu->add_sidebar_children_item('whatsapp', [
				'slug'     => 'message_settings',
				'name'     => 'Message Settings',
				'href'     => admin_url() . 'whatsapp/manage_templates',
				'position' => 2,
			]);
	}
}

function whatsapp_register_permissions()
{
    $capabilities['capabilities'] = [
        'view'     => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'message_settings'      => _l('message_settings'),
    ];

    register_staff_capabilities('whatsapp', $capabilities, _l('WhatsApp'));
}

/////////////////////////////////////////
// ✅ Dynamically register all triggers
/////////////////////////////////////////

// You can later move this array to DB if needed
$trigger_map = [
    'appointment_confirmation_triggered' => 'APPOINTMENT_CONFIRMATION',
    'followup_reminder_triggered'        => 'FOLLOWUP_REMINDER',
    'treatment_not_started_triggered'    => 'TREATMENT_NOT_STARTED',
    // Add more mappings as needed
];

foreach ($trigger_map as $hook => $template_key) {
    hooks()->add_action($hook, function ($data) use ($template_key) {
        $CI =& get_instance();
        $CI->load->model('whatsapp/Message_model');

        $dispatch_data = [
            'template_key' => $template_key,
            'channel'       => $data['channel'],
            'mobile'       => $data['mobile'],
            'params'       => $data['params'],
        ];

        $CI->Message_model->dispatch_message($dispatch_data);
    });
}

