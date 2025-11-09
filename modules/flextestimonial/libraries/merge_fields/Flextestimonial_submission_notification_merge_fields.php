<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_submission_notification_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Testimonial Name',
                'key'       => '{testimonial_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-submission-notification',
                ],
            ],
            [
                'name'      => 'Testimonial URL',
                'key'       => '{testimonial_url}',
                'available' => [],
                'templates' => [
                    'flextestimonial-submission-notification',
                ],
            ]
        ];
    }

    /**
     * Flextestimonial event merge fields
     */
    public function format($testimonial)
    {
        $fields['{testimonial_name}']       = $testimonial['title'];
        $fields['{testimonial_url}']       = admin_url('flextestimonial/responses/' . $testimonial['slug']);
        return hooks()->apply_filters('flextestimonial_submission_notification_merge_fields', $fields, [
            'event' => $testimonial,
        ]);
    }
}
