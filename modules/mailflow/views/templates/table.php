<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'template_name',
    'template_subject',
    'created_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mailflow_email_templates';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];

        $row[] = $aRow['template_name'];

        $row[] = $aRow['template_subject'];

        $row[] = $aRow['created_at'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

        if (has_permission('mailflow', '', 'edit')) {
            $options .= '<a href="' . admin_url('mailflow/create_template/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';
        }

        if (has_permission('mailflow', '', 'delete')) {
            $options .= '<a href="' . admin_url('mailflow/delete_template/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
            <i class="fa-regular fa-trash-can fa-lg"></i>
        </a>';
        }

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
