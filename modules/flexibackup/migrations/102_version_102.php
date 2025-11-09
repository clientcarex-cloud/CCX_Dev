<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {

        $CI = &get_instance();
        $table_name = 'flexibackup';


        try {
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN IF NOT EXISTS `note` MEDIUMTEXT NULL DEFAULT NULL AFTER `uploaded_to_remote`');
        } catch (Exception $e) {
            // do nothing
        }


        if (!option_exists(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING)) {
            add_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING, FLEXIBACKUP_RETAIN_FOR_DAYS_DEFAULT_VALUE);
        }
    }
}