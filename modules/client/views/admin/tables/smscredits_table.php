<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['id','package','pcredit','ccredit','description','created_at'];

$sIndexColumn = 'id';
$sTable       = db_prefix().'_smscredits';

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
    $row[]   = $aRow['package'];
    $row[]   = $aRow['pcredit'];
    $row[]   = $aRow['ccredit'];
    $row[]   = $aRow['description'];
    $row[]   = $aRow['created_at'];
    
    $output['aaData'][] = $row;
}
