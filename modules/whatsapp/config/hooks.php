<?php
hooks()->add_action('app_init', 'whatsapp_hooks_loader');
function whatsapp_hooks_loader() {
    require_once(__DIR__.'/../hooks/message_hooks.php');
}