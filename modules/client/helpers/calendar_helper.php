<?php
defined('BASEPATH') or exit('No direct script access allowed');

function add_calendar_assets($group = 'admin')
{
    $locale = $GLOBALS['locale'] ?? 'en';
    $CI     = &get_instance();

    $CI->app_scripts->add('fullcalendar-js', 'assets/plugins/fullcalendar/lib/main.min.js', $group);

    if ($locale != 'en' && file_exists(FCPATH . 'assets/plugins/fullcalendar/lib/locales/' . $locale . '.js')) {
        $CI->app_scripts->add('fullcalendar-lang-js', 'assets/plugins/fullcalendar/lib/locales/' . $locale . '.js', $group);
    }

    $CI->app_css->add('fullcalendar-css', 'assets/plugins/fullcalendar/lib/main.min.css', $group);
}
