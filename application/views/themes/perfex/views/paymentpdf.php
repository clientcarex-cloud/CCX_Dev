<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();
$left_margin = $dimensions['lm']; // Default left margin
$right_margin = $dimensions['rm']; // Default right margin
$page_width = $dimensions['wk'];   // Page width (excluding margins)

// Define specific margins to match the image's overall layout, if different from default
// You might need to adjust these based on your PDF library's default page margins
$content_left_margin = 10; // Adjusted left margin for content area
$content_right_margin = 10; // Adjusted right margin for content area
$content_width = $page_width - $content_left_margin - $content_right_margin;

// Set default font for the entire document
$pdf->SetFont($font_name, '', $font_size); // Assuming $font_name and $font_size are defined
$pdf->SetTextColor(50, 50, 50); // Darker grey for general text

// --- HEADER SECTION ---
$header_y_start = 10; // Starting Y for the header
$pdf->setY($header_y_start);
$pdf->setX($content_left_margin);

// Left side of header (Logo and Hospital Name)
$logo_url = get_admin_header_logo_url();
//$logo_url = 'http://localhost/uploads/company/59b9349a858cffc9c47d703c78fbfe2c.png';
$logo_width = 30; // Adjusted width for the logo
$hospital_name_x = $content_left_margin + $logo_width + 5; // X position for hospital name
$hospital_name_width = 50; // Width for hospital name text

// Add Logo
$pdf->Image($logo_url, $content_left_margin, $header_y_start, $logo_width, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

// Right side of header (Branch Info - DYNAMIC)
$branch_info_width = 75; // Adjusted width for branch info block
$branch_info_x = $page_width - $content_right_margin - $branch_info_width;
$pdf->SetX($branch_info_x);
$pdf->SetFont($font_name, 'B', 12);
$pdf->SetTextColor(50, 50, 50);

// Assuming 'TEST BRANCH' is a dynamic value, possibly from invoice_data or a branch config
$branch_name = get_option('invoice_company_name');
$pdf->Cell($branch_info_width, 0, $branch_name, 0, 1, 'R', 0, '', 0);

$pdf->SetX($branch_info_x);
$pdf->SetFont($font_name, '', 10);

// --- DYNAMIC BRANCH ADDRESS AND PHONE ---
// Get company info format and replace placeholders
$format = get_option('company_info_format');
$vat    = get_option('company_vat');

// Use the provided format replacement logic
$formatted_company_info = _info_format_replace('company_name', '<b style="color:black" class="company-name-formatted">' . get_option('invoice_company_name') . '</b>', $format);
$formatted_company_info = _info_format_replace('address', get_option('invoice_company_address'), $formatted_company_info);
$formatted_company_info = _info_format_replace('city', get_option('invoice_company_city'), $formatted_company_info);
$formatted_company_info = _info_format_replace('state', get_option('company_state'), $formatted_company_info);
$formatted_company_info = _info_format_replace('zip_code', get_option('invoice_company_postal_code'), $formatted_company_info);
$formatted_company_info = _info_format_replace('country_code', get_option('invoice_company_country_code'), $formatted_company_info);
$formatted_company_info = _info_format_replace('phone', get_option('invoice_company_phonenumber'), $formatted_company_info);
$formatted_company_info = _info_format_replace('vat_number', $vat, $formatted_company_info);
$formatted_company_info = _info_format_replace('vat_number_with_label', $vat == '' ? '':_l('company_vat_number') . ': ' . $vat, $formatted_company_info);

// Extract just the address and phone number
// This is a heuristic and might need adjustment based on the actual format string.
// We'll assume the address and phone are available directly or can be extracted.
$company_address_text = get_option('invoice_company_address');
$company_phone_text = get_option('invoice_company_phonenumber');

// If the format string includes line breaks or specific ordering, you might reconstruct it.
// Example: Assuming the format string is typically 'address<br>city, state zip<br>phone'
// For the specific image, it seems to be just address and phone.
$full_branch_address_phone = $company_address_text;

// Add phone number on a new line if available
if (!empty($company_phone_text)) {
    // Check if the address already contains the phone to avoid duplication
    if (strpos($full_branch_address_phone, $company_phone_text) === false) {
        $full_branch_address_phone .= "\n" . $company_phone_text . '(24X7)'; // Adding (24X7) as per image
    }
} else {
    // If phone is not dynamic, use the hardcoded one from the image
    $full_branch_address_phone .= "\n" . '+91-7671007000(24X7)';
}

// Ensure proper line breaks for MultiCell
$full_branch_address_phone = str_replace('<br>', "\n", $full_branch_address_phone);
$full_branch_address_phone = str_replace('<br/>', "\n", $full_branch_address_phone);
$full_branch_address_phone = strip_tags($full_branch_address_phone); // Remove any HTML tags if present

$pdf->MultiCell($branch_info_width, 0, $full_branch_address_phone, 0, 'R', 0, 1, '', '', true);

$pdf->Ln(6); // Space after header

// --- "RECEIPT" TITLE ---
$pdf->SetX($content_left_margin);
$pdf->SetFont($font_name, 'B', 20); // Larger, bolder font for main title
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell($content_width, 0, _l('receipt'), 0, 1, 'C', 0, '', 0);
$pdf->Line($content_left_margin, $pdf->getY() + 1, $page_width - $content_right_margin, $pdf->getY() + 1, ['color' => [150, 150, 150]]); // Horizontal line below title
$pdf->Ln(6); // Space after title and line

// --- PATIENT DETAILS SECTION ---
$pdf->SetFont($font_name, '', $font_size);
$pdf->SetTextColor(0, 0, 0);

$payments = $payment->invoice_data->payments;
$registration_start_date = $payment->invoice_data->client->registration_start_date;
$registration_end_date = $payment->invoice_data->client->registration_end_date;

$total_package = $payment->invoice_data->total;
$current_transaction_id = $payment->transactionid; // from current receipt

// Sort by date, then by payment ID
usort($payments, function($a, $b) {
    $dateCompare = strtotime($a['daterecorded']) <=> strtotime($b['daterecorded']);
    if ($dateCompare === 0) {
        return $a['id'] <=> $b['id']; // fallback: smaller ID is earlier
    }
    return $dateCompare;
});

$total_package_paid = 0;
foreach ($payments as $payment_data) {
    $total_package_paid += (float)$payment_data['amount'];

    // Stop when we reach the current payment
    if ($payment_data['transactionid'] === $current_transaction_id) {
        break;
    }
}

$total_package_due = $total_package - $total_package_paid;



/* echo "<pre>";
print_r($payment);
exit(); */

// Get dynamic patient data (reusing variables from old code structure)
$client_name = '';
if (isset($payment->invoice_data->client->company)) {
    $client_name .= strtoupper($payment->invoice_data->client->company);
}

$client_mobile = isset($payment->invoice_data->client->phonenumber) ? $payment->invoice_data->client->phonenumber : 'N/A';

// Assuming 'Visit ID' is a custom field for the client/invoice
// You might need to adjust 'custom_fields' key based on your CRM's implementation
$visit_id = '';
if (isset($payment->invoice_data->client->custom_fields['cfc_1'])) { // Example custom field
    $visit_id = $payment->invoice_data->client->custom_fields['cfc_1'];
} elseif (isset($payment->invoice_data->client->mr_no)) { // Check if it's directly on invoice data
    $visit_id = $payment->invoice_data->client->mr_no;
} else {
    $visit_id = 'N/A'; // Default if not found
}
// Branch name for patient details
$branch_name_patient_details = isset($payment->invoice_data->payment_branch_name) ? strtoupper($payment->invoice_data->payment_branch_name) : 'TEST BRANCH';
$payment_date_formatted = _d($payment->date); // Already formatted


$label_width = 25; // Width for labels like "Name", "Mobile No"
$value_width = 60; // Width for the values
$gap_between_columns = 10; // Space between left and right columns

// Row 1: Name and MR.No
$pdf->SetX($content_left_margin);

$pdf->Ln(2); // Space after patient details

// Define label width & value width for this block
$label_width = 40; // Adjust as needed
$value_width = 60; // Adjust as needed

// Common font settings
$pdf->SetFont($font_name, 'B', $font_size);

$pdf->Ln(2); // Space before block

// Column widths
$label_width = 35;
$value_width = 50;
$gap_between_columns = 20;

// Get the first item's description safely
$first_item_desc = '';
if (isset($payment->invoice_data->items[0]['description'])) {
    $first_item_desc = $payment->invoice_data->items[0]['description'];
}

// Row 1: Name & Registration ID
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Name', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, html_entity_decode($client_name), 0, 0, 'L');

if($first_item_desc != "Consultation Fee"){
	$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
	$pdf->SetFont($font_name, 'B', $font_size);
	$pdf->Cell($label_width, 0, 'Registration ID', 0, 0, 'L');
	$pdf->SetFont($font_name, '', $font_size);
	$pdf->Cell(3, 0, ':', 0, 0, 'C');
	$pdf->Cell($value_width, 0, $visit_id, 0, 1, 'L');

	$pdf->Ln(3);
}else{
	$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
	$pdf->SetFont($font_name, 'B', $font_size);
	//$pdf->Cell($label_width, 0, 'Registration ID', 0, 0, 'L');
	$pdf->SetFont($font_name, '', $font_size);
	//$pdf->Cell(3, 0, ':', 0, 0, 'C');
	//$pdf->Cell($value_width, 0, $visit_id, 0, 1, 'L');

	$pdf->Ln(8);
}


// Row 2: Mobile No & Branch
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Mobile No', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, $client_mobile, 0, 0, 'L');

$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Branch', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, $branch_name_patient_details, 0, 1, 'L');

$pdf->Ln(3);

if($first_item_desc != "Consultation Fee"){
// Row 3: Reg Date & Reg End Date
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Reg Date', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, _d($registration_start_date), 0, 0, 'L');

$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Reg End Date', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, _d($payment->invoice_data->duedate), 0, 1, 'L');

$pdf->Ln(3);
}

// Row 4: Package & Paid Amount
$pdf->SetFont($font_name, 'B', $font_size);


// If it's Consultation Fee, display that; otherwise, display Package
$label_text = ($first_item_desc === 'Consultation Fee') ? 'Consultation Fee' : 'Package Amount';

// Output to PDF
$pdf->Cell($label_width, 0, $label_text, 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, number_format($total_package), 0, 0, 'L');

$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Paid Amount', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, number_format($payment->amount), 0, 1, 'L');

$pdf->Ln(3);

// Row 5: Due Amount & Payment Date
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Package Due Amt.', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, number_format($total_package_due), 0, 0, 'L');

$pdf->SetX($content_left_margin + $label_width + 3 + $value_width + $gap_between_columns);
$pdf->SetFont($font_name, 'B', $font_size);
$pdf->Cell($label_width, 0, 'Payment Date', 0, 0, 'L');
$pdf->SetFont($font_name, '', $font_size);
$pdf->Cell(3, 0, ':', 0, 0, 'C');
$pdf->Cell($value_width, 0, $payment_date_formatted, 0, 1, 'L');

$pdf->Ln(4); // Space after block



// --- ITEMS TABLE ---
// Assuming only one item per payment based on the image and original code.
// If multiple items are possible, this part needs a loop over $payment->invoice_data->items or similar.
$receipt_no = isset($payment->invoiceid) ? $payment->invoiceid : 'N/A'; // Using transactionid for Receipt No
$payment_method_name = $payment->name;
if (!empty($payment->paymentmethod)) {
    $payment_method_name = $payment->paymentmethod; // As per image "Cash"
}

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0.5" style="border-collapse:collapse;">
    <thead>
        <tr bgcolor="#f2f2f2" style="color:#030712; font-weight: bold;">
            <th width="10%" align="left">S.No</th>
            <th width="30%" align="left">Receipt No</th>
            <th width="30%" align="left">Payment Type</th>
            <th width="30%" align="right">Amount Paid</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="10%" align="left">1</td>
            <td width="30%" align="left">' . $receipt_no . '</td>
            <td width="30%" align="left">' . $payment_method_name . '</td>
            <td width="30%" align="right">' . app_format_money($payment->amount, $payment->invoice_data->currency_name) . '</td>
        </tr>
    </tbody>
    <!--<tfoot>
        <tr>
            <td colspan="3" align="right" style="font-weight: bold;">Grand Total :</td>
            <td align="right" style="font-weight: bold;">' . app_format_money($payment->amount, $payment->invoice_data->currency_name) . '</td>
        </tr>
    </tfoot>-->
</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, $content_left_margin);
$pdf->Ln(1);

// --- IN WORDS ---
$amount_in_words_text = '';
if (function_exists('numberToWords')) {
	$amount = (int)$payment->amount;
    $amount_in_words_text = numberToWords($amount);
    // Add currency suffix, assuming 'INR' for Rupees
    // You might need to map currency codes to their full names (e.g., 'INR' to 'Rupees')
    $currency_suffix = 'Rupees'; // Default to Rupees for the image context
    if (isset($payment->invoice_data->currency_name)) {
        // You'll need a mapping for currency codes to human-readable names
        $currency_name_map = [
            'INR' => 'Rupees',
            'USD' => 'Dollars',
            'EUR' => 'Euros',
            // Add more as needed
        ];
        $currency_suffix = isset($currency_name_map[strtoupper($payment->invoice_data->currency_name)]) ? $currency_name_map[strtoupper($payment->invoice_data->currency_name)] : 'Units';
    }
    $amount_in_words_text = ucfirst($amount_in_words_text) . ' ' . $currency_suffix . ' Only';

} else {
    // Fallback if numberToWords is not available
    $amount_in_words_text = 'Amount in words: ' . app_format_money($payment->amount, $payment->invoice_data->currency_name) . ' Only (function missing)';
    // Consider logging an error here if `numberToWords` is critical.
}

$pdf->SetX($content_left_margin);
$pdf->SetFont($font_name, 'I', $font_size); // Italic font
$pdf->Cell(0, 0, 'In words:- ' . $amount_in_words_text, 0, 1, 'L', 0, '', 0);
$pdf->Ln(1);
$pdf->Line($content_left_margin, $pdf->getY() + 1, $page_width - $content_right_margin, $pdf->getY() + 1, ['color' => [150, 150, 150]]);
$pdf->Ln(2);
// --- TERMS & CONDITIONS SECTION ---
$pdf->SetX($content_left_margin);
$pdf->SetFont($font_name, 'B', 14);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell($content_width, 0, 'TERMS & CONDITIONS', 0, 1, 'C', 0, '', 0);
$pdf->Line($content_left_margin + ($content_width / 2) - 30, $pdf->getY() + 3, $content_left_margin + ($content_width / 2) + 30, $pdf->getY() + 3, ['color' => [000, 000, 000]]); // Underline for title
$pdf->Ln(8);

$pdf->SetFont($font_name, 'L', 10);  // 'B' for Bold
$pdf->SetTextColor(0, 0, 0);   // Slightly darker than your current 80,80,80

$terms_html = '<ol style="list-style-type: upper-alpha; margin: 0; padding: 0 0 0 0px; text-indent: 0; margin-left: 0;">
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">The facilities of joining the card includes any number of consultations with physician. Only the bearer can avail the facilities of the card.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">The card facilities are given only to the one on whose name the card is made.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">The fee is non transferable, non refundable and non extendable.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">Patients are strictly advised to use medicines as per attending physicians recommendation. We assume patients have the responsibility to inform the attending physician about the status of the health or any serious disorder during the course of treatment.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">We expect & would appreciate patients to visit the clinic as per the due date of their consultations.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">If patient is requested to co-operate with the mode of treatment, as sometimes the speed of recovery is slow (the time of recovery may vary).</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">The duration of treatment and results may vary from patient.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">The Doctor and the clinic has given no guarantee to me (Patient) about the results and duration of the treatment.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">During critical emergencies patients / attendants are advised to inform the attending physician.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">I have Sheet Received (Digital) and kept with the Doctor (in Server) till the end of the course of th treatment.</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">This Corporate Clinic, promises to provide Best Service and Treatment to all Patients</li>
    <li style="font-size: 11pt; font-weight: 900; margin-left: 0; padding-left: 0;">All disputes are subject to Hyderabad Court Jurisdication only E&OE.</li>
</ol>';

$current_y_for_terms_box = $pdf->getY();
//$pdf->SetX($content_left_margin);
$x_position = 0; // Negative value for left adjustment
$pdf->writeHTMLCell(
    $content_width, 
    '', 
    $x_position,  // This is the X position parameter (3rd parameter)
    $current_y_for_terms_box, 
    $terms_html, 
    0, 1, false, true, 'L', true
);

// Optionally, draw a light border around the terms for visual effect
//$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(220, 220, 220)));
//$pdf->RoundedRect($content_left_margin, $current_y_for_terms_box - 5, $content_width, $pdf->getY() - $current_y_for_terms_box + 10, 2, '1111', 'D', array(240, 240, 240)); // Light background and border

$pdf->Ln(10);

// --- SIGNATURE SECTION ---
$pdf->SetFont($font_name, '', $font_size);
$pdf->SetTextColor(50, 50, 50);

$signature_area_y = $pdf->getY();
$half_width = ($content_width / 2) - 5; // Half width with a small gap in middle

// Get dynamic cashier info
$cashier_name = 'TRAINEE EMPLOYEE'; // Default placeholder
$cashier_id = '123456'; // Default placeholder
if (isset($payment->received_by) && function_exists('get_staff_full_name')) {
    $staff = get_staff_full_name($payment->received_by);
    if ($staff) {
        $cashier_name = $staff;
        // If staff ID is stored in staff data
         $cashier_id = isset($payment->received_by) ? $payment->received_by : 'N/A';
    }
}
$patient_mobile_display = 'Mobile: ' . $client_mobile; // From patient details above


// Patient/Attendants Signature (Left)
$pdf->SetX($content_left_margin);
$pdf->SetFont($font_name, '', 14);
$pdf->Cell($half_width, 0, 'Patient / Attendants Signature', 0, 1, 'L', 0, '', 0); // Top border for line
$pdf->Ln(5); // Keep spacing for patient signature line
$pdf->SetX($content_left_margin);
$pdf->Cell($half_width, 0, $patient_mobile_display, 0, 0, 'L', 0, '', 0);

// Cashier Info (Right)
$pdf->SetY($signature_area_y); // Align Y with patient signature
$pdf->SetX($content_left_margin + $half_width + 10); // Position for right section (half_width + gap)

// Removed 'T' from Cell to remove the top border (horizontal line)
$pdf->Cell($half_width, 0, 'Authorized Signatory', 0, 0, 'R', 0, '', 0);
$pdf->Ln(8); // This line break still exists, move to the next line after the signatory text
$pdf->SetX($content_left_margin + $half_width + 10);
$pdf->Cell($half_width, 0, 'Cashier Name: ' . $cashier_name, 0, 0, 'R', 0, '', 0);
$pdf->Ln(5);
$pdf->SetX($content_left_margin + $half_width + 10);
$pdf->Cell($half_width, 0, 'Cashier ID: ' . $cashier_id, 0, 0, 'R', 0, '', 0);

$pdf->Ln(8); // Changed from Ln(10) to Ln(5) to reduce vertical spacing after Cashier ID

// --- FOOTER ---
$pdf->SetX($content_left_margin);
$pdf->SetFont($font_name, '', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->Line($content_left_margin, $pdf->getY(), $page_width - $content_right_margin, $pdf->getY(), ""); // Light line above footer
$pdf->Ln(3);

// Dynamic footer address and contact (reusing variables or default values)
$footer_address = 'Hyderabad, Telangana, India'; // Hardcoded as per image, assuming not dynamic from config
$footer_contact_email = 'patientsupport@drcarehospitals.com'; // Hardcoded as per image
$footer_contact_phone = '+91-7671007000(24X7)'; // Hardcoded as per image

$pdf->Cell($content_width, 0, $footer_address, 0, 1, 'C', 0, '', 0);
$pdf->Cell($content_width, 0, 'Email:' . $footer_contact_email . ' Mobile: ' . $footer_contact_phone, 0, 1, 'C', 0, '', 0);

// The original payment summary and table are replaced by the detailed receipt above.
// If any of the original elements (like total amount box or payment for string) are needed,
// integrate them into this new layout at appropriate positions.