<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Flexi Backup
Description: FlexiBackup is an Advance Backup/Restore & Migration Plugin.
Version: 1.0.2
Requires at least: 2.3.*
*/
require(__DIR__ . '/vendor/autoload.php');
define('FLEXIBACKUP_MODULE_NAME', 'flexibackup');
define('FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING', 'flexibackup_retain_for_days');
define('FLEXIBACKUP_RETAIN_FOR_DAYS_DEFAULT_VALUE', '');
define('FLEXIBACKUP_ONEDRIVE_CODE_SETTING', 'flexibackup_onedrive_code_setting');
define('FLEXIBACKUP_ONEDRIVE_CODE_DEFAULT_VALUE', '');
define('FLEXIBACKUP_ONEDRIVE_REDIRECT_URI', 'flexibackup/onedrive');
hooks()->add_action('after_cron_run', 'fleixbackup_perform_cron_actions');
hooks()->add_filter('module_flexibackup_action_links', 'module_flexibackup_action_links');
hooks()->add_action('admin_init', 'flexibackup_module_menu_item_collapsible');

hooks()->add_filter('numbers_of_features_using_cron_job', 'flexibackup_numbers_of_features_using_cron_job');
hooks()->add_filter('used_cron_features', 'flexibackup_used_cron_features');
function module_flexibackup_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('flexibackup') . '">' . _l('flexibackup') . '</a>';

    return $actions;
}
function flexibackup_numbers_of_features_using_cron_job($number)
{
    //$feature = get_option('auto_backup_enabled');
    $file_backup = intval(get_option('flexibackup_files_backup_schedule'));
    if ($file_backup > 1) {
        $number += 1;
    }
    $database_backup = intval(get_option('flexibackup_database_backup_schedule'));
    if ($database_backup > 1) {
        $number += 1;
    }
    return $number;
}

function flexibackup_used_cron_features($features)
{
    //$feature = get_option('auto_backup_enabled');
    $file_backup = intval(get_option('flexibackup_files_backup_schedule'));
    if ($file_backup > 1) {
        $features[] = 'Files Scheduled FlexiBackup';
    }
    $database_backup = intval(get_option('flexibackup_database_backup_schedule'));
    if ($database_backup > 1) {
        $features[] = 'Database Scheduled FlexiBackup';
    }

    return $features;
}

function fleixbackup_perform_cron_actions()
{
    $CI = &get_instance();
    $CI->load->library(FLEXIBACKUP_MODULE_NAME . '/' . 'flexibackup_module');
    $CI->flexibackup_module->perform_cron_backup();
    $CI->flexibackup_module->perform_cron_delete_backups();
}

/**
 * Database backups folder
 */
define('FLEXIBACKUP_FOLDER', FCPATH . 'flexibackup' . '/');
register_merge_fields("flexibackup/merge_fields/flexibackup_merge_fields");
/**
 * Register activation module hook
 */
register_activation_hook(FLEXIBACKUP_MODULE_NAME, 'flexibackup_module_activation_hook');

function flexibackup_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FLEXIBACKUP_MODULE_NAME, [FLEXIBACKUP_MODULE_NAME]);

hooks()->add_action('admin_init', FLEXIBACKUP_MODULE_NAME.'_module_menu_item_collapsible');

function flexibackup_module_menu_item_collapsible()
{
    //if the user is admin
    if (is_admin()) {
        $CI = &get_instance();

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => FLEXIBACKUP_MODULE_NAME,
            'name'     => 'Flexi Backup',
            'href'     => admin_url('flexibackup'),
            'position' => 55,
        ]);
    }
}

/**
 * Menu Helper
 */
function flexibackup_init_menu()
{
    $CI = &get_instance();
    $CI->load->view('partials/menu');
}

function init_backup_now_form()
{
    $CI = &get_instance();
    $CI->load->view('partials/backup-now-form');
}

function flexi_backup_file_options()
{
    $CI = &get_instance();
    $CI->load->view('partials/backup-files-options');
}

/**
 * Get Backup schedules types
 */
function get_backup_schedules_types(){
    $types = [
        [
            'key'       => 1,
            'label'      => 'flexibackup_type_manual',
        ],
        [
            'key'       => 2,
            'label'      => 'flexibackup_type_every_two_hours',
        ],
        [
            'key'       => 3,
            'label'      => 'flexibackup_type_every_four_hours',
        ],
        [
            'key'       => 4,
            'label'      => 'flexibackup_type_every_eight_hours',
        ],
        [
            'key'       => 5,
            'label'      => 'flexibackup_type_every_twelve_hours',
        ],
        [
            'key'       => 6,
            'label'      => 'flexibackup_type_daily',
        ],
        [
            'key'       => 7,
            'label'      => 'flexibackup_type_weekly',
        ],
        [
            'key'       => 8,
            'label'      => 'flexibackup_type_fortnightly',
        ],
        [
            'key'       => 9,
            'label'      => 'flexibackup_type_monthly',
        ],
    ];
    return $types;
}

/**
 * remote storage options
 */
function get_remote_storage_options(){
    $options = [
        [
            'key'       => 'ftp',
            'label'      => 'flexibackup_ftp_storage',
            'icon'=> 'assets/images/folder.png'
        ],
        [
            'key'       => 's3',
            'label'      => 'flexibackup_s3_storage',
            'icon'=> 'assets/images/s3.png'
        ],
        [
            'key'       => 'sftp',
            'label'      => 'flexibackup_sftp_storage',
            'icon'=> 'assets/images/folder.png'
        ],
        [
            'key'       => 'webdav',
            'label'      => 'flexibackup_webdav_storage',
            'icon'=> 'assets/images/webdav.png'
        ],
        [
            'key'       => 'email',
            'label'      => 'flexibackup_email',
            'icon'=> 'assets/images/email.png'
        ],
        [
            'key'       => 'google-drive',
            'label'      => 'flexibackup_google_drive',
            'icon'=> 'assets/images/googledrive.png'
        ],
        [
            'key'       => 'onedrive',
            'label'      => 'flexibackup_onedrive',
            'icon'=> 'assets/images/onedrive.png'
        ],
    ];
    return $options;
}

function flexibackup_get_onedrive_redirect_uri(){
    return admin_url(FLEXIBACKUP_ONEDRIVE_REDIRECT_URI);
}
function flexibackup_get_onedrive_scopes(){
    return [
        'files.read',
        'files.read.all',
        'files.readwrite',
        'files.readwrite.all',
        'offline_access',
    ];
}
