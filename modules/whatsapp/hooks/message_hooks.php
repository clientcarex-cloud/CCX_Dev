<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Hook handler for sending custom messages via WhatsApp/SMS
hooks()->add_action('send_custom_message', 'whatsapp_message_hook_handler');

function whatsapp_message_hook_handler($data)
{
    $CI =& get_instance();

    // Safely validate and extract parameters
    $trigger_key = isset($data['trigger_key']) ? trim($data['trigger_key']) : null;
    $phone       = isset($data['phone']) ? trim($data['phone']) : null;
    $params      = isset($data['params']) ? $data['params'] : [];
    $channel     = isset($data['channel']) ? strtolower($data['channel']) : 'sms';

    // Basic validation to avoid empty values
    if (!$trigger_key || !$phone) {
        log_message('error', '[Message Hook] Missing required trigger_key or phone number.');
        return;
    }

    // Load the message dispatch model
    $CI->load->model('whatsapp/Message_model');

    // Dispatch the message
    $CI->Message_model->dispatch_message($trigger_key, $phone, $params, $channel);
}
