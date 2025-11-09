<?php

defined('BASEPATH') or exit('No direct script access allowed');

function attendance_install()
{
    $CI = &get_instance();

   // Create attendance_staff table if not exists
	if (!$CI->db->table_exists(db_prefix() . 'attendance_staff')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'attendance_staff` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`staff_id` INT NULL,
				`punch_id` INT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	// Create attendance_auth_token table if not exists
	if (!$CI->db->table_exists(db_prefix() . 'attendance_auth_token')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'attendance_auth_token` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`token` TEXT DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}

	
}

function attendance_uninstall()
{
    $CI = &get_instance();
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'attendance_staff`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'attendance_auth_token`;');

}
