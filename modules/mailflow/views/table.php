<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'sent_by',
    'total_emails_to_send',
    'total_sms_to_send',
    'total_whatsapp_to_send',
    'created_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mailflow_newsletter_history';

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

        $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['sent_by']) . '">' . staff_profile_image($aRow['sent_by'], [
                'staff-profile-image-small',
            ]) . '</a>';

        $row[] = $aRow['total_emails_to_send'] ?? 0;

        $row[] = $aRow['total_sms_to_send'] ?? 0;
		
        $row[] = $aRow['total_whatsapp_to_send'] ?? 0;

        $row[] = $aRow['created_at'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('mailflow/view_newsletter/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fas fa-eye fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
