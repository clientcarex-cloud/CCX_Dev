<?php

defined('BASEPATH') or exit('No direct script access allowed');

function lead_call_log_install()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'lead_call_logs')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'lead_call_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `created_date` DATETIME NOT NULL,
                `branch_id` INT(11) NOT NULL,
                `enquired_by` INT(11) NOT NULL,
                `appointment_date` DATE NOT NULL,
                `doctor_id` INT(11) NOT NULL,
                `slot_time` VARCHAR(25) COLLATE utf8mb4_unicode_ci NOT NULL,
                `patient_response_id` INT(11) NOT NULL,
                `leads_id` INT(11) NOT NULL,
                `comments` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COLLATE=' . $CI->db->dbcollat . ';
        ');
    }
	
	  if (!$CI->db->field_exists('followup_date', db_prefix() . 'lead_call_logs')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` ADD COLUMN `followup_date` DATE NULL AFTER `comments`;');
    }

	if ($CI->db->field_exists('appointment_date', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
						CHANGE COLUMN `appointment_date` `appointment_date` DATETIME NOT NULL');
	}
	if (!$CI->db->field_exists('consultation_fee', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
                    ADD COLUMN `consultation_fee` DOUBLE NULL AFTER `followup_date`');
	}

	// Add paid_amount
	if (!$CI->db->field_exists('paid_amount', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
						ADD COLUMN `paid_amount` DOUBLE NULL AFTER `consultation_fee`');
	}

	// Add payment_status
	if (!$CI->db->field_exists('payment_status', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
						ADD COLUMN `payment_status` INT NULL AFTER `paid_amount`');
	}
	
	if (!$CI->db->field_exists('appointment_id', db_prefix() . 'lead_call_logs')) {
		$CI->db->query('ALTER TABLE `' . db_prefix() . 'lead_call_logs` 
						ADD COLUMN `appointment_id` INT NULL AFTER `payment_status`');
	}


}


function lead_call_log_uninstall()
{
    $CI = &get_instance();
	//$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'lead_call_logs`;');

}
