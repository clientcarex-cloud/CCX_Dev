<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['template_id', 'template_name', 'template_body', 'last_updated', 'trigger_key', 'status', 'template_channel'];

$sIndexColumn = 'id';
$sTable       = db_prefix().'message_config';

$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id', 'params_required', 'template_channel']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['template_id'];
    $row[] = $aRow['template_channel'];
    $row[] = $aRow['template_name'];
    $row[] = $aRow['template_body'];
    $row[] = _dt($aRow['last_updated']);
    $row[] = $aRow['trigger_key'];

    // Status Toggle
    $checked = $aRow['status'] == 1 ? 'checked' : '';
    $row[] = '
        <div class="onoffswitch">
            <input type="checkbox" ' . $checked . ' data-id="' . $aRow['id'] . '" class="switch_status onoffswitch-checkbox" id="s_' . $aRow['id'] . '">
            <label class="onoffswitch-label" for="s_' . $aRow['id'] . '"></label>
        </div>';
		

    // Action Buttons
    $jsonData = htmlspecialchars(json_encode($aRow), ENT_QUOTES, 'UTF-8');
    $row[] = '
        <a href="#" class="btn btn-sm btn-default" onclick="editTemplate(' . $jsonData . '); return false;">
            <i class="fa fa-edit"></i>
        </a>
        <a href="' . admin_url('whatsapp/manage_templates/delete/' . $aRow['id']) . '" class="btn btn-sm btn-danger _delete">
            <i class="fa fa-trash"></i>
        </a>';

    $output['aaData'][] = $row;
}
