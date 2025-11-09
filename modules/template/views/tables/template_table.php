<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['template_id','template_name','template_body','created_at', 'template_status', 'constant'];

$sIndexColumn = 'id';
$sTable       = db_prefix().'_templates';

$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

usort($rResult, function ($a, $b) {
    return $a['id'] - $b['id'];
});



foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['template_id'];
    $row[] = $aRow['template_name'];
    $row[] = $aRow['template_body'];
    $row[] = $aRow['created_at'];
	$row[] = $aRow['constant']; 

    // Status Toggle
    $checked = $aRow['template_status'] == 'Active' ? 'checked' : '';
    $toggle = '
        <div class="onoffswitch">
            <input type="checkbox" ' . $checked . ' data-id="' . $aRow['id'] . '" class="switch_status onoffswitch-checkbox" id="s_' . $aRow['id'] . '">
            <label class="onoffswitch-label" for="s_' . $aRow['id'] . '"></label>
        </div>';
    $row[] = $toggle;

    // Edit & Delete Buttons
    $edit = "open_modal('{$aRow['id']}','{$aRow['template_id']}','{$aRow['template_name']}','{$aRow['constant']}')";
    $delete = "delete_template('" . admin_url('template/templates/delete_templates/' . $aRow['id']) . "')";

    $row[] = '
        <a onclick="' . $edit . '" class="btn btn-warning btn-icon mright5">Edit</a>
        <a onclick="' . $delete . '" class="btn btn-danger btn-icon">Delete</a>
    ';

    $output['aaData'][] = $row;
}
