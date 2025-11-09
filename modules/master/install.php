<?php

defined('BASEPATH') or exit('No direct script access allowed');

function master_install()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'enquiry_type')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'enquiry_type` (
                `enquiry_type_id` INT(11) NOT NULL AUTO_INCREMENT,
                `enquiry_type_name` VARCHAR(255) NOT NULL,
                `enquiry_type_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`enquiry_type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }
	
	if (!$CI->db->table_exists(db_prefix() . 'leads_with_doctor')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'leads_with_doctor` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`branch_id` INT(11) DEFAULT NULL COMMENT "tblcustomers_groups id",
				`staffid` INT(11) DEFAULT NULL COMMENT "Doctor ID",
				`treatment_id` INT(11) DEFAULT NULL,
				`leads_id` INT(11) DEFAULT NULL,
				`slot_time` VARCHAR(25) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}


    if (!$CI->db->table_exists(db_prefix() . 'patient_response')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'patient_response` (
                `patient_response_id` INT(11) NOT NULL AUTO_INCREMENT,
                `patient_response_name` VARCHAR(255) NOT NULL,
                `patient_response_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`patient_response_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }

    if (!$CI->db->table_exists(db_prefix() . 'patient_priority')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'patient_priority` (
                `patient_priority_id` INT(11) NOT NULL AUTO_INCREMENT,
                `patient_priority_name` VARCHAR(255) NOT NULL,
                `patient_priority_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`patient_priority_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }


    /* if (!$CI->db->table_exists(db_prefix() . 'slots')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'slots` (
                `slots_id` INT(11) NOT NULL AUTO_INCREMENT,
                `slots_name` VARCHAR(255) NOT NULL,
                `slots_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`slots_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }

    if (!$CI->db->table_exists(db_prefix() . 'treatment')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'treatment` (
                `treatment_id` INT(11) NOT NULL AUTO_INCREMENT,
                `treatment_name` VARCHAR(255) NOT NULL,
                `treatment_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`treatment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    } 

    if (!$CI->db->table_exists(db_prefix() . 'consultation_fee')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'consultation_fee` (
                `consultation_fee_id` INT(11) NOT NULL AUTO_INCREMENT,
                `consultation_fee_name` VARCHAR(255) NOT NULL,
                `consultation_fee_status` TINYINT(1) DEFAULT 1,
                PRIMARY KEY (`consultation_fee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }*/
	if (!$CI->db->table_exists(db_prefix() . 'patient_source')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient_source` (
				`patient_source_id` INT(11) NOT NULL AUTO_INCREMENT,
				`patient_source_name` VARCHAR(255) NOT NULL,
				`patient_source_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`patient_source_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'medicine')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'medicine` (
				`medicine_id` INT(11) NOT NULL AUTO_INCREMENT,
				`medicine_name` VARCHAR(255) NOT NULL,
				`medicine_status` TINYINT(1) DEFAULT 1,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`medicine_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'medicine_potency')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'medicine_potency` (
				`medicine_potency_id` INT(11) NOT NULL AUTO_INCREMENT,
				`medicine_potency_name` VARCHAR(100) NOT NULL,
				`medicine_potency_status` TINYINT(1) DEFAULT 1,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`medicine_potency_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'medicine_dose')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'medicine_dose` (
				`medicine_dose_id` INT(11) NOT NULL AUTO_INCREMENT,
				`medicine_dose_name` VARCHAR(100) NOT NULL,
				`medicine_dose_status` TINYINT(1) DEFAULT 1,
				`medicine_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`medicine_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`medicine_dose_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'medicine_timing')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'medicine_timing` (
				`medicine_timing_id` INT(11) NOT NULL AUTO_INCREMENT,
				`medicine_timing_name` VARCHAR(100) NOT NULL,
				`medicine_timing_status` TINYINT(1) DEFAULT 1,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`medicine_timing_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'patient_status')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient_status` (
				`patient_status_id` INT(11) NOT NULL AUTO_INCREMENT,
				`patient_status_name` VARCHAR(255) NOT NULL,
				`patient_status_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`patient_status_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'appointment_type')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'appointment_type` (
				`appointment_type_id` INT(11) NOT NULL AUTO_INCREMENT,
				`appointment_type_name` VARCHAR(255) NOT NULL,
				`appointment_type_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`appointment_type_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	} 
	if (!$CI->db->table_exists(db_prefix() . 'call_type')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'call_type` (
				`call_type_id` INT(11) NOT NULL AUTO_INCREMENT,
				`call_type_name` VARCHAR(255) NOT NULL,
				`call_type_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`call_type_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	if (!$CI->db->table_exists(db_prefix() . 'criteria')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'criteria` (
				`criteria_id` INT(11) NOT NULL AUTO_INCREMENT,
				`criteria_name` VARCHAR(255) NOT NULL,
				`criteria_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`criteria_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'specialization')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'specialization` (
				`specialization_id` INT(11) NOT NULL AUTO_INCREMENT,
				`specialization_name` VARCHAR(255) NOT NULL,
				`specialization_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`specialization_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	if (!$CI->db->table_exists(db_prefix() . 'shift')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'shift` (
				`shift_id` INT(11) NOT NULL AUTO_INCREMENT,
				`shift_name` VARCHAR(255) NOT NULL,
				`shift_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`shift_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'languages')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'languages` (
				`languages_id` INT(11) NOT NULL AUTO_INCREMENT,
				`languages_name` VARCHAR(255) NOT NULL,
				`languages_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`languages_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'master_settings')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'master_settings` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`multi_select` INT(1) DEFAULT 0,
				`table` VARCHAR(100) DEFAULT NULL,
				`title` VARCHAR(100) DEFAULT NULL,
				`options` TEXT DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->field_exists('branch_id', db_prefix() . 'master_settings')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'master_settings` 
			ADD COLUMN `branch_id` INT NULL AFTER `multi_select`;
		');
	}
	
if (!$CI->db->field_exists('branch_id', db_prefix() . 'staff')) {
	$CI->db->query('
		ALTER TABLE `' . db_prefix() . 'staff` 
		ADD COLUMN `branch_id` INT NULL AFTER `is_logged_in`;
	');
}

$staffBranchColumn = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'staff` LIKE "branch_id"')->row();
if ($staffBranchColumn) {
	$type = isset($staffBranchColumn->Type) ? strtolower($staffBranchColumn->Type) : '';
	if (strpos($type, 'int') !== false) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'staff`
			MODIFY COLUMN `branch_id` VARCHAR(255) NULL DEFAULT NULL;
		');
	}
}

	
	if (!$CI->db->table_exists(db_prefix() . 'state')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'state` (
				`state_id` INT(11) NOT NULL AUTO_INCREMENT,
				`state_name` VARCHAR(255) NOT NULL,
				`state_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`state_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	if (!$CI->db->table_exists(db_prefix() . 'city')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'city` (
				`city_id` INT(11) NOT NULL AUTO_INCREMENT,
				`city_name` VARCHAR(255) NOT NULL,
				`state_id` INT(11) NOT NULL,
				`city_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`city_id`),
				FOREIGN KEY (`state_id`) REFERENCES `' . db_prefix() . 'state`(`state_id`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'pincode')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'pincode` (
				`pincode_id` INT(11) NOT NULL AUTO_INCREMENT,
				`pincode_name` VARCHAR(20) NOT NULL,
				`city_id` INT(11) NOT NULL,
				`pincode_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`pincode_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}



	if (!$CI->db->table_exists(db_prefix() . 'treatment_sub_type')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'treatment_sub_type` (
				`treatment_sub_type_id` INT(11) NOT NULL AUTO_INCREMENT,
				`treatment_sub_type_name` VARCHAR(255),
				`treatment_sub_type_price` double(10,2),
				`treatment_type_id` INT(11),
				`treatment_sub_type_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`treatment_sub_type_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'suggested_diagnostics')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'suggested_diagnostics` (
				`suggested_diagnostics_id` INT(11) NOT NULL AUTO_INCREMENT,
				`suggested_diagnostics_name` VARCHAR(255) NOT NULL,
				`suggested_diagnostics_status` TINYINT(1) DEFAULT 1,
				PRIMARY KEY (`suggested_diagnostics_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	
	if (!$CI->db->field_exists('medicine_code', db_prefix() . 'medicine')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'medicine` 
			ADD COLUMN `medicine_code` VARCHAR(45) NULL AFTER `medicine_id`
		');
	}

	
		
	$titles = [
    ['table' => 'leads_status',     'title' => 'lead_call_log_patient_response', 'options' => '', 'multi_select' => '1'],
    ['table' => 'clients',              'title' => 'patient_inactive_fields',        'options' => '', 'multi_select' => '1'],
    ['table' => 'customer_groups',      'title' => 'branch_code',                    'options' => '', 'multi_select' => '2'],
    ['table' => 'missed_appointment',      'title' => 'missed_appointment',                    'options' => '', 'multi_select' => '2'],
    ['table' => 'customer_groups',      'title' => 'branch_short_code',              'options' => '', 'multi_select' => '2'],
    ['table' => 'leads_status',         'title' => 'lead_to_patient_convert_status', 'options' => '', 'multi_select' => '0'],
    ['table' => 'show_finance_in_doctor_reports', 'title' => 'show_finance_for_all', 'options' => '', 'multi_select' => '0'],
	['table' => 'customer_groups',      'title' => 'medicine_followup_days',              'options' => '', 'multi_select' => '2'],
	['table' => 'customer_groups',      'title' => 'discount_limit_settings',                    'options' => '', 'multi_select' => '2'],
	['table' => 'customer_groups',      'title' => 'invoice_minimum_period_settings',                    'options' => '', 'multi_select' => '2'],
    ['table' => 'acknowledge', 'title' => 'invoice_acknowledge', 'options' => '', 'multi_select' => '3'],
	['table' => 'roles',               'title' => 'medicine_period_mandatory_roles',            'options' => '', 'multi_select' => '1'],
	];

	$CI = &get_instance();
	$staff_id = get_staff_user_id();
	if ($staff_id) {
		$CI->db->select('branch_id');
		$CI->db->from(db_prefix() . 'staff');
		$CI->db->where('staffid', $staff_id);
		$row = $CI->db->get()->row();
		$current_branch_id =  $row ? $row->branch_id : null;
	}else{
		$current_branch_id = 0;
	}
	$CI->db->query(
		"DELETE FROM `" . db_prefix() . "master_settings` WHERE `title` = ? AND `branch_id` = ?",
		[$title, $current_branch_id]
	);

	foreach ($titles as $setting) {
		$table = $setting['table'];
		$title = $setting['title'];
		$options = $setting['options'];
		$multi_select = $setting['multi_select'];

		$query = $CI->db->query(
			"SELECT * FROM `" . db_prefix() . "master_settings` WHERE `title` = ? AND `branch_id` = ?",
			[$title, $current_branch_id]
		);

		if ($query->num_rows() == 0) {
			$CI->db->query(
				"INSERT INTO `" . db_prefix() . "master_settings` (`title`, `table`, `options`, `multi_select`, `branch_id`) VALUES (?, ?, ?, ?, ?)",
				[$title, $table, $options, $multi_select, $current_branch_id]
			);
		}
	}


	if ($CI->db->field_exists('slot_id', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'lead_call_logs`
			CHANGE COLUMN `slot_id` `slot_time` VARCHAR(25) NOT NULL;
		');
	}
	
	
	$tables = [
		'chief_complaint' => [
			'id' => 'chief_complaint_id',
			'name' => 'chief_complaint_name',
			'status' => 'chief_complaint_status',
		],
		'medical_problem' => [
			'id' => 'medical_problem_id',
			'name' => 'medical_problem_name',
			'status' => 'medical_problem_status',
		],
		'medical_investigation' => [
			'id' => 'medical_investigation_id',
			'name' => 'medical_investigation_name',
			'status' => 'medical_investigation_status',
		],
		'dental_investigation' => [
			'id' => 'dental_investigation_id',
			'name' => 'dental_investigation_name',
			'status' => 'dental_investigation_status',
		],
		'treatment_type' => [
			'id' => 'treatment_type_id',
			'name' => 'treatment_type_name',
			'status' => 'treatment_type_status',
		],
		'treatment_procedure' => [
			'id' => 'treatment_procedure_id',
			'name' => 'treatment_procedure_name',
			'status' => 'treatment_procedure_status',
		],
		'lab' => [
			'id' => 'lab_id',
			'name' => 'lab_name',
			'status' => 'lab_status',
		],
		'lab_work' => [
			'id' => 'lab_work_id',
			'name' => 'lab_work_name',
			'status' => 'lab_work_status',
		],
		'lab_followup' => [
			'id' => 'lab_followup_id',
			'name' => 'lab_followup_name',
			'status' => 'lab_followup_status',
		],
		'case_remark' => [
			'id' => 'case_remark_id',
			'name' => 'case_remark_name',
			'status' => 'case_remark_status',
		],
	];

	foreach ($tables as $table => $columns) {
		$full_table = db_prefix() . $table;
		if (!$CI->db->table_exists($full_table)) {
			$CI->db->query("
				CREATE TABLE `{$full_table}` (
					`{$columns['id']}` INT(11) NOT NULL AUTO_INCREMENT,
					`{$columns['name']}` VARCHAR(255) NOT NULL,
					`{$columns['status']}` TINYINT(1) DEFAULT 1,
					PRIMARY KEY (`{$columns['id']}`)
				) ENGINE=InnoDB DEFAULT CHARSET={$CI->db->char_set};
			");
		}
	}



}

function master_uninstall()
{
    $CI = &get_instance();
	/* $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'leads_with_doctor`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'enquiry_type`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_response`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_priority`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'slots`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_source`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'treatment`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'medicine`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'consultation_fee`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'medicine_potency`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'medicine_dose`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'medicine_timing`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_status`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'appointment_type`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'criteria`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'specialization`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'shift`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'master_settings`;'); */

}
