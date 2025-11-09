<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (file_exists(APPPATH . 'controllers/admin/Perfex_dashboard.php')) {
    $route['admin'] = 'admin/perfex_dashboard/dashboards/my_dashboard';
}
