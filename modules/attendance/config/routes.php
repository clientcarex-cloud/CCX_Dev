<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['attendance/attendance_staff'] = 'attendance_staff/attendance_staff';

$route['attendance/auth_token'] = 'attendance_staff/auth_token';
$route['attendance/delete/(:any)/(:num)'] = 'attendance_staff/delete/$1/$2';
$route['attendance/get_record_by_id/(:any)'] = 'attendance_staff/get_record_by_id/$1';
