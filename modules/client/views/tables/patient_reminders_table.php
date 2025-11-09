<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$rel_type = 'customer';
$id       = $userid;

// DataTables request variables
$columns = ['r.description', 'r.date', 'r.staff', 'r.isnotified'];

$CI->db->from(db_prefix() . 'reminders r');
$CI->db->join(db_prefix() . 'staff s', 's.staffid = r.staff', 'inner');

// WHERE clause
$CI->db->where('r.rel_id', $id);
$CI->db->where('r.rel_type', $rel_type);

// Get total records before filtering
$totalRecords = $CI->db->count_all_results('', false); // preserves query

// Apply search filter if provided
if (!empty($_POST['search']['value'])) {
    $search = $_POST['search']['value'];
    $CI->db->group_start();
    foreach ($columns as $col) {
        $CI->db->or_like($col, $search);
    }
    $CI->db->group_end();
}

// Count after filtering
$filteredQuery = clone $CI->db;
$recordsFiltered = $filteredQuery->count_all_results('', false);

// Apply ordering
if (!empty($_POST['order'])) {
    foreach ($_POST['order'] as $order) {
        $colIdx = (int)$order['column'];
        $dir    = $order['dir'] === 'asc' ? 'asc' : 'desc';
        if (isset($columns[$colIdx])) {
            $CI->db->order_by($columns[$colIdx], $dir);
        }
    }
} else {
    $CI->db->order_by('r.id', 'DESC'); // default order
}
 $CI->db->order_by('r.id', 'DESC');
// Pagination (limit + offset)
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $CI->db->limit((int)$_POST['length'], (int)$_POST['start']);
}

// Select required fields
$CI->db->select('r.*, s.firstname, s.lastname');
$query   = $CI->db->get();
$results = $query->result_array();

// Build DataTables output
$output = [
    'draw'            => intval($_POST['draw']),
    'recordsTotal'    => $totalRecords,
    'recordsFiltered' => $recordsFiltered,
    'aaData'          => []
];

// Prepare each row
foreach ($results as $aRow) {
    $row = [];

    // Description with edit/delete options
    $_description = process_text_content_for_display($aRow['description']);
    if ($aRow['creator'] == get_staff_user_id() || is_admin()) {
        $_description .= '<div class="row-options">';
        if ($aRow['isnotified'] == 0) {
            $_description .= '<a href="#" onclick="edit_reminder(' . $aRow['id'] . ',this); return false;" class="edit-reminder">' . _l('edit') . '</a> | ';
        }
        $_description .= '<a href="' . admin_url('misc/delete_reminder/' . $id . '/' . $aRow['id'] . '/' . $aRow['rel_type']) . '" class="text-danger delete-reminder">' . _l('delete') . '</a>';
        $_description .= '</div>';
    }

    // Date formatting
    $_date = _dt($aRow['date']);

    // Staff link with image
    $_staff = '<a href="' . admin_url('staff/profile/' . $aRow['staff']) . '">' .
        staff_profile_image($aRow['staff'], ['staff-profile-image-small']) . ' ' .
        e($aRow['firstname'] . ' ' . $aRow['lastname']) .
        '</a>';

    // Notification status
    $_notified = $aRow['isnotified'] == 1
        ? _l('reminder_is_notified_boolean_yes')
        : _l('reminder_is_notified_boolean_no');

    // Final row output
    $row[] = $_description;
    $row[] = e($_date);
    $row[] = $_staff;
    $row[] = $_notified;
    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}

header('Content-Type: application/json');
echo json_encode($output);
exit;
