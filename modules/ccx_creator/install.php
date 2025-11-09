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
            `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
            `last_status_change` DATETIME NULL,
            `created_by` INT(11) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `record_form_idx` (`form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        if (! $CI->db->field_exists('status', $prefix . 'ccx_creator_records')) {
            $CI->db->query("ALTER TABLE `{$prefix}ccx_creator_records` ADD `status` VARCHAR(40) NOT NULL DEFAULT 'pending' AFTER `data`");
        }
        if (! $CI->db->field_exists('last_status_change', $prefix . 'ccx_creator_records')) {
            $CI->db->query("ALTER TABLE `{$prefix}ccx_creator_records` ADD `last_status_change` DATETIME NULL AFTER `status`");
        }
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

    if (! $CI->db->table_exists($prefix . 'ccx_creator_logic')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_logic` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NOT NULL,
            `event` VARCHAR(50) NOT NULL,
            `script` MEDIUMTEXT NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `form_event_unique` (`form_id`, `event`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_approval_steps')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_approval_steps` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NOT NULL,
            `label` VARCHAR(191) NOT NULL,
            `assignee_type` VARCHAR(40) NOT NULL DEFAULT 'any',
            `assignee_value` VARCHAR(191) NULL,
            `position` INT(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `approval_form_idx` (`form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_record_steps')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_record_steps` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `record_id` INT(11) NOT NULL,
            `step_id` INT(11) NOT NULL,
            `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
            `acted_by` INT(11) NULL,
            `acted_at` DATETIME NULL,
            `comment` TEXT NULL,
            PRIMARY KEY (`id`),
            KEY `record_step_idx` (`record_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_audit_logs')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_audit_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `form_id` INT(11) NULL,
            `record_id` INT(11) NULL,
            `action` VARCHAR(100) NOT NULL,
            `details` TEXT NULL,
            `staff_id` INT(11) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `audit_form_idx` (`form_id`),
            KEY `audit_record_idx` (`record_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists($prefix . 'ccx_creator_blocks')) {
        $CI->db->query("CREATE TABLE `{$prefix}ccx_creator_blocks` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
            `description` VARCHAR(255) NULL,
            `definition` LONGTEXT NOT NULL,
            `created_by` INT(11) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
