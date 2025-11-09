<?php

defined('BASEPATH') or exit('No direct script access allowed');

function registration_install()
{
    $CI = &get_instance();

   if (!$CI->db->table_exists(db_prefix() . 'registration_otp')) {
		$CI->db->query('
			CREATE TABLE `' . db_prefix() . 'registration_otp` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`phonenumber` VARCHAR(15) DEFAULT NULL,
				`otp_code` VARCHAR(6) DEFAULT NULL,
				`created_at` DATETIME DEFAULT NULL,
				`verified` TINYINT(1) DEFAULT 0,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
		');
	}


}

function registration_uninstall()
{
    $CI = &get_instance();
	$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'registration_otp`;');

}
