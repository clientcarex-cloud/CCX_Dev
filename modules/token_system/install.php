<?php

defined('BASEPATH') or exit('No direct script access allowed');

function token_system_install()
{
    $CI = &get_instance();
	// For display_images
	if (!$CI->db->table_exists(db_prefix() . 'display_images')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'display_images` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`display_id` int(11) NOT NULL,
				`image_path` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
		');
	}

	// For counter
	if (!$CI->db->table_exists(db_prefix() . 'counter')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'counter` (
				`counter_id` int(11) NOT NULL AUTO_INCREMENT,
				`counter_name` varchar(255) NOT NULL,
				`doctor_id` int(11) NOT NULL,
				`display_id` int(11) NOT NULL,
				`counter_status` enum(\'Available\',\'Lunch Break\',\'Emergency\') NOT NULL,
				`counter_url` varchar(255) NOT NULL,
				`auth_code` varchar(4) NOT NULL,
				PRIMARY KEY (`counter_id`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
		');
	}

	// For display_config
	if (!$CI->db->table_exists(db_prefix() . 'display_config')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'display_config` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`display_name` varchar(255) NOT NULL,
				`queue_type` enum(\'Smart\',\'Manual\') NOT NULL,
				`number_of_list` int(11) DEFAULT NULL,
				`doctor_info` tinyint(1) DEFAULT 0,
				`media_type` enum(\'Video\',\'Images\',\'None\') DEFAULT \'None\',
				`youtube_link` varchar(255) DEFAULT NULL,
				`display_features_logo` varchar(255) DEFAULT NULL,
				`display_patient_info` set(\'Image\',\'Name\',\'Token number\') DEFAULT NULL,
				`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
		');
	}
	
	if (!$CI->db->table_exists(db_prefix() . 'tokens')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'tokens` (
				`token_id` INT(11) NOT NULL AUTO_INCREMENT,
				`token_number` varchar(45) NOT NULL,
				`patient_id` INT(11) NOT NULL,
				`doctor_id` INT(11) NOT NULL,
				`date` DATE NOT NULL,
				`token_status` ENUM(\'Pending\', \'Serving\', \'Completed\', \'Recall\', \'Expired\', \'Canceled\', \'No Show\', \'Ready\', \'Delayed\') NOT NULL DEFAULT \'Pending\',
				PRIMARY KEY (`token_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		');
	}
	
	
	$table = db_prefix() . 'display_config';
	$column = 'display_patient_info';

	// Check if table exists
	if ($CI->db->table_exists($table)) {
		// Check if column exists before altering
		$fields = $CI->db->list_fields($table);
		if (in_array($column, $fields)) {
			$CI->db->query("
				ALTER TABLE `$table`
				CHANGE COLUMN `$column` `$column` 
				SET('Token number', 'Image', 'Name', 'Doctor Name', 'Status') 
				NULL DEFAULT NULL
			");
		}
	}


   

}

function token_system_uninstall()
{
    $CI = &get_instance();
	/* $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'display_config`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'counter`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'display_images`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'tokens`;'); */

}
