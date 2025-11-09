<?php

defined('BASEPATH') or exit('No direct script access allowed');

function lead_rollercoaster_install()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'lead_rollercoaster_settings')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'lead_rollercoaster_settings` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`is_enabled` TINYINT(1) DEFAULT 0,
				`selected_roles` TEXT DEFAULT NULL,
				`strategy` VARCHAR(200) DEFAULT NULL,
				`fallback_option` VARCHAR(200) DEFAULT NULL,
				`fallback_employee_id` TEXT DEFAULT NULL,
				`business_timing_from` TIME DEFAULT NULL,
				`business_timing_to` TIME DEFAULT NULL,
				`selected_sources` TEXT DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}else{
		//$table = db_prefix() . 'lead_rollercoaster_settings';
		//$CI->db->query("TRUNCATE TABLE `$table`");
	}
	
	$table = db_prefix() . 'lead_rollercoaster_settings';
	if (!$CI->db->field_exists('avoid_empty_leads', $table)) {
		$CI->db->query("ALTER TABLE `$table` ADD `avoid_empty_leads` TINYINT(1) DEFAULT 0");
	}

	if (!$CI->db->field_exists('auto_junk_leads', $table)) {
		$CI->db->query("ALTER TABLE `$table` ADD `auto_junk_leads` TINYINT(1) DEFAULT 0");
	}

	if (!$CI->db->field_exists('junk_lead_rules', $table)) {
		$CI->db->query("ALTER TABLE `$table` ADD `junk_lead_rules` TEXT DEFAULT NULL");
	}
	if (!option_exists('lead_rollercoaster_enabled')) {
		add_option('lead_rollercoaster_enabled', 1); // Enable by default on install
	} else {
		update_option('lead_rollercoaster_enabled', 1);
	}

}

function lead_rollercoaster_uninstall()
{
    $CI = &get_instance();
	
	update_option('lead_rollercoaster_enabled', 0); // Disable on deactivation
}
