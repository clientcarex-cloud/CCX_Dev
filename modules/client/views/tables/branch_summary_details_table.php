<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

// Assuming $ownership_details is already populated as per your data above

$output = [
    "draw" => $draw,
    "recordsTotal" => count($ownership_details),
    "recordsFiltered" => count($ownership_details),
    "data" => []
];

foreach ($ownership_details as $aRow) {
    $row = [];

    // Example columns to show in table (adjust as per your front-end columns)
	$url = admin_url('client/index/' . $aRow['userid']);
	$row[] = '<a href="' . $url . '" style="color:blue;">' . format_name($aRow['company']) . '</a>';

    $row[] = $aRow['mr_no'];
    $row[] = get_staff_full_name($aRow['enquiry_doctor_id']);   
    $row[] = $aRow['treatment_name'];	
    $row[] = date("d-m-Y", strtotime($aRow['appointment_date']));    
    $row[] = !empty($aRow['consulted_date']) ? date("d-m-Y", strtotime($aRow['consulted_date'])) : '';
   
	$packageDetailsList = $CI->client_model->get_patient_package_details($aRow['userid']);
    $packageCount = count($packageDetailsList);

    $total = $paid = $due = 0;
    foreach ($packageDetailsList as $p) {
        $total += $p['total'];
        $paid  += $p['paid'];
        $due   += $p['due'];
    }
	
	$row[] = e(app_format_money_custom($total, 1));
    $row[] = e(app_format_money_custom($paid, 1));
    $row[] = e(app_format_money_custom($due, 1));

    $output['data'][] = $row;
}

echo json_encode($output);
exit;
