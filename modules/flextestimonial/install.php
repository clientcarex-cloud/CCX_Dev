<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
if (!$CI->db->table_exists(db_prefix() . 'flextestimonial')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flextestimonial` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `primary_color` varchar(255) NOT NULL,
    `background_color` varchar(255) NOT NULL,
    `enable_gradient` enum('0','1') NOT NULL DEFAULT '0',
    `active` enum('0','1') NOT NULL DEFAULT '1',
    `enable_logo` enum('0','1') NOT NULL DEFAULT '0',
    `welcome_title` varchar(255) NOT NULL,
    `welcome_message` text NOT NULL,
    `enable_video_testimonial` enum('0','1') NOT NULL DEFAULT '0',
    `enable_text_testimonial` enum('0','1') NOT NULL DEFAULT '0',
    `welcome_video_url` varchar(255) NOT NULL,
    `response_prompt` text NOT NULL,
    `enable_rating` enum('0','1') NOT NULL DEFAULT '0',
    `enable_image` enum('0','1') NOT NULL DEFAULT '0',
    `enable_email` enum('0','1') NOT NULL DEFAULT '0',
    `require_email` enum('0','1') NOT NULL DEFAULT '0',
    `enable_job_title` enum('0','1') NOT NULL DEFAULT '0',
    `require_job_title` enum('0','1') NOT NULL DEFAULT '0',
    `enable_user_photo` enum('0','1') NOT NULL DEFAULT '0',
    `require_user_photo` enum('0','1') NOT NULL DEFAULT '0',
    `enable_website_url` enum('0','1') NOT NULL DEFAULT '0',
    `require_website_url` enum('0','1') NOT NULL DEFAULT '0',
    `enable_company_name` enum('0','1') NOT NULL DEFAULT '0',
    `require_company_name` enum('0','1') NOT NULL DEFAULT '0',
    `thankyou_title` varchar(255) NOT NULL,
    `thankyou_message` text NOT NULL,
    `thankyou_video_url` varchar(255) NOT NULL,
    `thankyou_button_text` varchar(255) NOT NULL,
    `thankyou_button_url` varchar(255) NOT NULL,
    `enable_social_share` enum('0','1') NOT NULL DEFAULT '0',
    `record_a_video_button_label` varchar(255) NOT NULL,
    `write_a_testimonial_button_label` varchar(255) NOT NULL,
    `upload_image_button_label` varchar(255) NOT NULL,
    `marketing_consent_label` varchar(255) NOT NULL,
    `notification_emails` text NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

//create the table for the testimonial_responses if not exists
if (!$CI->db->table_exists(db_prefix() . 'flextestimonialresponses')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flextestimonialresponses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `testimonial_id` int(11) NOT NULL,
        `name` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `phone` varchar(255) NOT NULL,
        `job_title` varchar(255) NOT NULL,
        `company_name` varchar(255) NOT NULL,
        `website_url` varchar(255) NOT NULL,
        `user_photo` varchar(255) NOT NULL,
        `text_response` text NOT NULL,
        `rating` int(11) NOT NULL,
        `video_url` varchar(255) NOT NULL,
        `images` text NOT NULL,
        `source` varchar(255) NOT NULL,
        `ip_address` varchar(255) NOT NULL,
        `user_agent` varchar(255) NOT NULL,
        `staff_id` int(11) NOT NULL,
        `client_id` int(11) NOT NULL,
        `contact_id` int(11) NOT NULL,
        `status` varchar(255) NOT NULL,
        `featured` enum('0','1') NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    //foregin key for the testimonial_responses table
    $CI->db->query("ALTER TABLE `" . db_prefix() . "flextestimonialresponses` ADD CONSTRAINT `fk_testimonial_responses_testimonial_id` FOREIGN KEY (`testimonial_id`) REFERENCES `" . db_prefix() . "flextestimonial`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
}

flextestimonial_create_storage_directory();

//create submission notification email template
$CI->load->library('flextestimonial/flextestimonial_module');
$CI->flextestimonial_module->create_submission_notification_email_template();
$CI->flextestimonial_module->create_thank_you_email_template();
$CI->flextestimonial_module->create_testimonial_request_email_template_tickets();
$CI->flextestimonial_module->create_testimonial_request_email_template_projects();
$CI->flextestimonial_module->create_testimonial_request_email_template_invoices();