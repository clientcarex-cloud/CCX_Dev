<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Run on module activation.
 */
function ccx_creator_install(): void
{
    $CI = &get_instance();

    if (! $CI) {
        return;
    }

    $menusTable = db_prefix() . 'ccx_creator_menus';
    $itemsTable = db_prefix() . 'ccx_creator_menu_items';

    if (! $CI->db->table_exists($menusTable)) {
        $CI->db->query("
            CREATE TABLE `{$menusTable}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `slug` VARCHAR(191) NOT NULL,
                `name` VARCHAR(191) NOT NULL,
                `icon` VARCHAR(191) DEFAULT NULL,
                `description` TEXT NULL,
                `role_access` TEXT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `created_by` INT UNSIGNED NULL,
                `updated_at` DATETIME NULL,
                `updated_by` INT UNSIGNED NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug_unique` (`slug`),
                KEY `status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    if (! $CI->db->table_exists($itemsTable)) {
        $CI->db->query("
            CREATE TABLE `{$itemsTable}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `menu_id` INT UNSIGNED NOT NULL,
                `slug` VARCHAR(191) NOT NULL,
                `name` VARCHAR(191) NOT NULL,
                `icon` VARCHAR(191) DEFAULT NULL,
                `role_access` TEXT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                `position` INT UNSIGNED NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `menu_id_index` (`menu_id`),
                KEY `status_index` (`status`),
                CONSTRAINT `fk_ccx_creator_menu_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `{$menusTable}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}

/**
 * Run on module deactivation.
 */
function ccx_creator_uninstall(): void
{
    $CI = &get_instance();

    if (! $CI) {
        return;
    }

    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'ccx_creator_menu_items' . '`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'ccx_creator_menus' . '`');
}
