<?php

defined('BASEPATH') or exit('No direct script access allowed');

function patient_install()
{
    $CI = &get_instance();

	if (!$CI->db->table_exists(db_prefix() . 'patient')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'patient` (
				`userid` INT(11) NOT NULL AUTO_INCREMENT,
				`company` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`vat` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`phonenumber` VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`country` INT(11) NOT NULL DEFAULT 0,
				`city` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`zip` VARCHAR(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`state` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`address` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`website` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`datecreated` DATETIME NOT NULL,
				`active` INT(11) NOT NULL DEFAULT 1,
				`leadid` INT(11) DEFAULT NULL,
				`billing_street` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`billing_city` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`billing_state` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`billing_zip` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`billing_country` INT(11) DEFAULT 0,
				`shipping_street` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`shipping_city` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`shipping_state` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`shipping_zip` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`shipping_country` INT(11) DEFAULT 0,
				`longitude` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`latitude` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`default_language` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`default_currency` INT(11) NOT NULL DEFAULT 0,
				`show_primary_contact` INT(11) NOT NULL DEFAULT 0,
				`stripe_id` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`registration_confirmed` INT(11) NOT NULL DEFAULT 1,
				`addedfrom` INT(11) NOT NULL DEFAULT 0,
				PRIMARY KEY (`userid`),
				KEY `country` (`country`),
				KEY `leadid` (`leadid`),
				KEY `company` (`company`),
				KEY `active` (`active`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		');
	}


}

function patient_uninstall()
{
    $CI = &get_instance();
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'patient`;');
}
