<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'mailflow_email_templates')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "mailflow_email_templates` (
  `id` int(11) NOT NULL,
  `template_name` text,
  `template_subject` text,
  `template_content` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

            $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_email_templates`
  ADD PRIMARY KEY (`id`);');

            $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
        }

        if (!$CI->db->table_exists(db_prefix() . 'mailflow_unsubscribed_emails')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "mailflow_unsubscribed_emails` (
  `id` int(11) NOT NULL,
  `email` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

            $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_unsubscribed_emails`
  ADD PRIMARY KEY (`id`);');

            $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_unsubscribed_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
        }
    }
}