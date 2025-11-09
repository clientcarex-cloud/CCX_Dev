<?php
defined('BASEPATH') OR exit('No direct script access allowed');
function format_inr($amount) {
    // Format number with Indian grouping
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

function convert_recurring_to_duration($count, $type) {
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

function convert_days_to_duration($days) {
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

function get_duration_from_date($date_string) {
    $given_date = new DateTime($date_string);
    $today = new DateTime();

    $interval = $today->diff($given_date);
    $days = $interval->days;

    return convert_days_to_duration($days);
}

function get_invoice_remaining_duration($start_date, $recurring, $recurring_type) {
    $start = new DateTime($start_date);
    $next_due = clone $start;

    // Add recurring duration to get next due date
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

    $days_remaining = $today->diff($next_due)->days;

    return convert_days_to_duration($days_remaining);
}


function app_format_money_custom($amount, $currency, $excludeSymbol = false)
{
    /**
     *  Check ewhether the amount is numeric and valid
     */

    if (!is_numeric($amount) && $amount != 0) {
        return $amount;
    }

    if (is_null($amount)) {
        $amount = 0;
    }

    /**
     * Check if currency is passed as Object from database or just currency name e.q. USD
     */
    if (is_string($currency)) {
        $dbCurrency = get_currency($currency);

        // Check of currency found in case does not exists in database
        if ($dbCurrency) {
            $currency = $dbCurrency;
        } else {
            $currency = [
                'symbol'             => $currency,
                'name'               => $currency,
                'placement'          => 'before',
                'decimal_separator'  => get_option('decimal_separator'),
                'thousand_separator' => get_option('thousand_separator'),
            ];
            $currency = (object) $currency;
        }
    }

    /**
     * Determine the symbol
     * @var string
     */
    $symbol = !$excludeSymbol ? $currency->symbol : '';

    /**
     * Check decimal places
     * @var mixed
     */
    $d = get_option('remove_decimals_on_zero') == 1 && !is_decimal($amount) ? 0 : get_decimal_places();

    /**
     * Format the amount
     * @var string
     */

    $amountFormatted = number_format($amount, $d, $currency->decimal_separator, $currency->thousand_separator);

    /**
     * Maybe add the currency symbol
     * @var string
     */
    $formattedWithCurrency = $currency->placement === 'after' ? $amountFormatted . '' . $symbol : $symbol . '' . $amountFormatted;

    return hooks()->apply_filters('app_format_money', $formattedWithCurrency, [
        'amount'         => $amount,
        'currency'       => $currency,
        'exclude_symbol' => $excludeSymbol,
        'decimal_places' => $d,
    ]);
}

function format_invoice_status_custom($status, $classes = '', $label = true)
{
    if (!class_exists('Invoices_model', false)) {
        get_instance()->load->model('invoices_model');
    }
    
    $id          = $status;
    
    $label_class = get_invoice_status_label($status);

    if ($status == Invoices_model::STATUS_UNPAID) {
        $status = _l('invoice_status_unpaid');
    } elseif ($status == Invoices_model::STATUS_PAID) {
        $status = _l('invoice_status_paid');
    } elseif ($status == Invoices_model::STATUS_PARTIALLY) {
        $status = _l('invoice_status_not_paid_completely');
    } elseif ($status == Invoices_model::STATUS_OVERDUE) {
        $status = _l('invoice_status_overdue');
    } elseif ($status == Invoices_model::STATUS_CANCELLED) {
        $status = _l('invoice_status_cancelled');
    } else {
        // status 6
        $status = _l('invoice_status_draft');
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status invoice-status-' . e($id) . '">' . e($status) . '</span>';
    }

    return e($status);
}

function get_patient_package($invoice_id) {
    $ci = &get_instance();
    $ci->load->model('client_model');
    return $ci->client_model->get_patient_package($invoice_id);
}


function format_appointment_status_custom($status, $classes = '', $label = true)
{
    $id = $status;

    switch ($status) {
        case '1':
        case 1:
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

        default:
            $label_class = 'default';
            $status_text = _l('unknown');
            break;
    }

    if ($label === true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status appointment-status-' . e($id) . '">' . e($status_text) . '</span>';
    }

    return e($status_text);
}

if (!function_exists('get_latest_estimate_dates')) {
    /**
     * Get the estimate with latest expiry date and total count for a client.
     *
     * @param int $clientid
     * @return array|null ['date' => 'Y-m-d', 'expirydate' => 'Y-m-d', 'total_estimates' => int] or null if not found
     */
    function get_latest_estimate_dates($clientid)
    {
        $CI =& get_instance();

        // Get estimate with latest expirydate
        $CI->db->select('date, expirydate');
        $CI->db->from(db_prefix() . 'estimates');
        $CI->db->where('clientid', $clientid);
        $CI->db->order_by('expirydate', 'DESC');
        $CI->db->limit(1);
        $row = $CI->db->get()->row();

        // Get total count of estimates for this client
        $CI->db->from(db_prefix() . 'estimates');
        $CI->db->where('clientid', $clientid);
        $total_count = $CI->db->count_all_results();

        if ($row) {
            return [
                'date'            => $row->date,
                'expirydate'      => $row->expirydate,
                'total_estimates' => $total_count,
            ];
        }

        return null;
    }
}

function mask_last_5_digits_1($number) {
    $reversed = strrev($number);
    $masked = '';
    $digitCount = 0;
    for ($i = 0; $i < strlen($reversed); $i++) {
        $char = $reversed[$i];
        $masked .= (ctype_digit($char) && $digitCount++ < 5) ? '*' : $char;
    }
    return strrev($masked);
}





