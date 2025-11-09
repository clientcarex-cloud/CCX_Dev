<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

    
    
    
    if (!$CI->db->table_exists(db_prefix().'_templates')) {
    $CI->db->query("CREATE TABLE ".db_prefix()."_templates (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `template_id` varchar(255) ,
    `template_name` varchar(255) ,
    `template_body` varchar(255) ,
    `created_at` varchar(255) ,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    }
    
    if (!$CI->db->table_exists(db_prefix().'_smslogs')) {
    $CI->db->query("CREATE TABLE ".db_prefix()."_smslogs (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ltemplate_id` varchar(255) ,
    `ltemplate_body` varchar(255) ,
    `ltemplate_status` varchar(255) ,
    `lnumbers` varchar(255) ,
    `lcredit_minus` varchar(255) ,
    `created_at` varchar(255) ,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    }
    
    if (!$CI->db->table_exists(db_prefix().'_smscredits')) {
    $CI->db->query("CREATE TABLE ".db_prefix()."_smscredits (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `package` varchar(255) ,
    `pcredit` varchar(255) ,
    `ccredit` varchar(255) ,
    `description` varchar(255) ,
    `created_at` varchar(255) ,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    }
	
	if ($CI->db->table_exists(db_prefix() . '_templates')) {

    // Check if column `template_status` already exists
    if (!$CI->db->field_exists('template_status', db_prefix() . '_templates')) {
        
        // Run ALTER query to add the column
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "_templates` 
            ADD COLUMN `template_status` VARCHAR(45) NULL DEFAULT 'Active' AFTER `created_at`

        ");
    }
    // Check if column `template_status` already exists
    if (!$CI->db->field_exists('constant', db_prefix() . '_templates')) {
        
        // Run ALTER query to add the column
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "_templates` 
			ADD COLUMN `constant` VARCHAR(100) NULL AFTER `template_status`

        ");
    }
}

