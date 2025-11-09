<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'scheduled_by',
    'scheduled_to',
    'campaign_status',
    'created_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mailflow_scheduled_campaigns';

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

        $row[] = $aRow['scheduled_to'] . '<br><span style="color: mediumpurple">' . mailflow_human_readable_time_difference($aRow['scheduled_to']) . '</span>';

        $row[] = mailflow_campaign_statuses($aRow['campaign_status'])['badge'];

        $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['scheduled_by']) . '">' . staff_profile_image($aRow['scheduled_by'], [
                'staff-profile-image-small',
            ]) . '</a>';

        $row[] = $aRow['created_at'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

        if (has_permission('mailflow', '', 'edit')) {
            $options .= '<a href="' . admin_url('mailflow/view_schedule/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-eye fa-lg"></i>
    </a>';
        }

        if (has_permission('mailflow', '', 'delete') && $aRow['campaign_status'] == 0) {
            $options .= '<a href="' . admin_url('mailflow/delete_schedule/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
            <i class="fa-regular fa-trash-can fa-lg"></i>
        </a>';
        }

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
