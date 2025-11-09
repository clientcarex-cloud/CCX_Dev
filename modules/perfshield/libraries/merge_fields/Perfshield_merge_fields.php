<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Perfshield_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Internet service provider',
                'key'       => '{isp}',
                'available' => [
                    '',
                ],
                'templates' => [
                    'unrecognized-login-detected',
                    'unrecognized-login-detected-to-admin',
                    'multiple-failed-login-attempts'
                ],
            ],
            [
                'name'      => 'Ip Address',
                'key'       => '{ip_address}',
                'available' => [
                    '',
                ],
                'templates' => [
                    'unrecognized-login-detected',
                    'unrecognized-login-detected-to-admin',
                    'multiple-failed-login-attempts'
                ],
            ],
            [
                'name'      => 'Country',
                'key'       => '{country}',
                'available' => [
                    '',
                ],
                'templates' => [
                    'unrecognized-login-detected',
                    'unrecognized-login-detected-to-admin',
                    'multiple-failed-login-attempts'
                ],
            ],
        ];
    }

    /**
     * Merge field for email verification.
     *
     * @param int    $clientid
     * @param string $client_email
     *
     * @return array
     */
    public function format($loginDetails)
    {
        $fields = [];

        $fields['{isp}'] = $loginDetails['isp'];
        $fields['{ip_address}'] = $loginDetails['ip'];
        $fields['{country}']    = $loginDetails['country'];

        return $fields;
    }
}
