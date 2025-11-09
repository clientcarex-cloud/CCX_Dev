<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Format number into Indian Rupee format
 */
if (!function_exists('format_inr')) {
    function format_inr($amount)
    {
        $exploded = explode('.', number_format($amount, 2, '.', ''));
        $rupees = $exploded[0];
        $paise = isset($exploded[1]) ? $exploded[1] : '00';

        $length = strlen($rupees);
        if ($length > 3) {
            $last3 = substr($rupees, -3);
            $restUnits = substr($rupees, 0, $length - 3);
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
            $formatted = $restUnits . "," . $last3;
        } else {
            $formatted = $rupees;
        }

        return $formatted . '.' . $paise;
    }
}

/**
 * Convert recurring cycle into readable duration
 */
if (!function_exists('convert_recurring_to_duration')) {
    function convert_recurring_to_duration($count, $type)
    {
        $days = 0;
        switch (strtolower($type)) {
            case 'day':
            case 'days':
                $days = $count;
                break;
            case 'week':
            case 'weeks':
                $days = $count * 7;
                break;
            case 'month':
            case 'months':
                $days = $count * 30;
                break;
            case 'year':
            case 'years':
                $days = $count * 365;
                break;
            default:
                return "Invalid recurring type";
        }
        return convert_days_to_duration($days);
    }
}

/**
 * Convert total days to year/month/day format
 */
if (!function_exists('convert_days_to_duration')) {
    function convert_days_to_duration($days)
    {
        $years = floor($days / 365);
        $days %= 365;

        $months = floor($days / 30);
        $days %= 30;

        $parts = [];
        if ($years > 0) {
            $parts[] = "$years year" . ($years > 1 ? "s" : "");
        }
        if ($months > 0) {
            $parts[] = "$months month" . ($months > 1 ? "s" : "");
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = "$days day" . ($days > 1 ? "s" : "");
        }

        return implode(", ", $parts);
    }
}

/**
 * Get duration between given date and today
 */
if (!function_exists('get_duration_from_date')) {
    function get_duration_from_date($date_string)
    {
        $given_date = new DateTime($date_string);
        $today = new DateTime();

        $interval = $today->diff($given_date);
        return convert_days_to_duration($interval->days);
    }
}

/**
 * Calculate remaining duration based on start + recurring type
 */
if (!function_exists('get_invoice_remaining_duration')) {
    function get_invoice_remaining_duration($start_date, $recurring, $recurring_type)
    {
        $start = new DateTime($start_date);
        $next_due = clone $start;

        switch (strtolower($recurring_type)) {
            case 'day':
            case 'days':
                $interval = new DateInterval('P' . $recurring . 'D');
                break;
            case 'week':
            case 'weeks':
                $interval = new DateInterval('P' . ($recurring * 7) . 'D');
                break;
            case 'month':
            case 'months':
                $interval = new DateInterval('P' . $recurring . 'M');
                break;
            case 'year':
            case 'years':
                $interval = new DateInterval('P' . $recurring . 'Y');
                break;
            default:
                return "Invalid recurring type";
        }

        $next_due->add($interval);
        $today = new DateTime();

        if ($next_due <= $today) {
            return "Expired";
        }

        return convert_days_to_duration($today->diff($next_due)->days);
    }
}

/**
 * Format money with custom currency settings
 */
if (!function_exists('app_format_money_custom')) {
    function app_format_money_custom($amount, $currency, $excludeSymbol = false)
    {
        if (!is_numeric($amount)) {
            return $amount;
        }

        if (is_null($amount)) {
            $amount = 0;
        }

        if (is_string($currency)) {
            $dbCurrency = get_currency($currency);
            $currency = $dbCurrency ?: (object)[
                'symbol'             => $currency,
                'name'               => $currency,
                'placement'          => 'before',
                'decimal_separator'  => get_option('decimal_separator'),
                'thousand_separator' => get_option('thousand_separator'),
            ];
        }

        $symbol = !$excludeSymbol ? $currency->symbol : '';
        $d = (get_option('remove_decimals_on_zero') == 1 && !is_decimal($amount)) ? 0 : get_decimal_places();

        $amountFormatted = number_format($amount, $d, $currency->decimal_separator, $currency->thousand_separator);
        $formattedWithCurrency = $currency->placement === 'after'
            ? $amountFormatted . $symbol
            : $symbol . $amountFormatted;

        return hooks()->apply_filters('app_format_money', $formattedWithCurrency, [
            'amount' => $amount,
            'currency' => $currency,
            'exclude_symbol' => $excludeSymbol,
            'decimal_places' => $d,
        ]);
    }
}

/**
 * Format invoice status
 */
if (!function_exists('format_invoice_status_custom')) {
    function format_invoice_status_custom($status, $classes = '', $label = true)
    {
        if (!class_exists('Invoices_model', false)) {
            get_instance()->load->model('invoices_model');
        }

        $id = $status;
        $label_class = get_invoice_status_label($status);

        switch ($status) {
            case Invoices_model::STATUS_UNPAID:
                $status = _l('invoice_status_unpaid');
                break;
            case Invoices_model::STATUS_PAID:
                $status = _l('invoice_status_paid');
                break;
            case Invoices_model::STATUS_PARTIALLY:
                $status = _l('invoice_status_not_paid_completely');
                break;
            case Invoices_model::STATUS_OVERDUE:
                $status = _l('invoice_status_overdue');
                break;
            case Invoices_model::STATUS_CANCELLED:
                $status = _l('invoice_status_cancelled');
                break;
            default:
                $status = _l('invoice_status_draft');
                break;
        }

        if ($label) {
            return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status invoice-status-' . e($id) . '">' . e($status) . '</span>';
        }

        return e($status);
    }
}

/**
 * Get patient package using invoice ID
 */
if (!function_exists('get_patient_package')) {
    function get_patient_package($invoice_id)
    {
        $ci = &get_instance();
        $ci->load->model('client_model');
        return $ci->client_model->get_patient_package($invoice_id);
    }
}

/**
 * Format appointment status
 */
if (!function_exists('format_appointment_status_custom')) {
    function format_appointment_status_custom($status, $classes = '', $label = true)
    {
        $id = $status;
        $status_text = $status;
        $label_class = 'default';

        switch (strtolower($status)) {
            case '1':
            case 1:
            case 'visited':
                $label_class = 'success';
                $status_text = _l('visit_status_visited');
                break;
            case 'missed':
                $label_class = 'danger';
                $status_text = _l('visit_status_missed');
                break;
            case 'upcoming':
                $label_class = 'info';
                $status_text = _l('visit_status_upcoming');
                break;
            case 'today':
                $label_class = 'warning';
                $status_text = _l('visit_status_today_confirm');
                break;
        }

        if ($label) {
            return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status appointment-status-' . e($id) . '">' . e($status_text) . '</span>';
        }

        return e($status_text);
    }
}

/**
 * Log communication message
 */
if (!function_exists('log_message_communication')) {
    function log_message_communication($data = [])
    {
        $CI = &get_instance();

        $insert_data = [
            'leadid'       => $data['leadid'] ?? null,
            'userid'       => $data['userid'] ?? null,
            'status'       => $data['status'] ?? null,
            'message_type' => $data['message_type'] ?? 'unknown',
            'message'      => $data['message'] ?? '',
            'response'     => is_array($data['response']) ? json_encode($data['response']) : $data['response'],
            'datetime'     => date('Y-m-d H:i:s'),
        ];

        $CI->db->insert(db_prefix() . 'message_log', $insert_data);
        return $CI->db->insert_id();
    }
}
