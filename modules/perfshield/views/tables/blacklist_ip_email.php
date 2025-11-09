<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'ip_email',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'blacklist';

$join = [];
$additionalSelect = [];

$where = [];

array_push($where, 'AND type = ' . $this->ci->db->escape($type));

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];

    $row[] = $key + 1;

    $row[] = $aRow['ip_email'];
    $actions = '';
    if (has_permission('perfshield', '', 'delete')) {
    $actions .= '<a href="javascript:void(0)" class="btn btn-danger btn-icon" onclick="removeIpOrEmailFromBlacklist(\'' . $type . '\', ' .  $aRow['id'] . ')"><i class="remove fa-solid fa-trash"></i></a>';
    }
    $row[] = $actions;

    $output['aaData'][] = $row;
}
