<?php

defined('BASEPATH') or exit('No direct script access allowed');

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

if (!$CI->db->table_exists(db_prefix() . 'mailflow_newsletter_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "mailflow_newsletter_history` (
  `id` int(11) NOT NULL,
  `sent_by` text,
  `email_subject` text,
  `email_content` text,
  `sms_content` text,
  `total_emails_to_send` text,
  `total_sms_to_send` text,
  `email_list` text,
  `sms_list` text,
  `emails_sent` text,
  `sms_sent` text,
  `emails_failed` text,
  `sms_failed` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_newsletter_history`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_newsletter_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
  
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_email_templates` 
  ADD COLUMN `sms_template_id` VARCHAR(100) NULL AFTER `created_at`,
  ADD COLUMN `sms_template_content` TEXT NULL AFTER `sms_template_id`,
  ADD COLUMN `whatsapp_template_name` VARCHAR(100) NULL AFTER `sms_template_content`,
  ADD COLUMN `whatsapp_template_content` TEXT NULL AFTER `whatsapp_template_name`');

}

$table = db_prefix() . 'mailflow_email_templates';

// Check and add `sms_template_id` column
if (!$CI->db->field_exists('sms_template_id', $table)) {
    $CI->db->query("ALTER TABLE `$table` ADD COLUMN `sms_template_id` VARCHAR(100) NULL AFTER `created_at`");
}

// Check and add `sms_template_content` column
if (!$CI->db->field_exists('sms_template_content', $table)) {
    $CI->db->query("ALTER TABLE `$table` ADD COLUMN `sms_template_content` TEXT NULL AFTER `sms_template_id`");
}

// Check and add `whatsapp_template_name` column
if (!$CI->db->field_exists('whatsapp_template_name', $table)) {
    $CI->db->query("ALTER TABLE `$table` ADD COLUMN `whatsapp_template_name` VARCHAR(100) NULL AFTER `sms_template_content`");
}

// Check and add `whatsapp_template_content` column
if (!$CI->db->field_exists('whatsapp_template_content', $table)) {
    $CI->db->query("ALTER TABLE `$table` ADD COLUMN `whatsapp_template_content` TEXT NULL AFTER `whatsapp_template_name`");
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

if (!$CI->db->table_exists(db_prefix() . 'mailflow_smtp_integrations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "mailflow_smtp_integrations` (
  `id` int(11) NOT NULL,
  `name` text,
  `email_encryption` text,
  `smtp_host` text,
  `smtp_port` text,
  `email` text,
  `smtp_username` text,
  `smtp_password` text,
  `email_charset` text,
  `bcc_all_emails_to` text,
  `can_delete` int default 1,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_smtp_integrations`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_smtp_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'mailflow_scheduled_campaigns')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "mailflow_scheduled_campaigns` (
  `id` int(11) NOT NULL,
  `scheduled_by` text,
  `email_subject` text,
  `email_content` text,
  `email_list` text,
  `email_smtp` text,
  `sms_content` text,
  `sms_list` text,
  `scheduled_to` text,
  `campaign_status` int default 0,
  `scheduled_at` datetime,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_scheduled_campaigns`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailflow_scheduled_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

$CI->db->query("INSERT INTO `".db_prefix()."mailflow_smtp_integrations` (`name`, `email_encryption`, `smtp_host`, `smtp_port`, `email`, `smtp_username`, `smtp_password`, `email_charset`, `bcc_all_emails_to`, `can_delete`, `created_at`) VALUES ('Amazon SES', 'tls','email-smtp.us-east-1.amazonaws.com','587','Amazon SES Secret ID', 'Amazon SES Secret ID', null, 'utf-8', null, 1, '2024-09-14 15:38:34');");
$CI->db->query("INSERT INTO `".db_prefix()."mailflow_smtp_integrations` (`name`, `email_encryption`, `smtp_host`, `smtp_port`, `email`, `smtp_username`, `smtp_password`, `email_charset`, `bcc_all_emails_to`, `can_delete`, `created_at`) VALUES ('Mailchimp', 'tls','smtp.mandrillapp.com','587','smtp@domain.com', 'smtp@domain.com', null, 'utf-8', null, 1, '2024-09-14 15:38:34');");
$CI->db->query("INSERT INTO `".db_prefix()."mailflow_smtp_integrations` (`name`, `email_encryption`, `smtp_host`, `smtp_port`, `email`, `smtp_username`, `smtp_password`, `email_charset`, `bcc_all_emails_to`, `can_delete`, `created_at`) VALUES ('Sendgrid', 'tls','smtp.sendgrid.net','587','apikey', 'apikey', null, 'utf-8', null, 1, '2024-09-14 15:38:34');");
$CI->db->query("INSERT INTO `".db_prefix()."mailflow_smtp_integrations` (`name`, `email_encryption`, `smtp_host`, `smtp_port`, `email`, `smtp_username`, `smtp_password`, `email_charset`, `bcc_all_emails_to`, `can_delete`, `created_at`) VALUES ('Mailgun', 'tls','smtp.mailgun.org','587','username@domain.com', 'username@domain.com', null, 'utf-8', null, 1, '2024-09-14 15:38:34');");


// === mailflow_newsletter_history columns ===
$table1 = db_prefix() . 'mailflow_newsletter_history';

if (!$CI->db->field_exists('whatsapp_template_content', $table1)) {
    $CI->db->query("ALTER TABLE `$table1` ADD COLUMN `whatsapp_template_content` TEXT NULL AFTER `created_at`");
}
if (!$CI->db->field_exists('total_whatsapp_to_send', $table1)) {
    $CI->db->query("ALTER TABLE `$table1` ADD COLUMN `total_whatsapp_to_send` TEXT NULL AFTER `whatsapp_template_content`");
}
if (!$CI->db->field_exists('whatsapp_sent', $table1)) {
    $CI->db->query("ALTER TABLE `$table1` ADD COLUMN `whatsapp_sent` TEXT NULL AFTER `total_whatsapp_to_send`");
}
if (!$CI->db->field_exists('whatsapp_list', $table1)) {
    $CI->db->query("ALTER TABLE `$table1` ADD COLUMN `whatsapp_list` TEXT NULL AFTER `whatsapp_sent`");
}
if (!$CI->db->field_exists('whatsapp_failed', $table1)) {
    $CI->db->query("ALTER TABLE `$table1` ADD COLUMN `whatsapp_failed` TEXT NULL AFTER `whatsapp_list`");
}

// === mailflow_scheduled_campaigns columns ===
$table2 = db_prefix() . 'mailflow_scheduled_campaigns';

if (!$CI->db->field_exists('whatsapp_template_content', $table2)) {
    $CI->db->query("ALTER TABLE `$table2` ADD COLUMN `whatsapp_template_content` TEXT NULL AFTER `created_at`");
}
if (!$CI->db->field_exists('total_whatsapp_to_send', $table2)) {
    $CI->db->query("ALTER TABLE `$table2` ADD COLUMN `total_whatsapp_to_send` TEXT NULL AFTER `whatsapp_template_content`");
}
if (!$CI->db->field_exists('whatsapp_sent', $table2)) {
    $CI->db->query("ALTER TABLE `$table2` ADD COLUMN `whatsapp_sent` TEXT NULL AFTER `total_whatsapp_to_send`");
}
if (!$CI->db->field_exists('whatsapp_list', $table2)) {
    $CI->db->query("ALTER TABLE `$table2` ADD COLUMN `whatsapp_list` TEXT NULL AFTER `whatsapp_sent`");
}
if (!$CI->db->field_exists('whatsapp_failed', $table2)) {
    $CI->db->query("ALTER TABLE `$table2` ADD COLUMN `whatsapp_failed` TEXT NULL AFTER `whatsapp_list`");
}
