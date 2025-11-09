<?php

defined('BASEPATH') or exit('No direct script access allowed');

function medicine_install()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'medicine')) {
        $CI->db->query('
            CREATE TABLE `' . db_prefix() . 'medicine` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `medicine_name` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
        ');
    }

}

function medicine_uninstall()
{
    $CI = &get_instance();
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'medicine`;');
}
