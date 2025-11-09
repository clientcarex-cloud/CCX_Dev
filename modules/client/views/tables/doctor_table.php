<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$start  = $CI->input->post('start') ?? 0;
$length = $CI->input->post('length') ?? 10;
$draw   = $CI->input->post('draw');

// Filter logic (optional)
$search = $CI->input->post('search')['value'] ?? '';

$CI->db->select('d.staffid, d.firstname, d.lastname, d.email, d.phonenumber, role.name as role');
$CI->db->from(db_prefix() . 'staff d');
$CI->db->join(db_prefix() . 'roles role', 'role.roleid = d.role', 'left');
$CI->db->group_start();
$CI->db->where('LOWER(role.name)', 'doctor');
$CI->db->or_where('LOWER(role.name)', 'service doctor');
$CI->db->group_end();

$CI->db->where('d.active', 1);

// Optional: apply search to name, email or phone
if ($search) {
    $CI->db->group_start();
    $CI->db->like('d.firstname', $search);
    $CI->db->or_like('d.lastname', $search);
    $CI->db->or_like('d.email', $search);
    $CI->db->or_like('d.phonenumber', $search);
    $CI->db->group_end();
}

// Clone for counting total
$total_query = clone $CI->db;
$total_filtered = $total_query->get()->num_rows();

// Add pagination
$CI->db->limit($length, $start);
$CI->db->order_by('d.staffid', 'DESC');

$query  = $CI->db->get();
$results = $query->result_array();

$output = [
    "draw" => intval($draw),
    "recordsTotal" => $total_filtered,
    "recordsFiltered" => $total_filtered,
    "data" => [],
];

// Build rows
foreach ($results as $row) {
    $full_name = $row['firstname'] . ' ' . $row['lastname'];
    $edit_url = admin_url('client/doctor/edit_doctor/' . $row['staffid']);
    $availability_url = admin_url('client/doctor/availability/' . $row['staffid']);

    $actions = '
        <a href="'.$edit_url.'" class="btn btn-sm btn-primary" style="color: #fff">Edit</a>
        <a href="'.$availability_url.'" class="btn btn-sm btn-success" style="color: #fff">Availability</a>
    ';

    $output['data'][] = [
        $row['staffid'],
        $full_name,
        $row['email'],
        $row['role'],
        $row['phonenumber'],
        $actions,
    ];
}

echo json_encode($output);
exit;
