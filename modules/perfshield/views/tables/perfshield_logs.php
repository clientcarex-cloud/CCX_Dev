<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'email',
    'time',
    'ip',
    'COUNT(count) AS count',
    'lockout',
    'GROUP_CONCAT(DISTINCT country) as country',
    'GROUP_CONCAT(DISTINCT country_code) as country_code',
    'GROUP_CONCAT(DISTINCT isp) as isp',
    'mobile',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'perfshield_logs';

$join = [];

$additionalSelect = [];

$groupBy = 'GROUP BY email';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect, $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->helper('date');

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['email'];

    $timeAgo = '<span data-toggle="tooltip" data-title="' . _dt(unix_to_human($aRow['time'], true), true) . '" data-placement="bottom"
        class="text-has-action tw-text-neutral-800">
            ' . time_ago(unix_to_human($aRow['time'], true)) . '
        </span>';

    $row[] = $timeAgo;

    $row[] = $aRow['ip'];

    $row[] = $aRow['count'];

    $row[] = getLockoutCycleCount($aRow['count']);

    $row[] = $aRow['country'];

    $row[] = $aRow['country_code'];

    $row[] = $aRow['isp'];

    $row[] = ($aRow['mobile'] == '0') ? _l('no') : _l('yes');

    $output['aaData'][] = $row;
}
