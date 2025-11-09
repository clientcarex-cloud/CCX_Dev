<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
	
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
    <?= _l($title); ?>
    
</h4>

<hr class="hr-panel-heading" />

<!-- Reusable Report Section -->
<?php
$default_url = admin_url('client/reports/mis_reports');

$mis_sections = [
	'Management' => [
		['KPI Dashboard', 'Key performance metrics across departments.', $default_url],
		['Branch Summary Report', 'Summary view of all branch activities.', $default_url],
		['Revenue vs Expense Report', 'Comparative report on income and outflow.', $default_url],
	],
	'Admin' => [
		['Attendance Report', 'Staff attendance and leave summary.', $default_url],
		['Asset Usage Report', 'Assets assigned to staff and usage status.', $default_url],
		['Staff Onboarding Summary', 'Details of newly joined staff.', $default_url],
	],
	'Productivity' => [
		
	],
	'Doctors' => [
		['Patient Visit Report', 'Patients seen per day with consultation details.', $default_url],
		['Prescription Summary', 'Total prescribed medicines and diagnosis count.', $default_url],
		['Treatment Completion Report', 'Follow-up and completed treatment cases.', $default_url],
	],
	'Data Analyst' => [
		['Raw Data Dump', 'Download raw patient, treatment, or invoice data.', $default_url],
		['Lead Conversion Analysis', 'How leads are converting into patients.', $default_url],
		['Funnel Drop Analysis', 'At which point users drop from system.', $default_url],
	],
	'Digital Marketing' => [
		['Campaign Performance', 'Facebook, Google Ads, and email campaign result summary.', $default_url],
		['Lead Source Analysis', 'Performance by lead source.', $default_url],
		['Cost per Lead Report', 'Marketing spend per lead acquisition.', $default_url],
	],
	'Sales' => [
		['Sales Performance', 'Conversion by sales team or individual.', $default_url],
		['Collection Report', 'Revenue received vs projected.', $default_url],
		['Quotation Follow-ups', 'Pending and closed quotation status.', $default_url],
	],
	'CCE' => [
		['Call Logs Report', 'Call status and durations by executive.', $default_url],
		['Missed Follow-ups', 'Follow-ups missed by team.', $default_url],
		['Callback Summary', 'List of callbacks with outcome.', $default_url],
	],
	'CSE' => [
		['Enquiry Report', 'CSE-wise lead and enquiry status.', $default_url],
		['Walk-in Tracker', 'Patient walk-in details with staff mapping.', $default_url],
		['First Response Time', 'Time taken to respond to new leads.', $default_url],
	],
	'Managers' => [
		['Staff Performance', 'All staff KPIs and targets achieved.', $default_url],
		['Department Health', 'Snapshot of department-wise status.', $default_url],
		['Complaint Escalations', 'Pending escalations and resolution rate.', $default_url],
	],
	'Other Reports' => [
		['SMS & WhatsApp Logs', 'Communication logs sent via SMS/WA.', $default_url],
		['Download Logs', 'What files were downloaded & by whom.', $default_url],
		['System Usage Stats', 'User login trends and usage.', $default_url],
	]
];

if (staff_can('doctor_reports', 'customers')) {
    $mis_sections['Doctors'][] = ['Doctor Ownership Report', 'Patients tagged under doctors.', admin_url('client/reports/doctor_ownership_reports')];
	
	$mis_sections['Doctors'][] = ['Appointment Report', 'Detailed appointment statistics.', admin_url('client/reports/appointment_report')];
	
	$mis_sections['Doctors'][] = ['Consultation Fee Report', 'Consultation fee collection per doctor.', admin_url('client/reports/consult_fee_report')];
	
	$mis_sections['Doctors'][] = ['Doctor Appointment List Report', 'List of appointments per doctor.', admin_url('client/reports/doctor_appointment_list_report')];


    $mis_sections['Doctors'][] = ['Doctor Appointment Report', 'Appointment status per doctor.', admin_url('client/reports/doctor_appointment_report')];

    $mis_sections['Doctors'][] = ['Enquiry Doctor Ownership Report Details', 'Enquiry Doctor Ownership Report Details.', admin_url('client/reports/enquiry_doctor_ownership_detail_report')];
}
if (staff_can('admin_reports', 'customers')) {
    $mis_sections['Admin'][] = ['Branch Registration Report', 'New registrations by branch.', admin_url('client/reports/branch_registration_report')];
	
	$mis_sections['Admin'][] = ['Payment Detail Report', 'Payment Detail Report', admin_url('client/reports/payment_detail_report')];
}

if (staff_can('cce_reports', 'customers')) {	
  
    $mis_sections['CCE'][] = ['My Appointments Report', 'Appointments created by me.', admin_url('client/reports/my_appointment_report_cc')];

    $mis_sections['CCE'][] = ['Appointment Report (CC)', 'Center-wise appointment stats.', admin_url('client/reports/appointment_report_cc')];
	
    $mis_sections['CCE'][] = ['Enquiry Call Report', 'Enquiry Call Report', admin_url('client/reports/enquiry_call_report')];
}

if (staff_can('cse_reports', 'customers')) {	
	
	$mis_sections['CSE'][] = ['CSE GT Report', 'CSE GT Report', admin_url('client/reports/cse_gt_report')];

	$mis_sections['CSE'][] = ['My Ownership Report', 'My Ownership Report', admin_url('client/reports/cse_ownership_report')];
}	
	
if (staff_can('sales_reports', 'customers')) {
    $mis_sections['Sales'][] = ['Branch Payment Report', 'Payments collected across branches.', admin_url('client/reports/branch_payment_report')];

    $mis_sections['Sales'][] = ['CRO Ownership Report', 'Patients owned by CROs.', admin_url('client/reports/cro_ownership_report')];
}

if (staff_can('other_reports', 'customers')) {

	$mis_sections['Other Reports'][] = ['Renewal Report', 'Patient treatment renewal report', admin_url('client/reports/renewal_report')];

	$mis_sections['Other Reports'][] = ['Enquiry GT Report', 'Enquiry GT Report', admin_url('client/reports/enquiry_gt_report')];

	$mis_sections['Other Reports'][] = ['GT Report', 'GT Report', admin_url('client/reports/gt_report')];

	$mis_sections['Other Reports'][] = ['Casesheet Patient Status Report', 'Casesheet Patient Status Report', admin_url('client/reports/casesheet_patient_status_report')];

	$mis_sections['Other Reports'][] = ['Source Enquiry Report', 'Source Enquiry Report', admin_url('client/reports/source_enquiry_report')];

	$mis_sections['Other Reports'][] = ['CRO Ownership Detail Report', 'CRO Ownership Detail Report', admin_url('client/reports/cro_ownership_detail_report')];

	$mis_sections['Other Reports'][] = ['Pharmacy Report', 'Medicine distribution and inventory tracking.', admin_url('client/reports/pharmacy_report')];
}

if (staff_can('manager_reports', 'customers')) {

	$mis_sections['Managers'][] = ['TAT Report', 'Turnaround Time for services.', admin_url('client/reports/tat_report')];
}

if (staff_can('productivity_reports', 'customers')) {

	$mis_sections['Productivity'][] = ['Task Count Report', 'Task Count Report', admin_url('client/reports/task_count')];

	$mis_sections['Productivity'][] = ['Date wise Delegation Report', 'Date wise Delegation Report', admin_url('client/reports/datewise_delegation')];

	$mis_sections['Productivity'][] = ['Task Category Count', 'Task Category Wise Count', admin_url('client/reports/task_category_count')];

	$mis_sections['Productivity'][] = ['Overall Productivity Status Report', 'Overall Productivity Status Report', admin_url('client/reports/overall_productivity_status')];

	$mis_sections['Productivity'][] = ['Task Ontime Report', 'Overall Productivity Status Report', admin_url('client/reports/task_ontime')];
}

// Render reports
foreach ($mis_sections as $title => $reports) {

    // Remove reports with default URL
    $filtered_reports = array_filter($reports, function ($report) use ($default_url) {
        return isset($report[2]) && $report[2] !== $default_url;
    });

    // Skip the section entirely if no reports remain
    if (empty($filtered_reports)) {
        continue;
    }

    echo '<div class="report-section">';
    echo '<div class="section-title">' . $title . '</div>';
    echo '<div class="report-grid">';

    // Re-index array to prevent chunking gaps
    $filtered_reports = array_values($filtered_reports);
    $half = ceil(count($filtered_reports) / 2);
    $chunks = array_chunk($filtered_reports, $half);

    foreach ($chunks as $chunk) {
        echo '<div class="report-column">';
        foreach ($chunk as $report) {
            $name = $report[0];
            $desc = $report[1];
            $url  = $report[2];
            echo '<p><a href="' . $url . '">' . $name . '</a><br>' . $desc . '</p>';
        }
        echo '</div>';
    }

    echo '</div></div>';
}


?>


<style>
    .section-title {
        font-weight: bold;
        background: #f9f9f9;
        padding: 10px;
        margin-bottom: 10px;
        font-size: 16px;
        border-bottom: 1px solid #e0e0e0;
    }
    .report-section {
        border: 1px solid #ddd;
        !padding: 5px;
        margin-bottom: 25px;
        border-radius: 4px;
    }
    .report-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
		padding-left: 5px;
    }
    .report-column p {
        font-size: 14px;
        margin-bottom: 12px;
    }
    .report-column a {
        color: #007bff;
        font-weight: 500;
        text-decoration: none;
    }
    .report-column a:hover {
        text-decoration: underline;
    }
</style>





</div>



</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>


</body>
</html>
