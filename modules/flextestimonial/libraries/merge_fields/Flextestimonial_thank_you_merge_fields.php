<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flextestimonial_thank_you_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Testimonial Name',
                'key'       => '{testimonial_name}',
                'available' => [],
                'templates' => [
                    'flextestimonial-thank-you',
                ],
            ],
        ];
    }

    /**
     * Flextestimonial event merge fields
     */
    public function format($testimonial)
    {
        $fields['{testimonial_name}']       = $testimonial['title'];
        return hooks()->apply_filters('flextestimonial_thank_you_merge_fields', $fields, [
            'event' => $testimonial,
        ]);
    }
}
