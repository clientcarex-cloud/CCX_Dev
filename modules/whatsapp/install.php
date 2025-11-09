<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix().'message_config')) {
	$CI->db->query("
		CREATE TABLE " . db_prefix() . "message_config (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`trigger_key` VARCHAR(100) NOT NULL,
			`template_channel` ENUM('sms', 'whatsapp') NOT NULL,
			`template_id` VARCHAR(50) DEFAULT NULL,
			`template_name` VARCHAR(100),
			`template_body` TEXT NOT NULL,
			`params_required` VARCHAR(255),
			`status` TINYINT(1) DEFAULT 1,
			`last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
	");
}
    
   


