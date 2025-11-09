<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Admin routes (respect custom admin URI setting)
$adminSegment = defined('ADMIN_URI') ? trim(ADMIN_URI, '/') : 'admin';
$adminRoute   = $adminSegment . '/ccx';
$route[$adminRoute]                               = 'ccx/index';
$route[$adminRoute . '/reports']                  = 'ccx/reports';
$route[$adminRoute . '/report/(:num)']            = 'ccx/report/$1';
$route[$adminRoute . '/report_table/(:num)']      = 'ccx/report_table/$1';
$route[$adminRoute . '/templates']                = 'ccx/templates';
$route[$adminRoute . '/template']                 = 'ccx/template';
$route[$adminRoute . '/template/(:num)']          = 'ccx/template/$1';
$route[$adminRoute . '/delete_template/(:num)']   = 'ccx/delete_template/$1';
$route[$adminRoute . '/sections']                 = 'ccx/sections';
$route[$adminRoute . '/section']                  = 'ccx/section';
$route[$adminRoute . '/section/(:num)']           = 'ccx/section/$1';
$route[$adminRoute . '/delete_section/(:num)']    = 'ccx/delete_section/$1';

// Backwards compatibility for older URLs without admin prefix
$route['ccx']                                     = 'ccx/index';
$route['ccx/reports']                             = 'ccx/reports';
$route['ccx/report/(:num)']                       = 'ccx/report/$1';
$route['ccx/report_table/(:num)']                 = 'ccx/report_table/$1';
$route['ccx/templates']                           = 'ccx/templates';
$route['ccx/template']                            = 'ccx/template';
$route['ccx/template/(:num)']                     = 'ccx/template/$1';
$route['ccx/delete_template/(:num)']              = 'ccx/delete_template/$1';
$route['ccx/sections']                            = 'ccx/sections';
$route['ccx/section']                             = 'ccx/section';
$route['ccx/section/(:num)']                      = 'ccx/section/$1';
$route['ccx/delete_section/(:num)']               = 'ccx/delete_section/$1';
