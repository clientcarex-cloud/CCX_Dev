<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CCX Reports
Description: Custom cross-table reporting with templates and sections.
Version: 1.0.0
Author: CCX Team
*/

define('CCX_MODULE_NAME', 'ccx');
define('CCX_MODULE_VERSION', '1.0.0');

register_activation_hook(CCX_MODULE_NAME, 'ccx_module_activation');
register_deactivation_hook(CCX_MODULE_NAME, 'ccx_module_deactivation');
register_uninstall_hook(CCX_MODULE_NAME, 'ccx_module_uninstall');

hooks()->add_action('admin_init', 'ccx_admin_init');
hooks()->add_action('app_admin_head', 'ccx_admin_head');

if (! function_exists('ccx_lang')) {
    function ccx_lang(string $key, string $default = ''): string
    {
        $translation = _l($key);

        if ($translation === $key || $translation === '') {
            return $default !== '' ? $default : $key;
        }

        return $translation;
    }
}

if (! function_exists('ccx_ensure_report_tables')) {
    function ccx_ensure_report_tables(): void
    {
        $CI = &get_instance();
        if (! $CI || ! isset($CI->db)) {
            return;
        }

        $prefix = db_prefix();

        $templatesTable = $prefix . 'ccx_report_templates';

        if (! $CI->db->table_exists($templatesTable)) {
            $CI->db->query('CREATE TABLE `' . $prefix . "ccx_report_templates` (
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

        if (! $CI->db->table_exists($prefix . 'ccx_report_template_columns')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "ccx_report_template_columns` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `template_id` INT(11) NOT NULL,
                `position` INT(11) NOT NULL DEFAULT 1,
                `label` VARCHAR(191) NOT NULL,
                `aggregate_function` VARCHAR(20) NOT NULL DEFAULT 'SUM',
                `table_name` VARCHAR(191) NOT NULL,
                `column_name` VARCHAR(191) NULL,
                `conditions` TEXT NULL,
                `decimal_places` INT(11) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `template_id` (`template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (! $CI->db->table_exists($prefix . 'ccx_report_sections')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "ccx_report_sections` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                `description` TEXT NULL,
                `display_order` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (! $CI->db->table_exists($prefix . 'ccx_report_section_templates')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "ccx_report_section_templates` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `section_id` INT(11) NOT NULL,
                `template_id` INT(11) NOT NULL,
                `display_order` INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `section_template_unique` (`section_id`, `template_id`),
                KEY `template_fk` (`template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (! $CI->db->table_exists($prefix . 'ccx_report_template_pages')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "ccx_report_template_pages` (
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
        } else {
            $fields = $CI->db->list_fields($prefix . 'ccx_report_template_pages');

            if (! in_array('html_content', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "ccx_report_template_pages` ADD `html_content` LONGTEXT NULL AFTER `sql_query`");
            }

            if (! in_array('filters', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "ccx_report_template_pages` ADD `filters` LONGTEXT NULL AFTER `html_content`");
            }

            if (! in_array('page_key', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "ccx_report_template_pages` ADD `page_key` VARCHAR(50) NOT NULL AFTER `template_id`");
            }
        }
    }
}

function ccx_admin_init()
{
    $CI = &get_instance();

    if (function_exists('register_language_files')) {
        register_language_files('ccx', ['ccx']);
    }

    ccx_ensure_report_tables();

    if (function_exists('register_staff_capabilities')) {
        $fullCapabilities = [
            'capabilities' => [
                'view'      => _l('permission_view'),
                'view_own'  => _l('permission_view_own'),
                'create'    => _l('permission_create'),
                'edit'      => _l('permission_edit'),
                'delete'    => _l('permission_delete'),
                'export'    => ccx_lang('ccx_permission_export_reports', 'Export reports'),
            ],
        ];

        register_staff_capabilities('ccx_reports', $fullCapabilities, 'CCX: ' . ccx_lang('ccx_menu_reports', 'Reports'));
        register_staff_capabilities('ccx_templates', $fullCapabilities, 'CCX: ' . ccx_lang('ccx_menu_templates', 'Report Templates'));
        register_staff_capabilities('ccx_sections', $fullCapabilities, 'CCX: ' . ccx_lang('ccx_menu_sections', 'Report Sections'));

        $importExportCaps = [
            'capabilities' => [
                'view'   => _l('permission_view'),
                'create' => _l('permission_create'),
            ],
        ];
        register_staff_capabilities('ccx_import_export', $importExportCaps, 'CCX: ' . ccx_lang('ccx_menu_import_export', 'Import/Export'));
    }

    $canViewReports      = staff_can('view', 'ccx_reports') || staff_can('view_own', 'ccx_reports');
    $canViewTemplates    = staff_can('view', 'ccx_templates') || staff_can('view_own', 'ccx_templates');
    $canViewSections     = staff_can('view', 'ccx_sections') || staff_can('view_own', 'ccx_sections');
    $canViewImportExport = staff_can('view', 'ccx_import_export');

    if (is_admin()) {
        $CI->load->model('ccx/ccx_model');
        $CI->ccx_model->ensure_sample_dynamic_template();
    }

    if (! (is_admin() || $canViewReports || $canViewTemplates || $canViewSections || $canViewImportExport)) {
        return;
    }

    $parentSlug = 'ccx';

    $CI->app_menu->add_sidebar_menu_item($parentSlug, [
        'name'     => ccx_lang('ccx_menu_title', 'CCX Reports'),
        'href'     => admin_url('ccx/reports'),
        'icon'     => 'fa fa-area-chart',
        'position' => 51,
    ]);

    if (is_admin() || $canViewReports) {
        $CI->app_menu->add_sidebar_children_item($parentSlug, [
            'slug'     => 'ccx_reports',
            'name'     => ccx_lang('ccx_menu_reports', 'Reports'),
            'href'     => admin_url('ccx/reports'),
            'icon'     => 'fa fa-bar-chart',
            'position' => 1,
        ]);
    }

    if (is_admin() || $canViewTemplates) {
        $CI->app_menu->add_sidebar_children_item($parentSlug, [
            'slug'     => 'ccx_report_templates',
            'name'     => ccx_lang('ccx_menu_templates', 'Report Templates'),
            'href'     => admin_url('ccx/templates'),
            'icon'     => 'fa fa-code',
            'position' => 2,
        ]);
    }

    if (is_admin() || $canViewSections) {
        $CI->app_menu->add_sidebar_children_item($parentSlug, [
            'slug'     => 'ccx_report_sections',
            'name'     => ccx_lang('ccx_menu_sections', 'Report Sections'),
            'href'     => admin_url('ccx/sections'),
            'icon'     => 'fa fa-object-group',
            'position' => 3,
        ]);
    }

    if (is_admin() || $canViewImportExport) {
        $CI->app_menu->add_sidebar_children_item($parentSlug, [
            'slug'     => 'ccx_import_export',
            'name'     => ccx_lang('ccx_menu_import_export', 'Import/Export'),
            'href'     => admin_url('ccx/import_export'),
            'icon'     => 'fa fa-exchange',
            'position' => 4,
        ]);
    }
}

function ccx_admin_head()
{
    // Placeholder for future CSS/JS requirements.
}

function ccx_module_activation(): void
{
    ccx_ensure_report_tables();
}

function ccx_module_deactivation(): void
{
    // No-op; reserved for future use.
}

function ccx_module_uninstall(): void
{
    $CI = &get_instance();
    if (! $CI || ! isset($CI->db)) {
        return;
    }

    $prefix = db_prefix();

    $tables = [
        'ccx_report_section_templates',
        'ccx_report_sections',
        'ccx_report_template_columns',
        'ccx_report_template_pages',
        'ccx_report_templates',
    ];

    foreach ($tables as $table) {
        $CI->db->query('DROP TABLE IF EXISTS `' . $prefix . $table . '`');
    }
}
