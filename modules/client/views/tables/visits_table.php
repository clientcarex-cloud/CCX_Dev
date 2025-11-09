<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'appointments.appointment_id',
    'appointments.visit_id',
    'appointments.userid',
    'new.mr_no',
    'patients.company as patient_name',
    'patients.phonenumber as patient_mobile',
    'appointments.appointment_date',
    'appointments.consulted_date',
    'appointments.visit_status',
];

$sIndexColumn = 'appointments.appointment_id';
$sTable       = db_prefix() . 'appointment appointments';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients patients ON patients.userid = appointments.userid',
    'LEFT JOIN ' . db_prefix() . 'clients_new_fields new ON new.userid = patients.userid'
];

$where = [];


		
if ($consulted_date) {
    $sql_date = to_sql_date($consulted_date);
    $where[] = "AND (consulted_date LIKE '{$sql_date}%' ESCAPE '!' OR appointment_date LIKE '{$sql_date}%' ESCAPE '!')";
}


// ✅ Force default ordering by ID in DESC order
//$order = 'appointments.appointment_id DESC';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['appointments.appointment_id']);
$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['visit_id'];
    $row[] = $aRow['mr_no'];
    $row[] = $aRow['patient_name'];
    $row[] = $aRow['patient_mobile'];
    $row[] = _d($aRow['appointment_date']);
    $row[] = _d($aRow['consulted_date']);

    $id = $aRow['appointment_id'];

  $appointmentDate = date('Y-m-d', strtotime($aRow['appointment_date']));
	$today = date('Y-m-d');

	if ($aRow['visit_status'] == 1) {
		$row[] = '<span class="btn btn-success btn-sm text-white">Visited</span> 
				<a href="' . admin_url('estimates/estimate') . '?customer_id=' . $aRow['userid'] . '" class="btn btn-info btn-sm text-white" title="Add Estimation" style="color: #fff;>
        <i class="fa fa-plus"></i>Add Package
    </a>';
	} else {
		if ($appointmentDate < $today) {
			$row[] = '<span class="btn btn-danger btn-sm text-white">Missed</span>';
		} elseif ($appointmentDate > $today) {
			$row[] = '<span class="btn btn-info btn-sm text-white">Upcoming</span>';
		} else {
			// today and not visited
			$row[] = '
				<a href="javascript:void(0);" onclick="confirmBooking(' . $aRow['appointment_id'] . ')" class="btn btn-warning btn-sm text-white" style="color: #fff; margin-right: 5px;">
					Confirm Visit
				</a>';

		}
	}



    $output['aaData'][] = $row;
}
