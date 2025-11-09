<?php
defined('BASEPATH') or exit('No direct script access allowed');
$hide_class = 'not_visible not-export';
$table_data = [
	[
	'name' => _l('S.No'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('S.No', $hide_columns) ? $hide_class : '']
],[
	'name' => _l('mr_no'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('mr_no', $hide_columns) ? $hide_class : '']
],[
	'name' => _l('name'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('name', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('phonenumber'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('phonenumber', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('age'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('age', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('gender'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('gender', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('email_id'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('email_id', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('city'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('city', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('treatment'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('treatment', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('assign_doctor'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('assign_doctor', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('last_calling_date'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('last_calling_date', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('next_calling_date'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('next_calling_date', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('current_status'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('current_status', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('registration_start_date'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('registration_start_date', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('registration_end_date'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('registration_end_date', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('status'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('status', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('duration'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('duration', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('medicine_days'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('medicine_days', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('followup_date'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('followup_date', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('bp'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('bp', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('pulse'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('pulse', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('weight'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('weight', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('height'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('height', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('temperature'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('temperature', $hide_columns) ? $hide_class : '']
],
[
	'name' => _l('bmi'),
	'th_attrs' => ['class' => isset($hide_columns) && in_array('bmi', $hide_columns) ? $hide_class : '']
],

];

$custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);
foreach ($custom_fields as $field) {
	array_push($table_data, [
		'name'     => $field['name'],
		'th_attrs' => 	['data-type' => $field['type'], 'data-custom-field' => 1,
						'class' => isset($hide_columns) && in_array($field['slug'],$hide_columns) ? $hide_class : ''],
	]);
}

$table_data = hooks()->apply_filters('si_leads_table_columns', $table_data);
render_datatable($table_data, isset($class) ?  $class : 'si-leads scroll-responsive', ['number-index-1'], [
	'data-last-order-identifier'=> 'si-leads',
	'data-default-order'		=> get_table_last_order('si-leads'),
]);