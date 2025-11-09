<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('client_model'); // adjust if different

// --- Step 1: Build the base query ---
// Note: We build the full query first, without the search, limit, and offset.

$CI->db->select('a.*, c.*, new.*, staff.*, inv.id as invoice_id, inv.total, pay.date as payment_date, c.phonenumber as mobile_number');
$CI->db->from(db_prefix() . 'invoices inv'); // Start from invoices
$CI->db->join(db_prefix() . 'invoicepaymentrecords pay', 'inv.id = pay.invoiceid', 'left');
$CI->db->join(db_prefix() . 'appointment a', 'a.userid = inv.clientid', 'left');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = a.userid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = a.enquiry_doctor_id', 'left');

// Filter: Invoice not cancelled
if ($type != 'renewal_calling') {
    $CI->db->where('inv.status !=', 2);
}
$CI->db->where("(new.mr_no IS NOT NULL AND new.mr_no != '')", null, false);


// Filter: Payment date this month
if ($type == 'cpot_calling') {
    
    // Join latest call log per patient
    $CI->db->join(
        '(SELECT t1.*
          FROM ' . db_prefix() . 'patient_call_logs t1
          JOIN (
              SELECT patientid, MAX(id) as max_id
              FROM ' . db_prefix() . 'patient_call_logs
              GROUP BY patientid
          ) t2 ON t1.id = t2.max_id
        ) as pcl',
        'pcl.patientid = c.userid',
        'left'
    );

    if ($date_filter == 'missed_calling') {
        // Missed follow-up this month
        $CI->db->where('pcl.next_calling_date >=', date('Y-m-01'));
        $CI->db->where('pcl.next_calling_date <=', date('Y-m-t'));
        $CI->db->where('pcl.next_calling_date < CURDATE()');
        $CI->db->where('DATE(pcl.created_date) < pcl.next_calling_date');
    } elseif ($date_filter == 'next_calling') {
        // Follow-ups scheduled between selected dates
        $CI->db->where('pcl.next_calling_date >=', $from_date);
        $CI->db->where('pcl.next_calling_date <=', $to_date);
        $CI->db->where('pcl.next_calling_date >= CURDATE()');
    } elseif ($date_filter == 'last_calling') {
        // Last calling created between from and to date
        $CI->db->where('DATE(pcl.created_date) >=', $from_date);
        $CI->db->where('DATE(pcl.created_date) <=', $to_date);
    } elseif ($date_filter == 'registration_date') {
        $CI->db->where('DATE(new.registration_start_date) >=', $from_date);
        $CI->db->where('DATE(new.registration_start_date) <=', $to_date);
    }else{
		// Filter by payment date in the current month
		$CI->db->where('MONTH(pay.date)', date('m'));
		$CI->db->where('YEAR(pay.date)', date('Y'));
		$CI->db->where('inv.status !=', 2);
	}
} elseif ($type == 'ppot_calling') {
   

    // Join latest call log per patient
    $CI->db->join(
        '(SELECT t1.*
          FROM ' . db_prefix() . 'patient_call_logs t1
          JOIN (
              SELECT patientid, MAX(id) as max_id
              FROM ' . db_prefix() . 'patient_call_logs
              GROUP BY patientid
          ) t2 ON t1.id = t2.max_id
        ) as pcl',
        'pcl.patientid = c.userid',
        'left'
    );


    if ($date_filter == 'missed_calling') {
        // Missed follow-up this month
        $CI->db->where('pcl.next_calling_date >=', date('Y-m-01'));
        $CI->db->where('pcl.next_calling_date <=', date('Y-m-t'));
        $CI->db->where('pcl.next_calling_date < CURDATE()');
        $CI->db->where('DATE(pcl.created_date) < pcl.next_calling_date');
    } elseif ($date_filter == 'next_calling') {
        // Follow-ups scheduled between selected dates
        $CI->db->where('pcl.next_calling_date >=', $from_date);
        $CI->db->where('pcl.next_calling_date <=', $to_date);
        $CI->db->where('pcl.next_calling_date >= CURDATE()');
    } elseif ($date_filter == 'last_calling') {
        // Last calling created between from and to date
        $CI->db->where('DATE(pcl.created_date) >=', $from_date);
        $CI->db->where('DATE(pcl.created_date) <=', $to_date);
    } elseif ($date_filter == 'registration_date') {
        $CI->db->where('DATE(new.registration_start_date) >=', $from_date);
        $CI->db->where('DATE(new.registration_start_date) <=', $to_date);
    }else{
		 $CI->db->where('MONTH(pay.date)', date('m', strtotime('-1 month')));
		$CI->db->where('YEAR(pay.date)', date('Y', strtotime('-1 month')));
		$CI->db->where('inv.status !=', 2);
	}
} elseif ($type == 'medicine_calling') {
    // Join latest casesheet per patient
    $CI->db->join(
        '(SELECT cs1.* FROM ' . db_prefix() . 'casesheet cs1
          JOIN (
            SELECT userid, MAX(id) as max_id 
            FROM ' . db_prefix() . 'casesheet 
            GROUP BY userid
          ) cs2 ON cs1.id = cs2.max_id
        ) as casesheet',
        'casesheet.userid = c.userid',
        'left'
    );

    // Get medicine_followup_days from master settings
    $medicine_followup_days = 0; // default
    foreach ($master_settings as $master) {
        if ($master['title'] == 'medicine_followup_days') {
            $medicine_followup_days = (int)$master['options'];
            break;
        }
    }

    // Apply follow-up range logic
    if ($from_date && $to_date) {
        $CI->db->where('casesheet.followup_date >=', $from_date);
        $CI->db->where('casesheet.followup_date <=', $to_date);
    } else {
        // If no from-to filter, fallback to master settings logic
        if ($medicine_followup_days == 0) {
            $CI->db->where('casesheet.followup_date <=', date('Y-m-d'));
        } else {
            $target_date = date('Y-m-d', strtotime(($medicine_followup_days > 0 ? '-' : '+') . abs($medicine_followup_days) . ' days'));
            $CI->db->where('casesheet.followup_date <=', $target_date);
        }
    }
} elseif ($type == 'treatment_followup_calling') {

    // Join latest call log per patient
    $CI->db->join(
        '(SELECT t1.*
          FROM ' . db_prefix() . 'patient_call_logs t1
          JOIN (
              SELECT patientid, MAX(id) as max_id
              FROM ' . db_prefix() . 'patient_call_logs
              GROUP BY patientid
          ) t2 ON t1.id = t2.max_id
        ) as pcl',
        'pcl.patientid = c.userid',
        'left'
    );


    if ($date_filter == 'missed_calling') {
        // Missed follow-up this month
        $CI->db->where('pcl.next_calling_date >=', date('Y-m-01'));
        $CI->db->where('pcl.next_calling_date <=', date('Y-m-t'));
        $CI->db->where('pcl.next_calling_date < CURDATE()');
        $CI->db->where('DATE(pcl.created_date) < pcl.next_calling_date');
    } elseif ($date_filter == 'next_calling') {
        // Follow-ups scheduled between selected dates
        $CI->db->where('pcl.next_calling_date >=', $from_date);
        $CI->db->where('pcl.next_calling_date <=', $to_date);
        $CI->db->where('pcl.next_calling_date >= CURDATE()');
    } elseif ($date_filter == 'last_calling') {
        // Last calling created between from and to date
        $CI->db->where('DATE(pcl.created_date) >=', $from_date);
        $CI->db->where('DATE(pcl.created_date) <=', $to_date);
    } elseif ($date_filter == 'registration_date') {
        $CI->db->where('DATE(new.registration_start_date) >=', $from_date);
        $CI->db->where('DATE(new.registration_start_date) <=', $to_date);
    }
} elseif ($type == 'reference_calling') {
    // Join tblleads to tblclients
    $CI->db->join(
        db_prefix() . 'leads AS l',
        'l.id = c.leadid',
        'left'
    );

    // Filter only staff/patient referred
    $CI->db->where_in('l.refer_type', ['staff', 'patient']);
    $CI->db->where('l.refer_id IS NOT NULL', null, false);
    $CI->db->where('l.refer_id !=', 0);

    // Join latest patient_call_logs
    $CI->db->join(
        '(SELECT t1.*
          FROM ' . db_prefix() . 'patient_call_logs t1
          JOIN (
              SELECT patientid, MAX(id) as max_id
              FROM ' . db_prefix() . 'patient_call_logs
              GROUP BY patientid
          ) t2 ON t1.id = t2.max_id
        ) as pcl',
        'pcl.patientid = c.userid',
        'left'
    );

    // Apply date filter condition
    if ($date_filter == 'missed_calling') {
        $CI->db->where('pcl.next_calling_date < CURDATE()');
        $CI->db->where('DATE(pcl.created_date) < pcl.next_calling_date');
    } elseif ($date_filter == 'next_calling' && $from_date && $to_date) {
        $CI->db->where('pcl.next_calling_date >=', $from_date);
        $CI->db->where('pcl.next_calling_date <=', $to_date);
    } elseif ($date_filter == 'last_calling' && $from_date && $to_date) {
        $CI->db->where('DATE(pcl.created_date) >=', $from_date);
        $CI->db->where('DATE(pcl.created_date) <=', $to_date);
    } elseif ($date_filter == 'registration_date' && $from_date && $to_date) {
        $CI->db->where('new.registration_start_date >=', $from_date);
        $CI->db->where('new.registration_start_date <=', $to_date);
    }
} if ($type == 'renewal_calling') {

    $CI->db->join(
        '(SELECT t1.*
          FROM ' . db_prefix() . 'patient_call_logs t1
          JOIN (
              SELECT patientid, MAX(id) as max_id
              FROM ' . db_prefix() . 'patient_call_logs
              GROUP BY patientid
          ) t2 ON t1.id = t2.max_id
        ) as pcl',
        'pcl.patientid = c.userid',
        'left'
    );

    if ($date_filter == 'missed_calling') {
        $CI->db->where('pcl.next_calling_date <', date('Y-m-d'));
        $CI->db->where('DATE(pcl.created_date) < pcl.next_calling_date');
    } elseif ($date_filter == 'next_calling' && $from_date && $to_date) {
        $CI->db->where('pcl.next_calling_date >=', $from_date);
        $CI->db->where('pcl.next_calling_date <=', $to_date);
    } elseif ($date_filter == 'last_calling' && $from_date && $to_date) {
        $CI->db->where('DATE(pcl.created_date) >=', $from_date);
        $CI->db->where('DATE(pcl.created_date) <=', $to_date);
    } elseif ($date_filter == 'registration_end_date' && $from_date && $to_date) {
        $CI->db->where('new.registration_end_date >=', $from_date);
        $CI->db->where('new.registration_end_date <=', $to_date);
    } else {
        if (!empty($from_date) && !empty($to_date)) {
            $CI->db->where('inv.duedate >=', $from_date);
            $CI->db->where('inv.duedate <=', $to_date);
        } else {
            $today = date('Y-m-d');
            $next_30_days = date('Y-m-d', strtotime('+30 days'));

            $CI->db->where('inv.duedate >=', $today);
            $CI->db->where('inv.duedate <=', $next_30_days);
        }
    }
}

// Filter by branch
if (!empty($selected_branch_id)) {
    // Handle when it's a single numeric ID
    if (is_numeric($selected_branch_id)) {
        $CI->db->where('a.branch_id', intval($selected_branch_id));
    }
    // Handle when it's an array of IDs
    elseif (is_array($selected_branch_id)) {
        // Sanitize the array values to ensure they're all integers
        $branch_ids = array_map('intval', $selected_branch_id);
        $CI->db->where_in('a.branch_id', $branch_ids);
    }
    // Handle when it's a comma-separated string
    elseif (is_string($selected_branch_id) && strpos($selected_branch_id, ',') !== false) {
        $branch_ids = array_map('intval', explode(',', $selected_branch_id));
        $CI->db->where_in('a.branch_id', $branch_ids);
    }
}

// Optional: group and order
$CI->db->group_by('c.userid');
$CI->db->order_by('pay.date', 'DESC');

// Apply the search filter if it exists
if (!empty($_POST['search']['value'])) {
    $search_value = $_POST['search']['value'];
    $CI->db->group_start(); // open bracket
    $CI->db->like('c.company', $search_value);
    $CI->db->or_like('c.phonenumber', $search_value);
    $CI->db->or_like('new.mr_no', $search_value);
    $CI->db->or_like('staff.firstname', $search_value);
    $CI->db->or_like('staff.lastname', $search_value);
    $CI->db->or_like('a.visit_id', $search_value);
    $CI->db->group_end(); // close bracket
}

// --- Step 2: Get the total number of filtered records ---
// We clone the query to get a count without the LIMIT clause
$db_for_count = clone $CI->db;
$filtered_count = $db_for_count->get()->num_rows();

// --- Step 3: Add pagination and get the data for the current page ---
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $CI->db->limit((int)$_POST['length'], (int)$_POST['start']);
}

$query = $CI->db->get();

$data  = $query->result_array();

// --- Step 4: Get the total number of records before any filters/search ---
// A more accurate way to get the total without all the conditional filters.
$CI->db->select('COUNT(DISTINCT c.userid) as total');
$CI->db->from(db_prefix() . 'invoices inv');
$CI->db->join(db_prefix() . 'invoicepaymentrecords pay', 'inv.id = pay.invoiceid', 'left');
$CI->db->join(db_prefix() . 'appointment a', 'a.userid = inv.clientid', 'left');
$CI->db->join(db_prefix() . 'clients c', 'c.userid = a.userid', 'left');
$CI->db->join(db_prefix() . 'clients_new_fields new', 'new.userid = c.userid', 'left');
$CI->db->join(db_prefix() . 'staff staff', 'staff.staffid = a.enquiry_doctor_id', 'left');
if ($type != 'renewal_calling') {
    $CI->db->where('inv.status !=', 2);
}
$CI->db->where('new.mr_no IS NOT NULL', null, false);
$total_count_query = $CI->db->get()->row();
$total_count = $total_count_query->total;

// Prepare output
$output = [
    'draw'              => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    'recordsTotal'      => $total_count,
    'recordsFiltered'   => $filtered_count,
    'aaData'            => [],
];
$start = (int)$_POST['start'];
$i = $start + 1;
foreach ($data as $row) {
    $dataRow = [];
    $url = admin_url('client/calling/' . $type . '/' . $row['userid']);
    $company = '<a href="' . $url . '" class="tw-font-medium">' . format_name($row['company']) . '</a>';
    $dataRow[] = $i++;
    $dataRow[] = $company; // from clients
    $dataRow[] = $row['mr_no'];  // from clients_new_fields?
    $dataRow[] = $row['mobile_number'];
    $dataRow[] = _d($row['appointment_date']);
    $dataRow[] = _d($row['consulted_date']);
    $dataRow[] = _d($row['registration_end_date']);
    
    $CI->db->select("c.*, s.*");
    $CI->db->from(db_prefix() . 'patient_call_logs as c');
    $CI->db->join(db_prefix() . 'staff as s', "s.staffid = c.called_by");
    $CI->db->order_by("id", "DESC");
    $CI->db->where(array("patientid" => $row['userid']));
    
    $call_data = $CI->db->get()->row();
    $called_by = "";
    if ($call_data) {
        
        $dataRow[] = _d($call_data->next_calling_date); 
        $dataRow[] = _d(date("Y-m-d", strtotime($call_data->created_date)));
        
        
        $call_status_text = '';
        if (!empty($call_data->next_calling_date) && strtotime($call_data->next_calling_date) < strtotime(date('Y-m-d'))) {
            $call_status_text = 'missed';
            $dataRow[] = format_appointment_status_custom('missed'); // HTML output
        } else {
            $dataRow[] = '';
        }
        
        $called_by = $call_data->firstname . ' ' . $call_data->lastname;
        
    } else {
        $dataRow[] = '';
        $dataRow[] = '';
        $dataRow[] = '';
    }
    
    $dataRow[] = $row['firstname'] . ' ' . $row['lastname'];
    $dataRow[] = $called_by;
    
    if ($type == 'nroc_calling') {
        if ($row['payment_date'] == NULL) {
             $output['aaData'][] = $dataRow;
        }
    } else {
        $output['aaData'][] = $dataRow;
    }
    
}

echo json_encode($output);
exit;