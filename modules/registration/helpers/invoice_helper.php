<?php
defined('BASEPATH') or exit('No direct script access allowed');

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