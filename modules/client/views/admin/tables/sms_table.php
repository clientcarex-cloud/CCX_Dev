<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['id','ltemplate_id','ltemplate_body','ltemplate_status','lnumbers','lcredit_minus','created_at'];

$sIndexColumn = 'id';
$sTable       = db_prefix().'_smslogs';

$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

usort($rResult, function ($a, $b) {
    return $b['id'] - $a['id'];
});


foreach ($rResult as $aRow) {
    $row = [];
    
    $row[]   = $aRow['id'];
    $row[]   = $aRow['ltemplate_id'];
    $row[]   = $aRow['ltemplate_body'];
    $row[]   = $aRow['ltemplate_status'];
    $row[]   = $aRow['lnumbers'];
    $row[]   = $aRow['lcredit_minus'];
    $row[]   = $aRow['created_at'];
    
    $output['aaData'][] = $row;
}
