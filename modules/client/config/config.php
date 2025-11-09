<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hook['pre_system'][] = [
    'class'    => '',
    'function' => 'include_custom_tabs_helper',
    'filename' => 'custom_tabs_helper.php',
    'filepath' => 'modules/client/helpers',
    'params'   => []
];


$config['vertical_name'] = 'AMR Autism'; 