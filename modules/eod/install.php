<?php

defined('BASEPATH') or exit('No direct script access allowed');

function eod_install()
{
    $CI = &get_instance();
	// For display_images
	if (!$CI->db->table_exists(db_prefix() . 'eod')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'eod` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`staffid` int(11) DEFAULT NULL,
				`eod_id` varchar(45) DEFAULT NULL,
				`date` datetime DEFAULT NULL,
				`branch_id` int(11) DEFAULT NULL,
				`subject` text DEFAULT NULL,
				`activity` text DEFAULT NULL,
				`today_report` text DEFAULT NULL,
				`eod_status` varchar(45) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		');
	}
 

}

function eod_uninstall()
{
    $CI = &get_instance();
	/* $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'display_config`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'counter`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'display_images`;');
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'tokens`;'); */

}
