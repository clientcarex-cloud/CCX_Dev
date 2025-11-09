<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'staffid',
    'expiry_date'
];

$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';

$join = [];
$additionalSelect = [
    'firstname',
    'lastname'
];

$where = [
    ' AND expiry_date IS NOT NULL'
];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];

    $staffID = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' 
        . staff_profile_image($aRow['staffid'], ['staff-profile-image-small']) . 
    '</a>';

    $staffID .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';

    $row[] = $staffID;

    $row[] = $aRow['expiry_date'];

    $actions = '';
    if (has_permission('perfshield', '', 'edit')) {
    $actions .= '<a href="javascript:void(0)" class="btn btn-default btn-icon" onclick="editStaffExpiryDate(' . $aRow['staffid'] . ', \'' . _d($aRow['expiry_date']) . '\')"><i class="remove fa-solid fa-pen"></i></a>';
    }
    if (has_permission('perfshield', '', 'delete')) {
    $actions .= '<a href="javascript:void(0)" class="btn btn-danger btn-icon" onclick="removeStaffExpiry(' . $aRow['staffid'] . ')"><i class="remove fa-solid fa-trash"></i></a>';
    }
    $row[] = $actions;

    $output['aaData'][] = $row;
}
