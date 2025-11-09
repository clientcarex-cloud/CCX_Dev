<?php

defined('BASEPATH') or exit('No direct script access allowed');

function client_install()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'appointment')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'appointment` (
                `appointment_id` INT(11) NOT NULL AUTO_INCREMENT,
                `enquiry_type_id` INT(1),
                `visit_id` VARCHAR(11) NOT NULL,
                `patient_response_id` INT(1),
                `patient_priority_id` INT(1),
                `slots_id` INT(1),
                `patient_source_id` INT(1),
                `userid` INT(1),
                `next_calling_date` DATE,
                `appointment_date` DATETIME,
                `treatment_id` INT(1),
                `enquiry_doctor_id` INT(1),
                `unit_doctor_id` INT(1),
                `remarks` TEXT,
                `consultation_fee_id` INT(1),
                `visit_status` VARCHAR(45) NOT NULL,
                PRIMARY KEY (`appointment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }
	if (!$CI->db->table_exists(db_prefix() . 'share_request')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'share_request` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`user_id` INT(11) NOT NULL,
				`type` ENUM("patient", "staff") NOT NULL,
				`date_sent` DATETIME NOT NULL,
				`created_by` INT(11) NOT NULL,
				`status` ENUM("pending", "sent") NOT NULL DEFAULT "pending",
				`send_email` TINYINT(1) NOT NULL DEFAULT 0,
				`send_sms` TINYINT(1) NOT NULL DEFAULT 0,
				`send_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
				`share_key` VARCHAR(5) NOT NULL,
				`feedback_id` INT(11) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	if (!$CI->db->table_exists(db_prefix() . 'patient_treatment')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient_treatment` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`created_at` DATETIME DEFAULT NULL,
				`casesheet_id` INT(11) DEFAULT NULL,
				`userid` INT(11) DEFAULT NULL,
				`treatment_type_id` INT(11) DEFAULT NULL,
				`improvement` VARCHAR(15) DEFAULT NULL COMMENT "tblitems id",
				`duration_value` VARCHAR(45) DEFAULT NULL,
				`treatment_status` VARCHAR(45) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

    if (!$CI->db->table_exists(db_prefix() . 'clients_new_fields')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'clients_new_fields` (
                `clients_new_fields_id` INT(11) NOT NULL AUTO_INCREMENT,
                `userid` INT(11) NOT NULL,
                `mr_no` VARCHAR(11) NOT NULL,
                `salutation` VARCHAR(11) NOT NULL,
                `email_id` VARCHAR(100) NOT NULL,
                `pincode` VARCHAR(10) NOT NULL,
                `marital_status` VARCHAR(15) NOT NULL,
                `area` TEXT NOT NULL,
                `gender` VARCHAR(11) NOT NULL,
                `age` INT(11) NOT NULL,
                `dob` DATE NOT NULL,
                `patient_status` VARCHAR(255) NOT NULL,
                `whatsapp_number` VARCHAR(255) NOT NULL,
                `alt_number1` VARCHAR(255) NOT NULL,
                `alt_number2` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`clients_new_fields_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }

    
    if (!$CI->db->table_exists(db_prefix() . 'patient_activity_log')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'patient_activity_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `patientid` INT(11) NOT NULL,
                `description` LONGTEXT NOT NULL,
                `additional_data` MEDIUMTEXT DEFAULT NULL,
                `date` DATETIME NOT NULL,
                `staffid` INT(11) NOT NULL,
                `full_name` VARCHAR(100) DEFAULT NULL,
                `custom_activity` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COLLATE=' . $CI->db->dbcollat . ';
        ');
    }

    if (!$CI->db->table_exists(db_prefix() . 'patient_call_logs')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'patient_call_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `patientid` INT(11) NOT NULL,
                `called_by` VARCHAR(100) NOT NULL,
                `criteria_id` VARCHAR(255) NOT NULL,
                `next_calling_date` DATE NOT NULL,
                `appointment_type_id` VARCHAR(100) NOT NULL,
                `appointment_date` DATE NOT NULL,
                `created_date` DATETIME NOT NULL,
                `comments` TEXT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COLLATE=' . $CI->db->dbcollat . ';
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
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COLLATE=' . $CI->db->dbcollat . ';
        ');
    }
   
    if (!$CI->db->table_exists(db_prefix() . 'doctor_new_fields')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'doctor_new_fields` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `doctor_id` INT(11) NOT NULL COMMENT "FK to tblstaff.staffid",
                `salutation` VARCHAR(10),
                `gender` VARCHAR(10),
                `qualification` VARCHAR(100),
                `signature` VARCHAR(255) COMMENT "Path to uploaded signature image",
                `location` VARCHAR(255),
                `licence_number` VARCHAR(100),
                `department` VARCHAR(100),
                `specialization` VARCHAR(100),
                `branch` VARCHAR(100),
                `date_of_birth` DATE,
                `experience_years` INT(3),
                `shift_id` INT(10),
                `consultation_fee` DECIMAL(10,2),
                PRIMARY KEY (`id`),
                KEY `doctor_id` (`doctor_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COLLATE=' . $CI->db->dbcollat . ';
        ');
    }


	if (!$CI->db->table_exists(db_prefix() . 'lead_call_logs')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'lead_call_logs` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`created_date` DATETIME NOT NULL,
				`branch_id` INT(11) NOT NULL,
				`enquired_by` INT(11) NOT NULL,
				`appointment_date` DATE NOT NULL,
				`doctor_id` INT(11) NOT NULL,
				`slot_id` VARCHAR(25) COLLATE utf8mb4_unicode_ci NOT NULL,
				`patient_response_id` INT(11) NOT NULL,
				`leads_id` INT(11) NOT NULL,
				`comments` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		');
	}

    if (!$CI->db->table_exists(db_prefix() . 'casesheet')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'casesheet` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`userid` INT(11) NOT NULL,

				-- Personal History
				`appetite` VARCHAR(255) DEFAULT NULL,
				`bowels` VARCHAR(255) DEFAULT NULL,
				`aversion` VARCHAR(255) DEFAULT NULL,
				`sleep` VARCHAR(255) DEFAULT NULL,
				`sun_headache` VARCHAR(255) DEFAULT NULL,
				`thirst` VARCHAR(255) DEFAULT NULL,
				`dreams` TEXT DEFAULT NULL,
				`diabetes` TEXT DEFAULT NULL,
				`thyroid` TEXT DEFAULT NULL,
				`past_treatment_history` TEXT DEFAULT NULL,
				`menstrual_obstetric_history` TEXT DEFAULT NULL,
				`desires` VARCHAR(255) DEFAULT NULL,
				`sweat` VARCHAR(255) DEFAULT NULL,
				`urine` VARCHAR(255) DEFAULT NULL,
				`side` VARCHAR(255) DEFAULT NULL,
				`habits` VARCHAR(255) DEFAULT NULL,
				`thermals` VARCHAR(255) DEFAULT NULL,
				`investigation` TEXT DEFAULT NULL,
				`hypertension` TEXT DEFAULT NULL,
				`hyperlipidemia` TEXT DEFAULT NULL,
				`family_history` TEXT DEFAULT NULL,
				`tongue` VARCHAR(255) DEFAULT NULL,
				`addiction` VARCHAR(255) DEFAULT NULL,
				`staffid` INT(11) DEFAULT NULL,

				-- Preliminary Data
				`presenting_complaints` TEXT DEFAULT NULL,

				-- General Examination
				`bp` VARCHAR(20) DEFAULT NULL,
				`pulse` VARCHAR(20) DEFAULT NULL,
				`weight` VARCHAR(20) DEFAULT NULL,
				`height` VARCHAR(20) DEFAULT NULL,
				`temperature` VARCHAR(20) DEFAULT NULL,
				`bmi` VARCHAR(20) DEFAULT NULL,
				`mental_generals` TEXT DEFAULT NULL,
				`pg` TEXT DEFAULT NULL,
				`particulars` TEXT DEFAULT NULL,
				`miasmatic_diagnosis` TEXT DEFAULT NULL,
				`analysis_evaluation` TEXT DEFAULT NULL,
				`reportorial_result` TEXT DEFAULT NULL,
				`management` TEXT DEFAULT NULL,
				`diet` TEXT DEFAULT NULL,
				`exercise` TEXT DEFAULT NULL,
				`critical` TEXT DEFAULT NULL,
				`level_of_assent` TEXT DEFAULT NULL,
				`dos_and_donts` TEXT DEFAULT NULL,
				`level_of_assurance` TEXT DEFAULT NULL,
				`criteria_future_plan_rx` TEXT DEFAULT NULL,
				`nutrition` VARCHAR(100) DEFAULT NULL,

				-- Clinical Observation
				`progress` TEXT DEFAULT NULL,
				`clinical_observation` TEXT DEFAULT NULL,
				`suggested_duration` INT(11) DEFAULT NULL,
				`documents` TEXT DEFAULT NULL,
				`medicine_days` INT(11) DEFAULT NULL,
				`followup_date` DATE DEFAULT NULL,
				`patient_status` VARCHAR(50) DEFAULT NULL,

				-- Mind Section
				`mind` TEXT DEFAULT NULL,

				-- Common Fields
				`date` DATE DEFAULT NULL,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	if (!$CI->db->table_exists(db_prefix() . 'patient_prescription')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient_prescription` (
				`patient_prescription_id` INT(11) NOT NULL AUTO_INCREMENT,
				`prescription_data` TEXT NOT NULL,
				`created_by` INT(11) NOT NULL,
				`created_datetime` DATETIME NOT NULL,
				`patient_prescription_status` TINYINT(1) NOT NULL DEFAULT 1,
				`userid` INT(11) DEFAULT NULL,
				PRIMARY KEY (`patient_prescription_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'patient_journey_log')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient_journey_log` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`userid` INT(11) DEFAULT NULL,
				`status` VARCHAR(100) DEFAULT NULL,
				`created_at` DATETIME DEFAULT NULL,
				`created_by` INT(11) DEFAULT NULL,
				`remarks` TEXT DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	
	if (!$CI->db->field_exists('lead_nature', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `lead_nature` VARCHAR(45) NULL AFTER `lead_value`');
	}
	if (!$CI->db->field_exists('followup_date', db_prefix() . 'lead_call_logs')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
        ADD COLUMN `followup_date` DATE NULL AFTER `comments`');
}

	if (!$CI->db->field_exists('enquiry_type_id', db_prefix() . 'clients')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` 
			ADD COLUMN `enquiry_type_id` INT NULL AFTER `addedfrom`');
	}
	
	if ($CI->db->field_exists('type', db_prefix() . 'share_request')) {
		$CI->db->query("
			ALTER TABLE `" . db_prefix() . "share_request` 
			CHANGE COLUMN `type` `type` ENUM('patient', 'staff', 'lead') NOT NULL
		");
	}

	
	if ($CI->db->field_exists('visit_status', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			CHANGE COLUMN `visit_status` `visit_status` INT NULL DEFAULT 0');
	}

	if ($CI->db->field_exists('mr_no', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'clients_new_fields` 
			CHANGE COLUMN `mr_no` `mr_no` VARCHAR(50) NOT NULL;');
	}
	
	if (!$CI->db->field_exists('appointment_type_id', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `appointment_type_id` INT NULL AFTER `visit_status`;');
	}
	
	if (!$CI->db->field_exists('consulted_date', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `consulted_date` DATETIME NULL AFTER `appointment_date`');
	}
	
	if (!$CI->db->field_exists('created_by', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `created_by` INT NULL AFTER `appointment_type_id`');
	}

	// Add 'created_at' column if it doesn't exist
	if (!$CI->db->field_exists('created_at', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `created_at` DATETIME NULL AFTER `created_by`');
	}

	// Add 'updated_by' column if it doesn't exist
	if (!$CI->db->field_exists('updated_by', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `updated_by` INT NULL AFTER `created_at`');
	}

	// Add 'updated_at' column if it doesn't exist
	if (!$CI->db->field_exists('updated_at', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `updated_at` DATETIME NULL AFTER `updated_by`');
	}
	
	if (!$CI->db->field_exists('branch_id', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `branch_id` INT NULL AFTER `updated_at`');
	}
	
	// Handle `mr_no` in `clients_new_fields`
	$clients_table = db_prefix() . 'clients_new_fields';
	if (!$CI->db->field_exists('mr_no', $clients_table)) {
		$CI->db->query('
			ALTER TABLE `' . $clients_table . '` 
			ADD COLUMN `mr_no` VARCHAR(50) NOT NULL
		');
	} else {
		$CI->db->query('
			ALTER TABLE `' . $clients_table . '` 
			CHANGE COLUMN `mr_no` `mr_no` VARCHAR(50) NOT NULL
		');
	}
	
	if (!$CI->db->field_exists('treatment_followup_date', db_prefix() . 'patient_treatment')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `treatment_followup_date` DATE NULL AFTER `treatment_status`
		');
	}

	// Handle `visit_id` in `appointment`
	$appointment_table = db_prefix() . 'appointment';
	if (!$CI->db->field_exists('visit_id', $appointment_table)) {
		$CI->db->query('
			ALTER TABLE `' . $appointment_table . '` 
			ADD COLUMN `visit_id` VARCHAR(45) NOT NULL
		');
	} else {
		$CI->db->query('
			ALTER TABLE `' . $appointment_table . '` 
			CHANGE COLUMN `visit_id` `visit_id` VARCHAR(45) NOT NULL
		');
	}
	
	$call_log_table = db_prefix() . 'patient_call_logs';

	// Add better_patient if not exists
	if (!$CI->db->field_exists('better_patient', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			ADD COLUMN `better_patient` VARCHAR(100) NULL AFTER `comments`
		');
	}

	// Change called_by to VARCHAR(100) NULL
	if ($CI->db->field_exists('called_by', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `called_by` `called_by` VARCHAR(100) NULL
		');
	}

	// Change criteria_id to VARCHAR(255) NULL
	if ($CI->db->field_exists('criteria_id', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `criteria_id` `criteria_id` VARCHAR(255) NULL
		');
	}

	// Change next_calling_date to DATE NULL
	if ($CI->db->field_exists('next_calling_date', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `next_calling_date` `next_calling_date` DATE NULL
		');
	}

	// Change appointment_type_id to VARCHAR(100) NULL
	if ($CI->db->field_exists('appointment_type_id', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `appointment_type_id` `appointment_type_id` VARCHAR(100) NULL
		');
	}

	// Change appointment_date to DATE NULL
	if ($CI->db->field_exists('appointment_date', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `appointment_date` `appointment_date` DATE NULL
		');
	}

	// Change created_date to DATETIME NULL
	if ($CI->db->field_exists('created_date', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `created_date` `created_date` DATETIME NULL
		');
	}

	// Change comments to TEXT NULL
	if ($CI->db->field_exists('comments', $call_log_table)) {
		$CI->db->query('
			ALTER TABLE `' . $call_log_table . '` 
			CHANGE COLUMN `comments` `comments` TEXT NULL
		');
	}
	
	$table = db_prefix() . 'flextestimonialresponses';
	$column = 'request_id';

	if ($CI->db->table_exists($table) && !$CI->db->field_exists($column, $table)) {
		$CI->db->query('
			ALTER TABLE `' . $table . '` 
			ADD COLUMN `' . $column . '` INT NULL AFTER `updated_at`
		');
	}



	// Check if table exists before altering
	if ($CI->db->table_exists(db_prefix() . 'clients_new_fields')) {
		$CI->db->query("
			ALTER TABLE `" . db_prefix() . "clients_new_fields` 
			CHANGE COLUMN `mr_no` `mr_no` VARCHAR(50) NULL,
			CHANGE COLUMN `salutation` `salutation` VARCHAR(11) NULL,
			CHANGE COLUMN `age` `age` INT(11) NULL,
			CHANGE COLUMN `patient_status` `patient_status` VARCHAR(255) NULL,
			CHANGE COLUMN `whatsapp_number` `whatsapp_number` VARCHAR(255) NULL,
			CHANGE COLUMN `alt_number1` `alt_number1` VARCHAR(255) NULL,
			CHANGE COLUMN `email_id` `email_id` VARCHAR(255) NULL,
			CHANGE COLUMN `marital_status` `marital_status` VARCHAR(255) NULL,
			CHANGE COLUMN `alt_number2` `alt_number2` VARCHAR(255) NULL;
		");
	}
	
	$table = db_prefix() . 'clients_new_fields';

	if ($CI->db->table_exists($table)) {

		if (!$CI->db->field_exists('current_status', $table)) {
			$CI->db->query("
				ALTER TABLE `$table` 
				ADD COLUMN `current_status` VARCHAR(45) NULL AFTER `dob`
			");
		}

		if (!$CI->db->field_exists('registration_start_date', $table)) {
			$CI->db->query("
				ALTER TABLE `$table` 
				ADD COLUMN `registration_start_date` DATE NULL AFTER `current_status`
			");
		}

		if (!$CI->db->field_exists('registration_end_date', $table)) {
			$CI->db->query("
				ALTER TABLE `$table` 
				ADD COLUMN `registration_end_date` DATE NULL AFTER `registration_start_date`
			");
		}
		
		if (!$CI->db->field_exists('dateadded', $table)) {
			$CI->db->query("
				ALTER TABLE `$table` 
				ADD COLUMN `dateadded` DATE NULL AFTER `registration_end_date`
			");
		}
		
		if (!$CI->db->field_exists('custEnquiryId', $table)) {
			$CI->db->query("
				ALTER TABLE `$table`
				ADD COLUMN `custEnquiryId` VARCHAR(45) NULL AFTER `dateadded`
			");
		}

	}
	
	if (!$CI->db->field_exists('lead_age', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `lead_age` INT NULL AFTER `lead_nature`');
	}

	if (!$CI->db->field_exists('lead_dob', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `lead_dob` DATE NULL AFTER `lead_age`');
	}

	if (!$CI->db->field_exists('lead_gender', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `lead_gender` VARCHAR(45) NULL AFTER `lead_dob`');
	}

	if (!$CI->db->field_exists('lead_priority', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `lead_priority` VARCHAR(45) NULL AFTER `lead_gender`');
	}
	if (!$CI->db->field_exists('area', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `area` VARCHAR(200) NULL AFTER `lead_priority`');
	}

	if (!$CI->db->field_exists('languages', db_prefix() . 'leads')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` 
			ADD COLUMN `languages` TEXT NULL AFTER `area`');
	}
	
	if (!$CI->db->field_exists('patient_response_id', db_prefix() . 'leads_with_doctor')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads_with_doctor` 
			ADD COLUMN `patient_response_id` INT NULL AFTER `slot_time`');
	}

	if (!$CI->db->field_exists('casesheet_id', db_prefix() . 'patient_prescription')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_prescription` 
			ADD COLUMN `casesheet_id` INT NULL AFTER `userid`');
	}
	if (!$CI->db->field_exists('received_by', db_prefix() . 'invoicepaymentrecords')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'invoicepaymentrecords` 
			ADD COLUMN `received_by` INT NULL AFTER `transactionid`');
	}
	if (!$CI->db->field_exists('treatment_start_date', db_prefix() . 'patient_treatment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `treatment_start_date` DATE NULL AFTER `treatment_followup_date`');
	}

	if (!$CI->db->field_exists('treatment_end_date', db_prefix() . 'patient_treatment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `treatment_end_date` DATE NULL AFTER `treatment_start_date`');
	}

	if (!$CI->db->field_exists('estimation_id', db_prefix() . 'patient_treatment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `estimation_id` INT NULL AFTER `treatment_end_date`');
	}

	if (!$CI->db->field_exists('estimation_created_by', db_prefix() . 'patient_treatment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `estimation_created_by` INT NULL AFTER `estimation_id`');
	}

	if (!$CI->db->field_exists('estimation_added_date', db_prefix() . 'patient_treatment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_treatment` 
			ADD COLUMN `estimation_added_date` DATETIME NULL AFTER `estimation_created_by`');
	}
	
	if (!$CI->db->field_exists('attachment', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
						ADD COLUMN `attachment` TEXT NULL AFTER `branch_id`');
	}
	if (!$CI->db->field_exists('medicine_given_by', db_prefix() . 'patient_prescription')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_prescription` 
			ADD COLUMN `medicine_given_by` INT NULL DEFAULT NULL AFTER `casesheet_id`');
	}

	if (!$CI->db->field_exists('medicine_given_date', db_prefix() . 'patient_prescription')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_prescription` 
			ADD COLUMN `medicine_given_date` DATETIME NULL DEFAULT NULL AFTER `medicine_given_by`');
	}

	if (!$CI->db->field_exists('medicine_remarks', db_prefix() . 'patient_prescription')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'patient_prescription` 
			ADD COLUMN `medicine_remarks` TEXT NULL DEFAULT NULL AFTER `medicine_given_date`');
	}

	
	if (!$CI->db->table_exists(db_prefix() . 'lead_patient_journey')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'lead_patient_journey` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`leadid` INT(11) NULL,
				`userid` INT(11) NULL,
				`status` INT(11) NULL,
				`datetime` DATETIME NULL,
				`remarks` TEXT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'doctor_availability')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'doctor_availability` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`staff_id` INT(11) NOT NULL,
				`day_of_week` ENUM("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday") NOT NULL,
				`start_time` TIME NOT NULL,
				`end_time` TIME NOT NULL,
				`time_gap_minutes` INT(11) DEFAULT 15,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	
	if (!$CI->db->table_exists(db_prefix() . 'message_log')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'message_log` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`leadid` INT(11) NULL,
				`userid` INT(11) NULL,
				`status` INT(11) NULL,
				`message_type` VARCHAR(100) DEFAULT NULL,
				`message` TEXT DEFAULT NULL,
				`response` TEXT DEFAULT NULL,
				`datetime` DATETIME DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}
	
	if (!$CI->db->field_exists('invoice_id', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `invoice_id` INT NULL DEFAULT NULL AFTER `attachment`');
	}
	if (!$CI->db->field_exists('sent_missed_notification', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `sent_missed_notification` INT NULL DEFAULT 0 AFTER `invoice_id`');
	}
	
	if (!$CI->db->field_exists('patient_source_id', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'clients_new_fields` 
			ADD COLUMN `patient_source_id` INT NULL AFTER `custEnquiryId`');
	}
	
	if (!$CI->db->field_exists('consultation_duration', db_prefix() . 'appointment')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'appointment` 
			ADD COLUMN `consultation_duration` INT NULL AFTER `sent_missed_notification`');
	}
	
	if (!$CI->db->field_exists('suggested_diagnostics_id', db_prefix() . 'patient_treatment')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_treatment`
			ADD COLUMN `suggested_diagnostics_id` INT NULL AFTER `estimation_added_date`
		');
	}
	
	if (!$CI->db->field_exists('refer_type', db_prefix() . 'leads')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'leads`
			ADD COLUMN `refer_type` VARCHAR(45) NULL AFTER `languages`
		');
	}

	if (!$CI->db->field_exists('refer_id', db_prefix() . 'leads')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'leads`
			ADD COLUMN `refer_id` INT NULL AFTER `refer_type`
		');
	}
	
	if (!$CI->db->field_exists('migrated', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'clients_new_fields` 
			ADD COLUMN `migrated` INT NOT NULL DEFAULT 0 AFTER `alt_number2`
		');
	}
	if (!$CI->db->field_exists('reg_by', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'clients_new_fields` 
			ADD COLUMN `reg_by` INT NULL AFTER `migrated`
		');
	}
	
	
	// 1. Change estimation_id to VARCHAR(100)
	if ($CI->db->field_exists('estimation_id', db_prefix() . 'patient_treatment')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_treatment`
			CHANGE COLUMN `estimation_id` `estimation_id` VARCHAR(100) NULL DEFAULT NULL
		');
	}

	// 2. Change followup_date to DATETIME NULL
	if ($CI->db->field_exists('followup_date', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'lead_call_logs`
			CHANGE COLUMN `followup_date` `followup_date` DATETIME NULL DEFAULT NULL
		');
	}

	// 3. Change next_calling_date to DATETIME NULL
	if ($CI->db->field_exists('next_calling_date', db_prefix() . 'patient_call_logs')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_call_logs`
			CHANGE COLUMN `next_calling_date` `next_calling_date` DATETIME NULL DEFAULT NULL
		');
	}

	// 4. Add pro_ownership column
	if (!$CI->db->field_exists('pro_ownership', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'clients_new_fields`
			ADD COLUMN `pro_ownership` INT NULL AFTER `reg_by`
		');
	}

	// 5. Add branch_id column
	if (!$CI->db->field_exists('branch_id', db_prefix() . 'leads')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'leads`
			ADD COLUMN `branch_id` INT NULL AFTER `refer_id`
		');
	}

	// 6. Add doctor_medicine_days column
	if (!$CI->db->field_exists('doctor_medicine_days', db_prefix() . 'casesheet')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'casesheet`
			ADD COLUMN `doctor_medicine_days` INT NULL AFTER `medicine_days`
		');
	}

	// 7. Add reg_by column (as you mentioned)
	if (!$CI->db->field_exists('reg_by', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'clients_new_fields` 
			ADD COLUMN `reg_by` INT NULL AFTER `migrated`
		');
	}
	
	if (!$CI->db->field_exists('utr_no', db_prefix() . 'invoicepaymentrecords')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'invoicepaymentrecords` 
			ADD COLUMN `utr_no` VARCHAR(45) NULL AFTER `received_by`
		');
	}
	if ($CI->db->field_exists('thirst', db_prefix() . 'casesheet')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'casesheet`
			CHANGE COLUMN `thirst` `thirst` VARCHAR(100) NULL AFTER `appetite`
		');
	}
	if (!$CI->db->field_exists('visited_date', db_prefix() . 'appointment')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'appointment`
			ADD COLUMN `visited_date` DATETIME NULL AFTER `consultation_duration`
		');
	}
	if (!$CI->db->field_exists('complaint', db_prefix() . 'casesheet')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'casesheet`
			ADD COLUMN `complaint` LONGTEXT NULL AFTER `created_at`
		');
	}
	if (!$CI->db->field_exists('complaint_migrate', db_prefix() . 'clients_new_fields')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'clients_new_fields`
			ADD COLUMN `complaint_migrate` INT NULL DEFAULT 0 AFTER `pro_ownership`
		');
	}
	
	if (!$CI->db->field_exists('pharmacy_medicine_days', db_prefix() . 'patient_call_logs')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_call_logs`
			ADD COLUMN `pharmacy_medicine_days` VARCHAR(45) NULL AFTER `better_patient`
		');
	}

	if (!$CI->db->field_exists('patient_took_medicine_days', db_prefix() . 'patient_call_logs')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'patient_call_logs`
			ADD COLUMN `patient_took_medicine_days` VARCHAR(45) NULL AFTER `pharmacy_medicine_days`
		');
	}
	
	if (!$CI->db->field_exists('advised_duration', db_prefix() . 'casesheet')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'casesheet`
			ADD COLUMN `advised_duration` VARCHAR(45) NULL AFTER `complaint`
		');
	}
	
	if (!$CI->db->field_exists('lead_alternate_number', db_prefix() . 'leads')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'leads`
			ADD COLUMN `lead_alternate_number` VARCHAR(45) NULL AFTER `branch_id`
		');
	}
	
	if (!$CI->db->field_exists('appointment_type_id', db_prefix() . 'estimates')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'estimates`
			ADD COLUMN `appointment_type_id` INT NULL DEFAULT 0 AFTER `short_link`
		');
	}

	// Add appointment_type_id to tblinvoices if not exists
	if (!$CI->db->field_exists('appointment_type_id', db_prefix() . 'invoices')) {
		$CI->db->query('
			ALTER TABLE `' . db_prefix() . 'invoices`
			ADD COLUMN `appointment_type_id` INT NULL DEFAULT 0 AFTER `short_link`
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'gt_goal')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'gt_goal` (
				`gt_goal_id` INT(11) NOT NULL AUTO_INCREMENT,
				`category` VARCHAR(100) NULL,
				`month` INT(2) NULL,
				`year` INT(4) NULL,
				`gt_goal` DOUBLE NULL,
                `gt_goal_status` VARCHAR(45) NOT NULL DEFAULT 1,
				PRIMARY KEY (`gt_goal_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}




add_option('feedback_template_name', "");
add_option('feedback_template_subject', "");
add_option('feedback_template_content', "");

add_option('feedback_sms_template_id', "");
add_option('feedback_sms_template_content', "");

add_option('feedback_whatsapp_template_name', "");
add_option('feedback_whatsapp_template_content', "");



// Appointment Created
add_option('appointment_created_template_enabled', "");
add_option('appointment_created_template_content', "");
add_option('appointment_created_sms_template_id', "");
add_option('appointment_created_sms_template_content', "");
add_option('appointment_created_whatsapp_template_name', "");
add_option('appointment_created_whatsapp_template_content', "");

// Package Accepted
add_option('package_accepted_template_enabled', "");
add_option('package_accepted_template_content', "");
add_option('package_accepted_sms_template_id', "");
add_option('package_accepted_sms_template_content', "");
add_option('package_accepted_whatsapp_template_name', "");
add_option('package_accepted_whatsapp_template_content', "");

// Package Created
add_option('package_created_template_enabled', "");
add_option('package_created_template_content', "");
add_option('package_created_sms_template_id', "");
add_option('package_created_sms_template_content', "");
add_option('package_created_whatsapp_template_name', "");
add_option('package_created_whatsapp_template_content', "");

// Patient Created
add_option('patient_created_template_enabled', "");
add_option('patient_created_template_content', "");
add_option('patient_created_sms_template_id', "");
add_option('patient_created_sms_template_content', "");
add_option('patient_created_whatsapp_template_name', "");
add_option('patient_created_whatsapp_template_content', "");

// Payment Done
add_option('payment_done_template_enabled', "");
add_option('payment_done_template_content', "");
add_option('payment_done_sms_template_id', "");
add_option('payment_done_sms_template_content', "");
add_option('payment_done_whatsapp_template_name', "");
add_option('payment_done_whatsapp_template_content', "");

// Communication Template Options Registration
add_option('appointment_created_template_enabled', "");
add_option('appointment_created_template_content', "");
add_option('appointment_created_sms_template_id', "");
add_option('appointment_created_sms_template_content', "");
add_option('appointment_created_whatsapp_template_name', "");
add_option('appointment_created_whatsapp_template_content', "");

add_option('package_accepted_template_enabled', "");
add_option('package_accepted_template_content', "");
add_option('package_accepted_sms_template_id', "");
add_option('package_accepted_sms_template_content', "");
add_option('package_accepted_whatsapp_template_name', "");
add_option('package_accepted_whatsapp_template_content', "");

add_option('package_created_template_enabled', "");
add_option('package_created_template_content', "");
add_option('package_created_sms_template_id', "");
add_option('package_created_sms_template_content', "");
add_option('package_created_whatsapp_template_name', "");
add_option('package_created_whatsapp_template_content', "");

add_option('patient_registered_template_enabled', "");
add_option('patient_registered_template_content', "");
add_option('patient_registered_sms_template_id', "");
add_option('patient_registered_sms_template_content', "");
add_option('patient_registered_whatsapp_template_name', "");
add_option('patient_registered_whatsapp_template_content', "");

add_option('medicine_followup_template_enabled', "");
add_option('medicine_followup_template_content', "");
add_option('medicine_followup_sms_template_id', "");
add_option('medicine_followup_sms_template_content', "");
add_option('medicine_followup_whatsapp_template_name', "");
add_option('medicine_followup_whatsapp_template_content', "");

add_option('payment_done_template_enabled', "");
add_option('payment_done_template_content', "");
add_option('payment_done_sms_template_id', "");
add_option('payment_done_sms_template_content', "");
add_option('payment_done_whatsapp_template_name', "");
add_option('payment_done_whatsapp_template_content', "");

add_option('call_back_template_enabled', "");
add_option('call_back_template_content', "");
add_option('call_back_sms_template_id', "");
add_option('call_back_sms_template_content', "");
add_option('call_back_whatsapp_template_name', "");
add_option('call_back_whatsapp_template_content', "");

add_option('call_feedback_template_enabled', "");
add_option('call_feedback_template_content', "");
add_option('call_feedback_sms_template_id', "");
add_option('call_feedback_sms_template_content', "");
add_option('call_feedback_whatsapp_template_name', "");
add_option('call_feedback_whatsapp_template_content', "");

add_option('missed_appointment_template_enabled', "");
add_option('missed_appointment_template_content', "");
add_option('missed_appointment_sms_template_id', "");
add_option('missed_appointment_sms_template_content', "");
add_option('missed_appointment_whatsapp_template_name', "");
add_option('missed_appointment_whatsapp_template_content', "");

add_option('reschedule_appointment_template_enabled', "");
add_option('reschedule_appointment_template_content', "");
add_option('reschedule_appointment_sms_template_id', "");
add_option('reschedule_appointment_sms_template_content', "");
add_option('reschedule_appointment_whatsapp_template_name', "");
add_option('reschedule_appointment_whatsapp_template_content', "");

add_option('not_registered_patient_day1_template_enabled', "");
add_option('not_registered_patient_day1_template_content', "");
add_option('not_registered_patient_day1_sms_template_id', "");
add_option('not_registered_patient_day1_sms_template_content', "");
add_option('not_registered_patient_day1_whatsapp_template_name', "");
add_option('not_registered_patient_day1_whatsapp_template_content', "");

add_option('not_registered_patient_week1_template_enabled', "");
add_option('not_registered_patient_week1_template_content', "");
add_option('not_registered_patient_week1_sms_template_id', "");
add_option('not_registered_patient_week1_sms_template_content', "");
add_option('not_registered_patient_week1_whatsapp_template_name', "");
add_option('not_registered_patient_week1_whatsapp_template_content', "");

add_option('medicine_appointment_template_enabled', "");
add_option('medicine_appointment_template_content', "");
add_option('medicine_appointment_sms_template_id', "");
add_option('medicine_appointment_sms_template_content', "");
add_option('medicine_appointment_whatsapp_template_name', "");
add_option('medicine_appointment_whatsapp_template_content', "");

add_option('renewal_reminder_template_enabled', "");
add_option('renewal_reminder_template_content', "");
add_option('renewal_reminder_sms_template_id', "");
add_option('renewal_reminder_sms_template_content', "");
add_option('renewal_reminder_whatsapp_template_name', "");
add_option('renewal_reminder_whatsapp_template_content', "");

add_option('clinic_closed_template_enabled', "");
add_option('clinic_closed_template_content', "");
add_option('clinic_closed_sms_template_id', "");
add_option('clinic_closed_sms_template_content', "");
add_option('clinic_closed_whatsapp_template_name', "");
add_option('clinic_closed_whatsapp_template_content', "");

add_option('doctor_changed_template_enabled', "");
add_option('doctor_changed_template_content', "");
add_option('doctor_changed_sms_template_id', "");
add_option('doctor_changed_sms_template_content', "");
add_option('doctor_changed_whatsapp_template_name', "");
add_option('doctor_changed_whatsapp_template_content', "");

add_option('employee_absence_template_enabled', "");
add_option('employee_absence_template_content', "");
add_option('employee_absence_sms_template_id', "");
add_option('employee_absence_sms_template_content', "");
add_option('employee_absence_whatsapp_template_name', "");
add_option('employee_absence_whatsapp_template_content', "");

add_option('employee_late_hr_template_enabled', "");
add_option('employee_late_hr_template_content', "");
add_option('employee_late_hr_sms_template_id', "");
add_option('employee_late_hr_sms_template_content', "");
add_option('employee_late_hr_whatsapp_template_name', "");
add_option('employee_late_hr_whatsapp_template_content', "");

add_option('uninformed_leave_hr_template_enabled', "");
add_option('uninformed_leave_hr_template_content', "");
add_option('uninformed_leave_hr_sms_template_id', "");
add_option('uninformed_leave_hr_sms_template_content', "");
add_option('uninformed_leave_hr_whatsapp_template_name', "");
add_option('uninformed_leave_hr_whatsapp_template_content', "");

add_option('new_joiner_punch_in_template_enabled', "");
add_option('new_joiner_punch_in_template_content', "");
add_option('new_joiner_punch_in_sms_template_id', "");
add_option('new_joiner_punch_in_sms_template_content', "");
add_option('new_joiner_punch_in_whatsapp_template_name', "");
add_option('new_joiner_punch_in_whatsapp_template_content', "");

add_option('monthly_attendance_confirmation_template_enabled', "");
add_option('monthly_attendance_confirmation_template_content', "");
add_option('monthly_attendance_confirmation_sms_template_id', "");
add_option('monthly_attendance_confirmation_sms_template_content', "");
add_option('monthly_attendance_confirmation_whatsapp_template_name', "");
add_option('monthly_attendance_confirmation_whatsapp_template_content', "");

add_option('uninformed_leave_employee_template_enabled', "");
add_option('uninformed_leave_employee_template_content', "");
add_option('uninformed_leave_employee_sms_template_id', "");
add_option('uninformed_leave_employee_sms_template_content', "");
add_option('uninformed_leave_employee_whatsapp_template_name', "");
add_option('uninformed_leave_employee_whatsapp_template_content', "");

add_option('daily_eod_template_enabled', "");
add_option('daily_eod_template_content', "");
add_option('daily_eod_sms_template_id', "");
add_option('daily_eod_sms_template_content', "");
add_option('daily_eod_whatsapp_template_name', "");
add_option('daily_eod_whatsapp_template_content', "");

add_option('uninformed_leave_manager_template_enabled', "");
add_option('uninformed_leave_manager_template_content', "");
add_option('uninformed_leave_manager_sms_template_id', "");
add_option('uninformed_leave_manager_sms_template_content', "");
add_option('uninformed_leave_manager_whatsapp_template_name', "");
add_option('uninformed_leave_manager_whatsapp_template_content', "");

add_option('eod_report_template_enabled', "");
add_option('eod_report_template_content', "");
add_option('eod_report_sms_template_id', "");
add_option('eod_report_sms_template_content', "");
add_option('eod_report_whatsapp_template_name', "");
add_option('eod_report_whatsapp_template_content', "");

add_option('dr_early_punch_in_template_enabled', "");
add_option('dr_early_punch_in_template_content', "");
add_option('dr_early_punch_in_sms_template_id', "");
add_option('dr_early_punch_in_sms_template_content', "");
add_option('dr_early_punch_in_whatsapp_template_name', "");
add_option('dr_early_punch_in_whatsapp_template_content', "");

add_option('dr_normal_punch_in_template_enabled', "");
add_option('dr_normal_punch_in_template_content', "");
add_option('dr_normal_punch_in_sms_template_id', "");
add_option('dr_normal_punch_in_sms_template_content', "");
add_option('dr_normal_punch_in_whatsapp_template_name', "");
add_option('dr_normal_punch_in_whatsapp_template_content', "");

add_option('leave_reconsideration_template_enabled', "");
add_option('leave_reconsideration_template_content', "");
add_option('leave_reconsideration_sms_template_id', "");
add_option('leave_reconsideration_sms_template_content', "");
add_option('leave_reconsideration_whatsapp_template_name', "");
add_option('leave_reconsideration_whatsapp_template_content', "");


add_option('enquiry_template_enabled', "");
add_option('enquiry_template_content', "");
add_option('enquiry_sms_template_id', "");
add_option('enquiry_sms_template_content', "");
add_option('enquiry_whatsapp_template_name', "");
add_option('enquiry_whatsapp_template_content', "");

add_option('call_back_template_enabled', "");
add_option('call_back_template_content', "");
add_option('call_back_sms_template_id', "");
add_option('call_back_sms_template_content', "");
add_option('call_back_whatsapp_template_name', "");
add_option('call_back_whatsapp_template_content', "");

add_option('junk_template_enabled', "");
add_option('junk_template_content', "");
add_option('junk_sms_template_id', "");
add_option('junk_sms_template_content', "");
add_option('junk_whatsapp_template_name', "");
add_option('junk_whatsapp_template_content', "");

add_option('lost_template_enabled', "");
add_option('lost_template_content', "");
add_option('lost_sms_template_id', "");
add_option('lost_sms_template_content', "");
add_option('lost_whatsapp_template_name', "");
add_option('lost_whatsapp_template_content', "");

add_option('new_template_enabled', "");
add_option('new_template_content', "");
add_option('new_sms_template_id', "");
add_option('new_sms_template_content', "");
add_option('new_whatsapp_template_name', "");
add_option('new_whatsapp_template_content', "");

add_option('no_feedback_template_enabled', "");
add_option('no_feedback_template_content', "");
add_option('no_feedback_sms_template_id', "");
add_option('no_feedback_sms_template_content', "");
add_option('no_feedback_whatsapp_template_name', "");
add_option('no_feedback_whatsapp_template_content', "");

add_option('no_response_template_enabled', "");
add_option('no_response_template_content', "");
add_option('no_response_sms_template_id', "");
add_option('no_response_sms_template_content', "");
add_option('no_response_whatsapp_template_name', "");
add_option('no_response_whatsapp_template_content', "");

add_option('on_appointment_template_enabled', "");
add_option('on_appointment_template_content', "");
add_option('on_appointment_sms_template_id', "");
add_option('on_appointment_sms_template_content', "");
add_option('on_appointment_whatsapp_template_name', "");
add_option('on_appointment_whatsapp_template_content', "");

add_option('only_consulted_template_enabled', "");
add_option('only_consulted_template_content', "");
add_option('only_consulted_sms_template_id', "");
add_option('only_consulted_sms_template_content', "");
add_option('only_consulted_whatsapp_template_name', "");
add_option('only_consulted_whatsapp_template_content', "");

add_option('paid_appointment_template_enabled', "");
add_option('paid_appointment_template_content', "");
add_option('paid_appointment_sms_template_id', "");
add_option('paid_appointment_sms_template_content', "");
add_option('paid_appointment_whatsapp_template_name', "");
add_option('paid_appointment_whatsapp_template_content', "");

add_option('prospect_template_enabled', "");
add_option('prospect_template_content', "");
add_option('prospect_sms_template_id', "");
add_option('prospect_sms_template_content', "");
add_option('prospect_whatsapp_template_name', "");
add_option('prospect_whatsapp_template_content', "");

add_option('reg._patients_template_enabled', "");
add_option('reg._patients_template_content', "");
add_option('reg._patients_sms_template_id', "");
add_option('reg._patients_sms_template_content', "");
add_option('reg._patients_whatsapp_template_name', "");
add_option('reg._patients_whatsapp_template_content', "");

add_option('visited_template_enabled', "");
add_option('visited_template_content', "");
add_option('visited_sms_template_id', "");
add_option('visited_sms_template_content', "");
add_option('visited_whatsapp_template_name', "");
add_option('visited_whatsapp_template_content', "");


// Feedback Auto Reply
add_option('feedback_auto_reply_template_enabled', "");
add_option('feedback_auto_reply_template_content', "");
add_option('feedback_auto_reply_sms_template_id', "");
add_option('feedback_auto_reply_sms_template_content', "");
add_option('feedback_auto_reply_whatsapp_template_name', "");
add_option('feedback_auto_reply_whatsapp_template_content', "");


// Refer To Us Auto Reply
add_option('refer_to_us_auto_reply_template_enabled', "");
add_option('refer_to_us_auto_reply_template_content', "");
add_option('refer_to_us_auto_reply_sms_template_id', "");
add_option('refer_to_us_auto_reply_sms_template_content', "");
add_option('refer_to_us_auto_reply_whatsapp_template_name', "");
add_option('refer_to_us_auto_reply_whatsapp_template_content', "");

// Edit Auto Reply
add_option('edit_auto_reply_template_enabled', "");
add_option('edit_auto_reply_template_content', "");
add_option('edit_auto_reply_sms_template_id', "");
add_option('edit_auto_reply_sms_template_content', "");
add_option('edit_auto_reply_whatsapp_template_name', "");
add_option('edit_auto_reply_whatsapp_template_content', "");


}

function client_uninstall()
{
    $CI = &get_instance();
	/* $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'appointment`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'clients_new_fields`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_prescription`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_activity_log`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_call_logs`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'casesheet`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient_treatment`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'lead_call_logs`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'doctor_new_fields`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'leads_with_doctor`;'); 
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'clients_new_fields_id`;'); */

}
