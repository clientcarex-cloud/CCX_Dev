<?php

defined('BASEPATH') or exit('No direct script access allowed');

function ccx_install()
{
    $CI = &get_instance();

    // Remove legacy demo tables from the old module version if they still exist.
    if ($CI->db->table_exists(db_prefix() . 'ccx_students')) {
        $CI->db->query('DROP TABLE `' . db_prefix() . 'ccx_students`');
    }

    if ($CI->db->table_exists(db_prefix() . 'ccx_classes')) {
        $CI->db->query('DROP TABLE `' . db_prefix() . 'ccx_classes`');
    }

    $templatesTable = db_prefix() . 'ccx_report_templates';

    if (! $CI->db->table_exists($templatesTable)) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "ccx_report_templates` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
            `description` TEXT NULL,
            `type` VARCHAR(20) NOT NULL DEFAULT 'smart',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sql_query` LONGTEXT NULL,
            `filters` LONGTEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if ($CI->db->table_exists($templatesTable)) {
        if (! $CI->db->field_exists('type', $templatesTable)) {
            $CI->db->query('ALTER TABLE `' . $templatesTable . "` ADD `type` VARCHAR(20) NOT NULL DEFAULT 'smart' AFTER `description`");
        }

        if (! $CI->db->field_exists('is_active', $templatesTable)) {
            $CI->db->query('ALTER TABLE `' . $templatesTable . "` ADD `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `type`");
        }

        if (! $CI->db->field_exists('sql_query', $templatesTable)) {
            $CI->db->query('ALTER TABLE `' . $templatesTable . "` ADD `sql_query` LONGTEXT NULL AFTER `is_active`");
        }

        if (! $CI->db->field_exists('filters', $templatesTable)) {
            $CI->db->query('ALTER TABLE `' . $templatesTable . "` ADD `filters` LONGTEXT NULL AFTER `sql_query`");
        }
    }

    if (! $CI->db->table_exists(db_prefix() . 'ccx_report_template_columns')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "ccx_report_template_columns` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `template_id` INT(11) NOT NULL,
            `position` INT(11) NOT NULL DEFAULT 1,
            `label` VARCHAR(191) NOT NULL,
            `aggregate_function` VARCHAR(20) NOT NULL DEFAULT 'SUM',
            `table_name` VARCHAR(191) NOT NULL,
            `column_name` VARCHAR(191) NULL,
            `conditions` TEXT NULL,
            `decimal_places` INT(11) NULL,
            `mode` VARCHAR(20) NOT NULL DEFAULT 'simple',
            `formula_sources` LONGTEXT NULL,
            `formula_expression` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `template_id` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    $tableColumns = db_prefix() . 'ccx_report_template_columns';
    if ($CI->db->field_exists('table_name', $tableColumns)) {
        if (! $CI->db->field_exists('mode', $tableColumns)) {
            $CI->db->query('ALTER TABLE `' . $tableColumns . "` ADD `mode` VARCHAR(20) NOT NULL DEFAULT 'simple' AFTER `decimal_places`");
        }
        if (! $CI->db->field_exists('formula_sources', $tableColumns)) {
            $CI->db->query('ALTER TABLE `' . $tableColumns . "` ADD `formula_sources` LONGTEXT NULL AFTER `mode`");
        }
        if (! $CI->db->field_exists('formula_expression', $tableColumns)) {
            $CI->db->query('ALTER TABLE `' . $tableColumns . "` ADD `formula_expression` TEXT NULL AFTER `formula_sources`");
        }
    }

    if (! $CI->db->table_exists(db_prefix() . 'ccx_report_sections')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "ccx_report_sections` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
            `description` TEXT NULL,
            `display_order` INT(11) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists(db_prefix() . 'ccx_report_section_templates')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "ccx_report_section_templates` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `section_id` INT(11) NOT NULL,
            `template_id` INT(11) NOT NULL,
            `display_order` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `section_template_unique` (`section_id`, `template_id`),
            KEY `template_fk` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (! $CI->db->table_exists(db_prefix() . 'ccx_report_template_pages')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "ccx_report_template_pages` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `template_id` INT(11) NOT NULL,
            `page_key` VARCHAR(50) NOT NULL,
            `sql_query` LONGTEXT NULL,
            `html_content` LONGTEXT NULL,
            `filters` LONGTEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `template_page_unique` (`template_id`, `page_key`),
            KEY `template_idx` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    log_activity('CCX module installed (report designer tables ensured)');
}

function ccx_uninstall()
{
    log_activity('CCX module uninstalled');
}
