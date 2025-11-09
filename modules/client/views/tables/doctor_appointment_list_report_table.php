<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'appointments.appointment_id',
    'appointments.visit_id',
    'appointments.userid',
	'appointments.consulted_date',
    'new.mr_no',
    'patients.company as patient_name',
    'patients.phonenumber as patient_mobile',
    'appointments.appointment_date',
    'appointments.visit_status',
    'enquiry_type.enquiry_type_name',
    'appointment_type.appointment_type_name',
	
];

$sIndexColumn = 'appointments.appointment_id';
$sTable       = db_prefix() . 'appointment appointments';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients patients ON patients.userid = appointments.userid',
    'LEFT JOIN ' . db_prefix() . 'enquiry_type enquiry_type ON enquiry_type.enquiry_type_id = appointments.enquiry_type_id',
    'LEFT JOIN ' . db_prefix() . 'appointment_type appointment_type ON appointment_type.appointment_type_id = appointments.appointment_type_id',
    'LEFT JOIN ' . db_prefix() . 'clients_new_fields new ON new.userid = patients.userid',
    'LEFT JOIN ' . db_prefix() . 'staff staff ON staff.staffid = appointments.enquiry_doctor_id',
];

$where = [];
if (!empty($appointment_type_id) && is_numeric($appointment_type_id)) {
    $where[] = "AND appointments.appointment_type_id = " . intval($appointment_type_id);
}

if (!empty($selected_branch_id) && is_numeric($selected_branch_id)) {
    $where[] = "AND appointments.branch_id = " . intval($selected_branch_id);
}
if (!empty($doctor_id) && is_numeric($doctor_id)) {
    $where[] = "AND appointments.enquiry_doctor_id = " . intval($doctor_id);
}
if ($consulted_from_date && $consulted_to_date) {
    $from_date = to_sql_date($consulted_from_date);
    $to_date   = to_sql_date($consulted_to_date);

    $like_conditions = [];

    // Generate all dates between from and to
    $period = new DatePeriod(
        new DateTime($from_date),
        new DateInterval('P1D'),
        (new DateTime($to_date))->modify('+1 day')
    );

    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $like_conditions[] = "(consulted_date LIKE '{$d}%' ESCAPE '!' OR appointment_date LIKE '{$d}%' ESCAPE '!')";
    }

    if (!empty($like_conditions)) {
        $where[] = 'AND (' . implode(' OR ', $like_conditions) . ')';
    }
} elseif ($consulted_from_date) {
    $sql_date = to_sql_date($consulted_from_date);
    $where[] = "AND (consulted_date LIKE '{$sql_date}%' ESCAPE '!' OR appointment_date LIKE '{$sql_date}%' ESCAPE '!')";
}



// ✅ Force default ordering by ID in DESC order
//$order = 'appointments.appointment_id DESC';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['appointments.appointment_id']);
$output  = $result['output'];
$rResult = $result['rResult'];




foreach ($rResult as $aRow) {
	
	$CI = &get_instance();
	$CI->load->model('client_model');
	$CI->db->select('i.*');
	$CI->db->from(db_prefix() . 'invoices as i');
	$CI->db->join(db_prefix() . 'itemable as item', 'item.rel_id = i.id', 'left');
	$CI->db->where(array("item.rel_type"=>'invoice', "i.clientid"=>$aRow['userid'], "i.date"=>date('Y-m-d')));
	$check_payment = $CI->db->get_where()->row();


    $row = [];
	$url = admin_url('client/get_patient_list/' . $aRow['userid']);
    $row[] = $aRow['visit_id'];
    $row[] = $aRow['mr_no'];
    $row[] = '<b><a href="' . $url . '">'.
			$aRow['patient_name'].'
		</a></b>';
    $row[] = mask_mobile_number_1($aRow['patient_mobile']);
    $row[] = _d($aRow['appointment_date']);
    $row[] = _d($aRow['consulted_date']);
	
	$row[] = get_treatments_by_userid($aRow['userid']);

    $id = $aRow['appointment_id'];
    $edit = "open_edit_modal('$id')";
    $delete = "delete_appointment('" . admin_url("appointments/delete/$id") . "')";
	
	$appointmentDate = date('Y-m-d', strtotime($aRow['appointment_date']));
	$today = date('Y-m-d');

	$status_key = '';

	if ($aRow['visit_status'] == 1) {
		$status_key = '1';
	} else {
		if ($appointmentDate < $today) {
			$status_key = 'missed';
		} elseif ($appointmentDate > $today) {
			$status_key = 'upcoming';
		} else {
			$status_key = 'today';
		}
	}

	//$row[] = format_appointment_status_custom($status_key);
	$row[] = $aRow['appointment_type_name'];
	$row[] = $aRow['enquiry_type_name'];
	
	
	
	$row[] = format_invoice_status_custom($check_payment->status);
	/* if ($aRow['visit_status'] == 1) {
		$row[] = format_appointment_status_custom($status_key);
	} else {
		if ($appointmentDate < $today) {
			$row[] = format_appointment_status_custom($status_key);
		} elseif ($appointmentDate > $today) {
			$row[] = format_appointment_status_custom($status_key);
		} else {
			// today and not visited
			if($check_payment->status == 2){
				$row[] = '
			<a href="javascript:void(0);" onclick="confirmBooking(' . $aRow['appointment_id'] . ')" class="btn btn-warning btn-sm text-white" style="margin-right: 5px;color: #fff">
				Confirm Visit
			</a>
			<a href="' . admin_url('client/edit_appointment/' . $aRow['appointment_id']) . '" class="btn btn-default btn-sm">
				<i class="fa-regular fa-pen-to-square"></i>
			</a>';
			}else{
				$row[] = '
			<a href="' . admin_url('client/edit_appointment/' . $aRow['appointment_id']) . '" class="btn btn-default btn-sm">
				<i class="fa-regular fa-pen-to-square"></i>
			</a>';
			}
			


		}
	} */
	


  
	/*$row[] = '
	<a href="' . admin_url('estimates/estimate') . '?customer_id=' . $aRow['userid'] . '" class="btn btn-info btn-sm text-white" title="Add Estimation" style="color: #fff;>
        <i class="fa fa-plus"></i>Add Package
    </a>';*/

  

    $output['aaData'][] = $row;
}

function mask_mobile_number_1($number) {
	
	if (staff_can('mobile_masking', 'customers') && !is_admin()) {
		
		$length = strlen($number);
		if ($length <= 5) {
			return $number; // no masking needed
		}
		return str_repeat('*', $length - 5) . substr($number, -5);
	}else{
		return $number;
	}
}
function get_treatments_by_userid($userid)
{
    $CI =& get_instance();
    $CI->db->select('t.description as treatment_name');
    $CI->db->from(db_prefix() . 'patient_treatment pt');
    $CI->db->join(db_prefix() . 'items t', 't.id = pt.treatment_type_id', 'left');
    $CI->db->where('pt.userid', $userid);

    $results = $CI->db->get()->result_array();

    if (!$results) return '-';

    return implode(', ', array_column($results, 'treatment_name'));
}
