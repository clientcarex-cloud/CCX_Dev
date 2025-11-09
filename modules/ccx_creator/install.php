<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Run on module activation.
 */
function ccx_creator_install(): void
{
    ccx_creator_run_migrations();
}

/**
 * Run on module deactivation.
 */
function ccx_creator_uninstall(): void
{
    // Intentionally keep data; comment the drop helper if you need a clean uninstall.
}

function ccx_creator_run_migrations(): void
{
    $CI = &get_instance();
    $prefix = db_prefix();

    if (! $CI->db->table_exists($prefix . 'ccx_creator_forms')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_forms` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
            `slug` VARCHAR(191) NOT NULL,
            `description` TEXT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` INT(11) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_fields')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_fields` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NOT NULL,
            `label` VARCHAR(191) NOT NULL,
            `field_key` VARCHAR(191) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `required` TINYINT(1) NOT NULL DEFAULT 0,
            `configuration` TEXT NULL,
            `position` INT(11) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `form_id_idx` (`form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_records')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_records` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NOT NULL,
            `data` LONGTEXT NULL,
            `created_by` INT(11) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `record_form_idx` (`form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_workflows')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_workflows` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `event` VARCHAR(50) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `workflow_form_idx` (`form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_workflow_actions')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_workflow_actions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `workflow_id` INT(11) NOT NULL,
            `action_type` VARCHAR(50) NOT NULL,
            `settings` TEXT NULL,
            `position` INT(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `workflow_action_idx` (`workflow_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
