<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('flexibackup_files_backup_schedule', 1);
add_option('flexibackup_database_backup_schedule', 1);
add_option('flexibackup_remote_storage', '');
add_option('last_flexi_backup_file', "");
add_option('last_flexi_backup_database', "");

if (!$CI->db->table_exists(db_prefix() . 'flexibackup')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexibackup` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_type` varchar(255) NOT NULL,
  `backup_data` varchar(255) NOT NULL,
  `datecreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibackup`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibackup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibackup`
  ADD COLUMN `uploaded_to_remote` tinyint NOT NULL DEFAULT 0;');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibackup`  ADD COLUMN `note` MEDIUMTEXT NULL DEFAULT NULL');
}

$CI->load->library('flexibackup/flexibackup_module');
$CI->flexibackup_module->create_storage_directory();
$CI->flexibackup_module->create_email_template();

if(!option_exists(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING)){
  add_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING, FLEXIBACKUP_RETAIN_FOR_DAYS_DEFAULT_VALUE);
}
