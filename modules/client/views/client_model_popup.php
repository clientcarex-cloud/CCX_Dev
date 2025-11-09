<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="client-model-auto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xxl">
    <div class="modal-content">
    <style>
.dataTables_filter {
  text-align: right !important;
}
.modal-body {
  max-height: 90vh;
  overflow-y: auto;
}

.dataTables_length select.form-control {
    display: inline-block;
    width: auto;
    height: 30px;
    min-height: 20px;
    margin-top: 0px; /* adjust as needed */
    padding-top: 4px; /* tweak if necessary */
    font-size: 14px;
}
        
/* Scoped styles for table and cell centering */
.medicine-table-container {
  margin-top: 20px;
}

.medicine-table {
  width: 100%;
  border-collapse: collapse;
}

.medicine-table th, .medicine-table td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
  vertical-align: middle; /* Ensures vertical alignment in the cell */
}



.medicine-table td {
  vertical-align: middle; /* Vertically centers content in td */
}

.medicine-select-container {
  position: relative;
  width: 100%;
}

.medicine-select-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.medicine-select-options {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  max-height: 150px;
  overflow-y: auto;
  background-color: white;
  border: 1px solid #ccc;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  display: none;
  z-index: 999;
}

.medicine-select-option {
  padding: 8px;
  cursor: pointer;
}

.medicine-select-option:hover {
  background-color: #f1f1f1;
}

.medicine-btn {
  padding: 6px 12px;
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

.medicine-btn:hover {
  background-color: #0056b3;
}

.medicine-textarea {
  width: 100%;
  padding: 4px;
  box-sizing: border-box;
  border-radius: 4px;
  border: 1px solid #ccc;
}
</style>
<style>
    /* Container for Prescription Form */
    .medicine-table-container {
        margin-top: 20px;
        border: 1px solid #ccc;
        padding: 20px;
        !background-color: #f9f9f9;
        border-radius: 8px;
    }

    /* Prescription Table */
    .medicine-table {
        width: 100%;
        border-collapse: collapse;
    }

    /* Table Header Styling */
    .medicine-table th {
        padding: 12px;
        text-align: left;
        !background-color: #007bff;  /* A blue color for the table header */
        !color: #fff;                /* White text color */
        font-size: 16px;
        font-weight: 500;
    }

    .medicine-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    /* Table Data Styling */
    .medicine-table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    /* Heading Styling */
    .patient-section-title h4 {
        color: #343a40;         /* Dark gray for headings */
        font-size: 1.5rem;       /* Slightly larger font size */
        font-weight: 600;        /* Bold text for headings */
        margin-bottom: 20px;
    }

    /* Form Buttons */
    .form-actions {
        margin-top: 20px;
        text-align: right;
    }

    .form-actions button {
        margin-left: 10px;
    }

    /* Add Medicine Button */
    .medicine-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .medicine-btn {
        background-color: #28a745;  /* Green for the "Add Medicine" button */
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .medicine-btn:hover {
        background-color: #218838;  /* Darker green on hover */
    }

    /* Prescription Form Layout */
    .medicine-table th, .medicine-table td {
        padding: 10px;
        border: 1px solid #ddd;
    }

    .medicine-table-container h4 {
        margin-bottom: 20px;
    }

    /* Prescription Form Input Styling */
    .medicine-table input, .medicine-table textarea {
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .medicine-table select {
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    /* Action Button Styling */
    .medicine-btn {
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border-radius: 5px;
        font-size: 14px;
    }

    .medicine-btn:hover {
        background-color: #0056b3;
    }
</style>
<style>
          .patient-section-title {
            font-weight: 500;
            !background: #f2f9ff;
            font-size: 16px;
            !color: #fff;
            !text-align: center;
            !color: #007bff; 
            padding: 8px; 
            border-radius: 5px; 
            border: 1px solid #ccc;
            margin: 0;
          }

          .patient-info-row {
            margin-bottom: 10px;
          }

          .patient-label {
            font-weight: 600;
            color: #555;
          }

          .patient-value {
            color: #000;
          }

          .note-text {
            color: #007bff;
            font-weight: 500;
          }
          .blurred {
  filter: blur(3px);
  transition: filter 0.3s;
}

          .patient-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 14px;
  }

  .patient-table td {
    border: 1px solid #ccc;
    padding: 8px 12px;
    vertical-align: top;
  }

  .patient-label {
    !font-weight: bold;
    color: #333;
    display: inline-block;
    min-width: 150px;
  }

  .text-success {
    color: green;
  }
  


/* Card Header Styling */
.card-header {
    background-color: #f1f1f1; /* Light gray background */
    color: #333; /* Dark text color for better contrast */
    !padding: 12px 20px; /* Padding on both sides */
    !border: 1px solid #ccc; /* Light gray border */
    cursor: pointer; /* Pointer cursor on hover */
    display: flex;
    justify-content: space-between; /* Space between title and icon */
    align-items: center;
    !border-radius: 5px 5px 0 0; /* Rounded corners at the top */
    transition: background-color 0.3s ease; /* Smooth background color change */
}

/* Card Header Hover Effect */
.card-header:hover {
    !background-color: #e0e0e0; /* Slightly darker gray when hovering */
}

/* Accordion Header Text */
.card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600; /* Slightly bolder text */
}

/* Arrow Icon Styling */
.toggle-icon {
    transition: transform 0.3s; /* Smooth rotation for the arrow */
    font-size: 18px;
   
}

/* When the section is open, rotate the icon */
.toggle-icon.open {
    transform: rotate(180deg);
}

/* Card Body Styling */
.card-body {
    padding: 20px;
    !border-top: 1px solid #e0e0e0; /* Light gray border at the top */
    background-color: #fafafa; /* Very light gray background for card content */
}

/* General Form Layout */
.mtop10 {
    margin-top: 10px;
}

.mtop20 {
    margin-top: 20px;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.form-actions .btn {
    margin-left: 10px;
}

/* Adjust for Card Body if needed */
.card {
    margin-bottom: 15px;
}

/* Styling for Form Elements */
select.form-control, input.form-control, textarea.form-control {
    !border-radius: 5px;
    !border: 1px solid #ccc; /* Light gray border for form elements */
    padding: 10px;
}
.scroller.arrow-right {
  display: block !important;
}


  </style>

      <!-- Modal Header -->
      <div class="modal-header d-flex justify-content-between align-items-center">
	   <div class="modal-dialog modal-dialog-centered modal-xxl" role="document">
      <div class="row align-items-center">
			<div class="col-md-10">
				<h4 class="modal-title m-0" style="display: inline-block;">
					#<?= $client->userid; ?> - <?= $client->company; ?> | <?php echo $customer_new_fields->gender?> <?php
					$today = date('Y-m-d');
					$today_appointments = [];
					foreach ($appointment_data as $app_data) {
						if (!empty($app_data['appointment_date'])) {
							$date_only = date('Y-m-d', strtotime($app_data['appointment_date']));
							
							if ($date_only === $today) {
								$today_appointments[] = $app_data;
							}
						}
					}

					if (!empty($customer_new_fields->age)) {
						echo " | ".$customer_new_fields->age;
					}else if (!empty($customer_new_fields->dob)) {
						$dob = new DateTime($customer_new_fields->dob);
						$today = new DateTime();
						$diff = $dob->diff($today);

						echo " | ".$diff->y . ' Years, ' . $diff->m . ' Months, ' . $diff->d . ' Days';
					}
					
					if (!empty($patient_treatment)) {
						$treatment_names = array_column($patient_treatment, 'treatment_name');

						// Get unique names
						$unique_names = array_unique($treatment_names);

						// Convert to comma-separated string
						echo " | ".implode(', ', $unique_names);
					}

					?>
					<?php 
		   $current_status = $client->status_name;
		   $status_name = $client->status_name;
		   $color = $client->status_color;
		   
		   echo $outputStatus = '<span class="lead-status-' . $current_status . ' label' . (empty($color) ? ' label-default' : '') . '" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . e($status_name) . '</span>';?>
				</h4>
				<?php
				$color = '#28a745'; // green or any dynamic color
				$bg = adjust_hex_brightness($color, 0.04);
				$border = adjust_hex_brightness($color, 0.4);

				echo '<label id="consultation_timer_container" style="display: none; margin-left: 10px;">
				  <span class="label label-default" 
						style="color:' . $color . ';
							   border:1px solid ' . $border . ';
							   background:' . $bg . ';
							   padding: 4px 8px;
							   border-radius: 4px;">
					Consultation Duration:&nbsp;<span id="consultation_timer"> 00:00</span>
				  </span>
				</label>';
				?>




			</div>
			
			<div class="col-md-2 text-end" style="display: flex; align-items: center; justify-content: flex-end; gap: 5px;">


<?php
$doctor_id = get_staff_user_id(); // current logged-in staff
$counter = get_counter_by_doctor_id($doctor_id);

$current_status = strtolower($counter->counter_status ?? '');
$is_break = in_array($current_status, ['emergency', 'lunch break']);

if($counter){
	if (staff_can('token_emergency_lunch_break', 'customers')) {
	?>
	<div class="btn-group">
  <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
    <?= _l('Counter Status'); ?>: <?= $current_status ?> <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-left">
    <li class="<?= $current_status == 'Available' ? 'active' : '' ?>">
      <a href="javascript:void(0);" class="update-status" data-status="Available">Available</a>
    </li>
    <li class="<?= $current_status == 'Lunch Break' ? 'active' : '' ?>">
      <a href="javascript:void(0);" class="update-status" data-status="Lunch Break">Lunch Break</a>
    </li>
    <li class="<?= $current_status == 'Emergency' ? 'active' : '' ?>">
      <a href="javascript:void(0);" class="update-status" data-status="Emergency">Emergency</a>
    </li>
  </ul>
</div>
<?PHP
	}
	if (staff_can('token_smart_queue', 'customers')) {
		
	?>
<div class="btn-group ml-2">
  <?php if (!$is_break): ?>
    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
      Start Token <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right">
      <?php foreach ($today_appointment_data as $row): ?>
        <li>
          <a href="javascript:void(0);" class="start-token"
             data-patient-id="<?= $row['patient_id'] ?>"
             data-doctor-id="<?= $row['enquiry_doctor_id'] ?>">
            <?= $row['patient_name'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <span class="label label-warning"><?= ucfirst($current_status) ?> Mode</span>
  <?php endif; ?>
</div>
	
	<?PHP
	}
}
?>

		<script>
		$(document).on('click', '.start-token', function () {
    const patient_id = $(this).data('patient-id');
    const doctor_id  = $(this).data('doctor-id');

    $.post(admin_url + 'client/ajax_start_token', {
        patient_id: patient_id,
        doctor_id: doctor_id
    }, function (response) {
        const res = JSON.parse(response);
        if (res.success) {
            window.location.href = admin_url + 'client/get_patient_list/' + patient_id;
            // Optional: refresh token table
        } else {
			alert_float('danger', 'Failed to start token');
        }
    });
});
$(document).on('click', '.update-status', function () {
    const status = $(this).data('status');
    const doctor_id = <?= get_staff_user_id(); ?>;

    $.post(admin_url + 'client/update_counter_status', {
        doctor_id: doctor_id,
        status: status
    }, function (res) {
        const response = JSON.parse(res);
        if (response.success) {
			alert_float('success', 'Counter status updated to ' + status);
            location.reload(); // Refresh to reflect status
        } else {
			alert_float('danger', 'Failed to update status.');
        }
    });
});


		</script>	
			<?PHP
			

			if (staff_can('edit', 'customers')) {
			?>
				<a href="<?= admin_url('client/edit_client/'.$client->userid); ?>">
					<button type="button" class="btn btn-warning btn-sm edit-button">
						<i class="fas fa-pencil-alt"></i> <?php echo _l('edit'); ?>
					</button>
				</a>
			<?PHP
			}
			$should_close_tab = (strpos($callback_url, 'reports/') === 0);
			
			if($callback_url){
				if($should_close_tab){
					?>
					<a href="#" onclick="window.close();">
						<button type="button" class="btn btn-secondary btn-sm close close-button">
						<span aria-hidden="true">&times;</span>
					</button>
					</a>
				<?php
				}else{
					if($callback_url == "appointments-tab"){
						?>
					<a href="<?= admin_url('client/get_patient_list/#appointments-tab'); ?>">
						<button type="button" class="btn btn-secondary btn-sm close close-button">
						<span aria-hidden="true">&times;</span>
					</button>
					</a>
					<?php
					}else{
					
					?>
					<a href="<?= admin_url('client/'.$callback_url); ?>">
						<button type="button" class="btn btn-secondary btn-sm close close-button">
						<span aria-hidden="true">&times;</span>
					</button>
					</a>
					<?php
						
					}
				}
				
			}else{
				?>
				<a href="<?= admin_url('client/get_patient_list'); ?>">
				<button type="button" class="btn btn-secondary btn-sm close close-button">
					<span aria-hidden="true">&times;</span>
				</button>
				</a>
				<?php
			}
			?>
			
				
			</div>
		</div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div class="top-lead-menu">
          <div class="horizontal-scrollable-tabs tw-mb-10">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right" style="display: block !important;"><i class="fa fa-angle-right"></i></div>

            <div class="horizontal-tabs">
              <ul class="nav nav-tabs nav-tabs-horizontal nav-tabs-segmented" role="tablist">
                <?php
                  if (staff_can('view_overview', 'customers')) {
                ?>
                <li role="presentation" class="active">
                  <a href="#tab_overview" aria-controls="tab_overview" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-circle-info menu-icon"></i>
                    <?= _l('overview'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view_casesheet', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_casesheet" aria-controls="tab_casesheet" role="tab" data-toggle="tab">
                  <i class="fa-solid fa-notes-medical menu-icon"></i>
                    <?= _l('casesheet'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view_prescription', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_prescription" aria-controls="tab_prescription" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-prescription-bottle-medical menu-icon"></i>
                    <?= _l('prescription'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view', 'estimates')) {
                ?>
                <li role="presentation">
                  <a href="#tab_estimation" aria-controls="tab_estimation" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-credit-card menu-icon"></i>
                    <?= _l('package'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view_payments', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_payments" aria-controls="tab_payments" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-credit-card menu-icon"></i>
                    <?= _l('payments'); ?>
                  </a>
                </li>
                <?php
                  } if (staff_can('view_visits', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_visits" aria-controls="tab_visits" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-calendar-check menu-icon"></i>
                    <?= _l('visits'); ?>
                  </a>
                </li>
                <?php
                  } if (staff_can('view_feedback', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_feedback" aria-controls="tab_feedback" role="tab" data-toggle="tab">
                    <i class="fa-regular fa-comments menu-icon"></i>
                    <?= _l('feedback'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view_call_log', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_calls" aria-controls="tab_calls" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-phone menu-icon"></i>
                    <?= _l('call_logs'); ?>
                  </a>
                </li>
                <?php
                  }if (staff_can('view_message_log', 'customers')) {
                ?>
                <li role="presentation">
					<a href="#message_log" aria-controls="message_log" role="tab" data-toggle="tab">
						<i class="fa-solid fa-envelope menu-icon"></i> <?= _l('message_log'); ?>
					</a>
				</li>
                <?php
                  }if (staff_can('view_patient_reminders', 'customers')) {
                ?>
                <li role="presentation">
					<a href="#patient_reminders"
					   aria-controls="patient_reminders" role="tab" data-toggle="tab">
						<i class="fa-regular fa-bell menu-icon"></i>
						<?= _l('leads_reminders_tab'); ?>
						<?php if ($total_reminders > 0) { ?>
							<span class="badge"><?= $total_reminders; ?></span>
						<?php } ?>
					</a>
				</li>

                  <?php
                  }if (staff_can('view_activity_log', 'customers')) {
                ?>
                <li role="presentation">
                  <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                    <i class="fa-solid fa-list menu-icon"></i>
                    <?= _l('activity_logs'); ?>
                  </a>
                </li>
                  <?php
                  }
                  ?>
              </ul>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
		 <?php
		  if (staff_can('view_overview', 'customers')) {
		?>
          <div role="tabpanel" class="tab-pane active" id="tab_overview">
          
        
        <div class="container-fluid">
		<?php
        $estimate_info = get_latest_estimate_dates($client->userid);
        $total_estimates = $estimate_info['total_estimates'] ?? 0;
        $renewal_start_date = $estimate_info['date'] ?? null;
        $renewal_end_date = $estimate_info['expirydate'] ?? null;
    
		$today = new DateTime();
		$renewalLabel = '';
		$color = '';
		$bg = '';
		$border = '';

		if (!empty($renewal_end_date)) {
			try {
				$endDate = new DateTime($renewal_end_date);
				$diff = (int)$today->diff($endDate)->format('%r%a');

				if ($diff < 0) {
					$renewalLabel = 'Renewal Expired Since ' . abs($diff) . ' days';
					$color = '#dc3545'; // red
				} elseif ($diff <= 30) {
					$renewalLabel = 'Renewal Expiry In ' . $diff . ' days';
					$color = '#28a745'; // green
				}

				if ($renewalLabel) {
					$bg = adjust_hex_brightness($color, 0.04);
					$border = adjust_hex_brightness($color, 0.4);
				}

			} catch (Exception $e) {
				// Silent fail
			}
		}
		?>

		


          <!-- Section 1: Patient Details -->
          <div class="patient-section-title"><?= _l('patient_details'); ?> <?php if ($renewalLabel): ?> 
		  <div class="text-right" style="display: inline-block; width: 85%;">
			<span class="label"
				  style="color: <?= $color ?>;
						 border: 1px solid <?= $border ?>;
						 background: <?= $bg ?>;
						 border-radius: 4px;
						 font-weight: 400;
						 font-size: 14px;">
			  <i class="fa fa-calendar-check-o"></i> <?= $renewalLabel ?>
			</span>
		  </div>
		<?php endif; ?></div>
			

          <table class="patient-table">
    <tr>
        <td><span class="patient-value"><strong><?= _l('mr_no'); ?>:</strong> <?= $customer_new_fields->mr_no; ?></span></td>
        <td><span class="patient-value"><strong><?= _l('city_state_country'); ?>:</strong> <?= $client->city_name; ?>, <?= $client->state_name; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('patient_name'); ?>:</strong> <?= $client->company; ?></span></td>
        <td><span class="patient-value"><strong><?= _l('pincode'); ?>:</strong> <?= $client->pincode_name; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('age'); ?>:</strong> <?= $customer_new_fields->age; ?></span></td>
        <td><span class="patient-value"><strong><?= _l('gender'); ?>:</strong> <?= $customer_new_fields->gender; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('contact_number'); ?>:</strong>
            <?php
                $number = $client->phonenumber;
                if (staff_can('mobile_masking', 'customers') && !is_admin()) {
                    $length = strlen($number);
                    echo ($length <= 5) ? str_repeat('*', $length) : substr($number, 0, $length - 5) . str_repeat('*', 5);
                } else {
                    echo $number;
                }
            ?>
        </span></td>
        <td><span class="patient-value"><strong><?= _l('marital_status'); ?>:</strong> <?= $client->marital_status; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('email_id'); ?>:</strong> <?= $client->email_id; ?></span></td>
        <td><span class="patient-value"><strong><?= _l('language_known'); ?>:</strong> <?= $client->default_language; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('area'); ?>:</strong> <?= $client->area; ?></span></td>
        <td><span class="patient-value"><strong><?= _l('lead_source'); ?>:</strong> <?= $client->source_name ?? ''; ?></span></td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('address'); ?>:</strong> <?= $client->address; ?></span></td>
        <td>
            <span class="patient-value"><strong><?= _l('patient_status'); ?>:</strong>
                <?php
                    $status_name = $client->status_name;
                    $color = $client->status_color;
                    echo '<span class="lead-status label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background:' . adjust_hex_brightness($color, 0.04) . ';">' . e($status_name) . '</span>';
                ?>
            </span>
        </td>
    </tr>
    <tr>
        <td><span class="patient-value"><strong><?= _l('current_status'); ?>:</strong>
            <?php
                $current_status_name = $client->current_status ?? '';
                $CI = &get_instance();
                $CI->db->select('color, id');
                $CI->db->where('name', $current_status_name);
                $status = $CI->db->get(db_prefix() . 'leads_status')->row();
                if ($status) {
                    echo '<span class="lead-status-' . $status->id . ' label" style="color:' . $status->color . ';border:1px solid ' . adjust_hex_brightness($status->color, 0.4) . ';background:' . adjust_hex_brightness($status->color, 0.04) . ';">' . e($current_status_name) . '</span>';
                } else {
                    echo '-';
                }
            ?>
        </span></td>
        <td><span class="patient-value"><strong><?= _l('registration_date'); ?>:</strong>
            <?php
                if (!empty($client->registration_start_date) && $client->registration_start_date != '1970-01-01' && !empty($customer_new_fields->mr_no)) {
                    echo _d($client->registration_start_date);
                } else {
                    echo "-";
                }
            ?>
        </span></td>
    </tr>
    
    <?php if ($total_estimates > 1): ?>
        <tr>
            <td><span class="patient-value"><strong><?= _l('renewal_start_date'); ?>:</strong> <?= $renewal_start_date ? _d($renewal_start_date) : '-'; ?></span></td>
            <td><span class="patient-value"><strong><?= _l('renewal_end_date'); ?>:</strong> <?= $renewal_end_date ? _d($renewal_end_date) : '-'; ?></span></td>
        </tr>
    <?php else: ?>
        <tr>
            <td><span class="patient-value"><strong><?= _l('registration_end_date'); ?>:</strong>
                <?php
                    if (!empty($client->registration_end_date) && $client->registration_end_date != '1970-01-01' && !empty($customer_new_fields->mr_no)) {
                        echo _d($client->registration_end_date);
                    } else {
                        echo "-";
                    }
                ?>
            </span></td>
            <td></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td><span class="patient-value"><strong><?= _l('treatment'); ?>:</strong>
            <?php
                $latest_doctor_id = null;
                $latest_treatment = null;
                $latest_time = 0;
                foreach ($appointment_data as $row) {
                    $appointment_time = strtotime($row['appointment_date']);
                    if ($appointment_time > $latest_time) {
                        $latest_time = $appointment_time;
                        $latest_doctor_id = $row['enquiry_doctor_id'];
                        $latest_treatment = $row['description'];
                    }
                }
                echo $latest_treatment;
            ?>
        </span></td>
        <td><span class="patient-value"><strong><?= _l('doctor'); ?>:</strong> <?= get_staff_full_name($latest_doctor_id); ?></span></td>
    </tr>
	<tr>
		<td><span class="patient-value"><strong><?= _l('branch'); ?>:</strong>
			<?= $client->branch_name ?>
		</span></td>
		<td><span class="patient-value"><strong><?= _l('pro_ownership'); ?>:</strong>
			<?PHP
			if($client->pro_ownership){
				echo get_staff_full_name($client->pro_ownership);
			}
			 ?>
		</span></td>
	</tr>
	<tr>
		<td><span class="patient-value"><strong><?= _l('alternate_number'); ?>:</strong>
			<?= $client->alt_number1 ?>
		</span></td>
		<td><span class="patient-value"><strong><?= _l('consultation_fee'); ?>:</strong>
			<?PHP
			echo $first_appointment->total;
			 ?>
		</span></td>
	</tr>
	<tr>
		<td><span class="patient-value"><strong><?= _l('medicine_end_date'); ?>:</strong>
			<?php
			if (!empty($latest_casesheet->followup_date) && $latest_casesheet->followup_date != '0000-00-00') {
				echo date("d-m-Y", strtotime($latest_casesheet->followup_date));
			}
			?>

		</span></td>
		<td><span class="patient-value"><strong></strong>
			<?PHP
			
			 ?>
		</span></td>
	</tr>
</table>


          <!-- Section 2: Patient Payment Summary -->
       <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
  <div class="row">
  <div class="col-md-10">
  <h5 class="patient-section-title mb-0"><?= _l('patient_treatment_summary'); ?></h5>
  </div>
  <div class="col-md-2">
  <button id="toggleSummary" class="btn btn-success btn-sm">
    Show Total Summary
  </button>
  
</div>
</div>
</div>

		  

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleSummary');
    const totalBlocks = document.querySelectorAll('.total-summary');
    const latestBlocks = document.querySelectorAll('.latest-summary');

    toggleBtn.addEventListener('click', function () {
        totalBlocks.forEach(el => el.style.display = el.style.display === 'none' ? 'block' : 'none');
        latestBlocks.forEach(el => el.style.display = el.style.display === 'none' ? 'block' : 'none');
        toggleBtn.textContent = toggleBtn.textContent.includes('Total') ? 'Show Latest Package' : 'Show Total Summary';
    });

    // Initially hide total summary
    totalBlocks.forEach(el => el.style.display = 'none');
});
</script>

          <br>
		  <div class="total-summary">
          <?php
		 // print_r($patient_treatment);
		  foreach($patient_treatment as $treatments_1){
			  
			  $estimation_ids = json_decode($treatments_1['estimation_id'], true);
			  
						if (!is_array($estimation_ids)) {
							$estimation_ids = array_filter(array_map('intval', explode(',', $treatments_1['estimation_id'])));
						}
						$total_package = 0;
						$total_paid    = 0;
						$total_dues    = 0;
						$currency      = ''; // to use the first non-empty currency
						$total_days      = 0;
						$total_completed = 0;
						$total_remaining = 0;

						$today = new DateTime();

						foreach ($estimation_ids as $estimation_id) {
							$summary = get_estimation_payment_summary($estimation_id);
							
							$total_package += $summary['total'];
							$total_paid    += $summary['paid'];
							$total_dues    += $summary['dues'];

							// Set currency once if available
							if (empty($currency) && !empty($summary['currency'])) {
								$currency = $summary['currency'];
							}
							
							$start = !empty($summary['date']) ? new DateTime($summary['date']) : null;
							$end   = !empty($summary['expirydate']) ? new DateTime($summary['expirydate']) : null;
							
							if ($start && $end) {
								$total_days += $start->diff($end)->days;

								// Completed days: from start to today (but not beyond expiry)
								if ($today > $start) {
									$completed = $start->diff(min($today, $end))->days;
									$total_completed += $completed;
								}

								// Remaining days: from today to end (if future)
								if ($today < $end) {
									$remaining = $today->diff($end)->days;
									$total_remaining += $remaining;
								}
							}
						}

						if($total_package>0){
					
			  ?>
			  <table class="patient-table">
              <tr>
              <td>
                  <span class="patient-value">
                    <strong><?= _l('treatment'); ?>:</strong> 
                    <?php 
                      echo $treatments_1['treatment_name']; 
                    ?>  
                  </span>
                </td>
                <td>
                  <span class="patient-value">
                    <strong><?= _l('treatment_duration_period'); ?>:</strong> 
                     <?php 
                      echo $total_days.' days';
                    ?>
                  </span>
                </td>
                <td>
                  <span class="patient-value">
                    <strong><?= _l('treatment_completed_duration'); ?>:</strong> 
                    <?php 
                      echo $total_completed.' days';
                    ?>
                  </span>
                </td>
                
              </tr>
              <tr>
			  
              <td>
                  <span class="patient-value">
                    <strong><?= _l('treatment_duration_left'); ?>:</strong> 
                   <?php 
                      echo $total_remaining.' days';
                    ?>
                  </span>
                </td>
             
               
                 
                
             
                
                <td>
                  <span class="patient-value">
					<span class="patient-value"><strong><?= _l('total_package'); ?>:</strong> <?= app_format_money_custom($total_package, $currency); ?></span>
                  </span>
                </td>
                <td>
                  <span class="patient-value">
                    <span class="patient-value"><strong><?= _l('total_paid'); ?>:</strong> <?= app_format_money_custom($total_paid, $currency); ?></span>
                  </span>
                </td>
                
              </tr>
              <tr>
              <td>
                  <span class="patient-value">
                    <span class="patient-value"><strong><?= _l('total_dues'); ?>:</strong> <?= app_format_money_custom($total_dues, $currency); ?></span>
                  </span>
                </td>
                <td><span class="patient-value"><strong><?= _l('cob_with_refund'); ?>:</strong> -</span></td>
                <td><span class="patient-value"><strong><?= _l('normal_refund'); ?>:</strong> -</span></td>
                
              </tr>
            </table>
			  <?PHP
						}
		  }
          
?>
</div>
<div class="latest-summary">
<?php
foreach ($patient_treatment as $treatments_1) {

    $estimation_ids = json_decode($treatments_1['estimation_id'], true);

    if (!is_array($estimation_ids)) {
        $estimation_ids = array_filter(array_map('intval', explode(',', $treatments_1['estimation_id'])));
    }

    $latest_summary = null;
    $latest_date = null;
    $currency = '';
    $total_days = 0;
    $total_completed = 0;
    $total_remaining = 0;
    $today = new DateTime();

    foreach ($estimation_ids as $estimation_id) {
        $summary = get_estimation_payment_summary($estimation_id);
        $summary_date = !empty($summary['date']) ? new DateTime($summary['date']) : null;

        if ($summary_date && (!$latest_date || $summary_date > $latest_date)) {
            $latest_date = $summary_date;
            $latest_summary = $summary;
        }
    }

    if ($latest_summary) {
        $start = !empty($latest_summary['date']) ? new DateTime($latest_summary['date']) : null;
        $end   = !empty($latest_summary['expirydate']) ? new DateTime($latest_summary['expirydate']) : null;

        if ($start && $end) {
            $total_days = $start->diff($end)->days;

            // Completed days
            if ($today > $start) {
                $total_completed = $start->diff(min($today, $end))->days;
            }

            // Remaining days
            if ($today < $end) {
                $total_remaining = $today->diff($end)->days;
            }
        }

        $currency = $latest_summary['currency'] ?? '';

        ?>
        <table class="patient-table">
            <tr>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('treatment'); ?>:</strong> <?= $treatments_1['treatment_name']; ?>
                    </span>
                </td>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('treatment_duration_period'); ?>:</strong> <?= $total_days ?> days
                    </span>
                </td>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('treatment_completed_duration'); ?>:</strong> <?= $total_completed ?> days
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('treatment_duration_left'); ?>:</strong> <?= $total_remaining ?> days
                    </span>
                </td>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('total_package'); ?>:</strong> <?= app_format_money_custom($latest_summary['total'], $currency); ?>
                    </span>
                </td>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('total_paid'); ?>:</strong> <?= app_format_money_custom($latest_summary['paid'], $currency); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="patient-value">
                        <strong><?= _l('total_dues'); ?>:</strong> <?= app_format_money_custom($latest_summary['dues'], $currency); ?>
                    </span>
                </td>
                <td><span class="patient-value"><strong><?= _l('cob_with_refund'); ?>:</strong> -</span></td>
                <td><span class="patient-value"><strong><?= _l('normal_refund'); ?>:</strong> -</span></td>
            </tr>
        </table>
        <?php
    }
}
?>

</div>
</div>



          </div>
<?PHP
}if (staff_can('view_prescription', 'customers')) {
?>
          <div role="tabpanel" class="tab-pane" id="tab_prescription">
  <?php if (staff_can('create_prescription', 'customers')): ?>
    <!-- Future prescription form button -->
  <?php endif; ?>

  <!-- Section Title -->
  <div class="patient-section-title mt-4"><?= _l('doctor_prescription'); ?></div>

  <!-- Top Table: Medicine-wise Remarks -->
  <!--<h4 class="mb-2"><?php echo _l('enter_medicine_wise_remarks'); ?></h4>
  <table class="table table-bordered" id="medicine-remarks-table">
    <thead>
      <tr>
	  <th>#</th>
	  <th><?php echo _l('medicine'); ?></th>
	  <th><?php echo _l('remark'); ?></th>
	  <th><?php echo _l('action'); ?></th>
	</tr>

    </thead>
    <tbody id="medicine-remarks-body">
      
    </tbody>
  </table>-->

  <!-- Bottom DataTable: Complete Prescriptions List -->
<?= render_datatable([
  _l('s_no'),                  // 0
  _l('by_doctor'),             // 1
  _l('created_date'),          // 2
  _l('medicine_given_by'),     // 3
  _l('medicine_given_date'),   // 4
 // _l('remarks'),               // 5
  _l('view'),                  // 6 (eye icon)
], 'doctor-prescription'); ?>




</div>
<script>
// Toggle medicine child row
$(document).on('click', '.toggle-medicines', function () {
  const $icon = $(this);
  const $row = $icon.closest('tr');
  const prescription_id = $icon.data('id');
  const casesheet_id = $icon.data('casesheet-id');
  const medicine_days = $icon.data('medicine-days') || '';
  const followup_date = $icon.data('followup-date') || '';

  // Toggle behavior
  if ($row.hasClass('shown')) {
    $row.next('.prescription-child-row').remove();
    $row.removeClass('shown');
    return;
  }

  // Remove other rows
  $('.prescription-child-row').remove();
  $('.shown').removeClass('shown');

  // Parse data
  let prescription = [], remarks = [];
  try {
    prescription = JSON.parse($icon.attr('data-prescription')) || [];
    remarks = JSON.parse($icon.attr('data-remarks')) || [];
  } catch (e) {}

  // Insert row
  const $newRow = $(`
    <tr class="prescription-child-row">
      <td colspan="${$row.find('td').length}">
        <div class="medicine-table-content p-2 text-muted text-center">Loading medicines...</div>
      </td>
    </tr>
  `);
  $row.after($newRow);
  $row.addClass('shown');

  const $content = $newRow.find('.medicine-table-content');

  if (!prescription.length) {
    $content.html('<div class="text-danger">No medicines found.</div>');
    return;
  }

  let html = `
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th><?= _l('medicine_name'); ?></th>
            <th><?= _l('potency'); ?></th>
            <th><?= _l('dose'); ?></th>
            <th><?= _l('timings'); ?></th>
            <th><?= _l('doctor_remarks'); ?></th>
            <th><?= _l('remarks'); ?></th>
            <th><input type="checkbox" class="select-all-meds" title="Select All"></th>
          </tr>
        </thead>
        <tbody>`;

  prescription.forEach((item, i) => {
    const cleaned = item.trim().replace(/^\d+\.\s*/, '');
    const parts = cleaned.split(';').map(p => p.trim());
    const remark = remarks[i] || '';
    const isChecked = remark !== '' ? 'checked' : '';

    html += `<tr>
      <td>${i + 1}</td>
      <td>${parts[0] || ''}</td>
      <td>${parts[1] || ''}</td>
      <td>${parts[2] || ''}</td>
      <td>${parts[3] || ''}</td>
      <td>${parts[4] || ''}</td>
      <td>
        <input type="text" class="form-control form-control-sm remark-input" 
               data-index="${i}" 
               data-prescription-id="${prescription_id}" 
               value="${remark.replace(/"/g, '&quot;')}">
      </td>
      <td>
        <input type="checkbox" class="medicine-checkbox" data-index="${i}" ${isChecked}>
      </td>
    </tr>`;
  });

  html += `
        </tbody>
      </table><br>

      <div class="row mt-3">
        <div class="col-md-3">
          <label><strong><?= _l('medicine_days'); ?>(In Days)</strong></label>
          <input type="number" class="form-control form-control-sm medicine-days-input" 
                 name="medicine_days" placeholder="Enter number of days"
                 value="${medicine_days}" min=1>
        </div>

        <div class="col-md-3">
          <label><strong><?= _l('followup_date'); ?></strong></label>
          <input type="date" class="form-control form-control-sm followup-date-input" 
                 name="followup_date"
                 value="${followup_date}">
        </div>

        <div class="col-md-2">
          <div class="form-check" style="margin-top: 30px;">
            <input type="checkbox" class="form-check-input notify-doctor-checkbox" id="notifyDoctor${prescription_id}">
            <label class="form-check-label ms-1" for="notifyDoctor${prescription_id}">
              <?= _l('notify_to_doctor'); ?>
            </label>
          </div>
        </div>

        <div class="col-md-2">
          <div style="margin-top: 30px;">
            <button class="btn btn-sm btn-success save-remarks-btn" 
                    data-id="${prescription_id}" 
                    data-casesheet-id="${casesheet_id}">
              <?= _l('save'); ?>
            </button>
          </div>
        </div>
      </div>
    <br></div>`;

  $content.html(html);
});

// Auto-fill followup date based on medicine days
$(document).on('input', '.medicine-days-input', function () {
  const days = parseInt($(this).val(), 10);
  if (!isNaN(days) && days > 0) {
    const today = new Date();
    today.setDate(today.getDate() + days);
    
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    
    const formatted = `${yyyy}-${mm}-${dd}`;
    $('.followup-date-input').val(formatted);
  }
});

// Save remarks
$(document).on('click', '.save-remarks-btn', function () {
  const prescription_id = $(this).data('id');
  const casesheet_id = $(this).data('casesheet-id');
  const remarks = [];

  $(`.remark-input[data-prescription-id="${prescription_id}"]`).each(function () {
    remarks.push($(this).val().trim());
  });

  const medicine_days = $(`.medicine-days-input`).val();
  const followup_date = $(`.followup-date-input`).val();
  const notifyDoctor = $(`#notifyDoctor${prescription_id}`).is(':checked');

  // ✅ Validate mandatory field
  if (!medicine_days || parseInt(medicine_days, 10) <= 0) {
    alert_float('danger', 'Please enter medicine days');
    $(`.medicine-days-input`).focus();
    return; // stop save
  }

  const csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>';
  const csrf_token_value = '<?= $this->security->get_csrf_hash(); ?>';

  $.post(admin_url + 'client/update_prescription_remarks', {
    id: prescription_id,
    remarks: remarks.join('|'),
    casesheet_id: casesheet_id,
    medicine_days: medicine_days,
    followup_date: followup_date,
    notify_doctor: notifyDoctor ? 1 : 0,
    [csrf_token_name]: csrf_token_value
  }).done(function (res) {
    try {
      res = JSON.parse(res);
    } catch (e) {
      alert_float('danger', 'Invalid response');
      return;
    }

    if (res.success) {
      alert_float('success', 'Remarks & follow-up updated');
      $('table.table-doctor-prescription').DataTable().ajax.reload(null, false);
    } else {
      alert_float('danger', res.message || 'Failed to update');
    }
  });
});

// Handle single checkbox: Clear/Set remark
$(document).on('change', '.medicine-checkbox', function () {
  const index = $(this).data('index');
  const $input = $(`.remark-input[data-index="${index}"]`);
  if (!$(this).is(':checked')) {
    $input.val('');
  } else {
    $input.val('Given');
  }
});

// Select All
$(document).on('change', '.select-all-meds', function () {
  const isChecked = $(this).is(':checked');
  const $tbody = $(this).closest('table').find('tbody');

  $tbody.find('.medicine-checkbox').each(function () {
    $(this).prop('checked', isChecked);
    const index = $(this).data('index');
    const $input = $(`.remark-input[data-index="${index}"]`);
    if (isChecked && !$input.val()) {
      $input.val('Given');
    } else if (!isChecked) {
      $input.val('');
    }
  });
});
</script>



<?php
	  }
	?>
<script>
    function togglePrescriptionForm() {
        const form = document.getElementById('prescription-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>


<?PHP
if (staff_can('view_casesheet', 'customers')) {
?>
<div role="tabpanel" class="tab-pane" id="tab_casesheet">
	<?PHP
	$visit_status = 0; // default

	$today = date('Y-m-d');

	if (!empty($appointment_data) && is_array($appointment_data)) {
		foreach ($appointment_data as $appointment) {
			// Convert appointment_date to Y-m-d format
			$appointment_day = date('Y-m-d', strtotime($appointment['appointment_date'] ?? ''));

			if ($appointment_day === $today && $appointment['visit_status'] == 1) {
				$visit_status = 1;
				break; // Found today's completed visit, no need to continue
			}
		}
	}

		$casesheet_data = null;
		if (isset($casesheet) && is_array($casesheet) && count($casesheet) > 0) {
			$casesheet_data = $casesheet[0];
		}
        if (staff_can('create_casesheet', 'customers') && (!isset($casesheet_data['date']) || date('Y-m-d', strtotime($casesheet_data['date'])) != date('Y-m-d')) && $visit_status == 1) {
        //if ($visit_status == 1) {
        ?>
         <!--<button class="btn btn-primary btn-sm" onclick="toggleCaseSheetForm()" style="float: right; margin-top: 6px; margin-right: 5px;">
            <?= _l('add_casesheet'); ?>
          </button>-->
          <a href="<?= admin_url('client/add_casesheet/'.$client->userid); ?>" target="_blank"><button class="btn btn-primary btn-sm" style="float: right; margin-top: 6px; margin-right: 5px;">
            <?= _l('add_casesheet'); ?>
          </button></a>
      <?php
        }?>
  <!-- Title Section -->
  <div class="patient-section-title mt-4"><?= _l('casesheet'); ?></div>
	
  <br>

  <!-- Prescription Form Section -->
  <div class="medicine-table-container" id="casesheet-form" style="display:none; margin-top: -20px;">
    

   <form id="casesheetForm">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" id="csrf_token">
  <input type="hidden" name="patientid" value="<?= $client->userid; ?>">
 <input type="hidden" name="casesheet_id" id="record_id" value="">
 <input type="hidden" name="consultation_duration" id="consultation_duration" value="">
  <!-- Accordion Tabs -->
  <div class="accordion" id="casesheetAccordion">
	
    <!-- Preliminary Data Tab -->
    <div class="card">
	<?php $isOpen = count($casesheet) > 0; ?>
		<button class="btn <?php echo $isOpen ? '' : 'collapsed'; ?>" type="button" data-toggle="collapse" data-target="#collapsePreliminaryData"  
		  aria-expanded="<?php echo $isOpen ? 'true' : 'false'; ?>" aria-controls="collapsePreliminaryData" style="width: 100%">
		  <div class="card-header" id="headingPreliminaryData">
			<h5 class="mb-0">
			  <strong><?php echo _l('preliminary_data'); ?></strong> 
			  <i class="fa fa-chevron-down toggle-icon" id="icon-preliminaryData"></i>
			</h5>
		  </div>
		</button>

      <div id="collapsePreliminaryData" class="<?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingPreliminaryData" data-parent="#casesheetAccordion">
  
        <div class="card-body">
			<!-- Treatment Dropdown -->
			<div class="form-group">
			<!--<label class="control-label"><?php echo _l('treatment_details'); ?></label>-->
			<div id="treatment_rows">
    <!-- Initial Row -->
    <div class="row treatment-row align-items-end mb-3">
        <div class="col-md-3">
            <?php
			$flat_items = [];

			foreach ($items as $group) {
				foreach ($group as $item) {
					if (isset($item['group_name']) && $item['group_name'] == "Consultation Fee") {
						continue; // Skip Consultation Fee
					}
					$flat_items[] = $item;
				}
			}

			$last_appointment = end($appointment_data);
			
			if($casesheet_data['treatment_type_id']){
				$selected = isset($casesheet_data['treatment_type_id']) ? $casesheet_data['treatment_type_id'] : '';
			}else{
				$selected = isset($last_appointment['treatment_id']) ? $last_appointment['treatment_id'] : '';
			}
			

			echo render_select(
				'description',
				$flat_items,
				['id', 'description'],
				'treatment_type',
				$selected,
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex'),
					//'disabled' => 'disabled',
					'required' => 'required'
				]
			);

			// Add hidden input with actual name used in backend
			echo '<input type="hidden" name="treatment_type[]" value="' . htmlspecialchars($selected) . '">';
			?>


        </div>

       <div class="col-md-2">
		<label><?php echo _l('duration_months');?></label>
		
				
				<?php
$duration_value = '';
if (isset($casesheet_data['duration_value']) && $casesheet_data['duration_value'] !== '') {
    $duration_value = $casesheet_data['duration_value'];
} elseif (isset($patient_treatment[0]['duration_value']) && $patient_treatment[0]['duration_value'] !== '') {
    $duration_value = $patient_treatment[0]['duration_value'];
}
?>

<input type="number" 
       name="duration_value" 
       class="form-control" 
       min="1" 
       placeholder="Number" 
       value="<?= htmlspecialchars($duration_value) ?>">

			
			
	</div>


        <!--<div class="col-md-2">
            <label><?php echo _l('improvement_percentage'); ?></label>
            <input type="number" name="improvement" class="form-control improvement-input" value="0" min="0" max="100" placeholder="Enter Percentage" value="<?= isset($casesheet_data['improvement']) ? htmlspecialchars($casesheet_data['improvement']) : '' ?>" >
        </div>

        <div class="col-md-4">
            <label><?php echo _l('overall_progress'); ?></label>
            <div class="progress">
			
                <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>-->
		<div class="col-md-4">
		<?PHP
		if ($casesheet_data['suggested_diagnostics_id']) {
			$selected_diagnostic_id = isset($casesheet_data['suggested_diagnostics_id']) ? $casesheet_data['suggested_diagnostics_id'] : '';
		} else {
			$selected_diagnostic_id = isset($last_appointment['suggested_diagnostics_id']) ? $last_appointment['suggested_diagnostics_id'] : '';
		}

		echo render_select(
				'suggested_diagnostics_id',
				$suggested_diagnostics, // This should be your array of diagnostic options
				['suggested_diagnostics_id', 'suggested_diagnostics_name'],
				'Suggested Diagnostics',
				$selected_diagnostic_id, // selected value
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex')
				]
			);

		?>
</div>
<div class="col-md-3">
	<label>Status</label>
	<select name="treatment_status" class="form-control" style="padding: 1px" required>
		<option value="Other">Other</option>
		<option value="Better">Better</option>
		<option value="Not Better">Not Better</option>
		<option value="First Consultation">First Consultation</option>
		<option value="SQ">SQ</option>
	</select>
		
	</div>
        <!--<div class="col-md-1">
		<br>
            <button type="button" class="btn btn-success add-row"><i class="fa fa-plus"></i></button>
        </div>-->
    </div>
</div>





</div>


		<!-- JavaScript to Add/Clone Rows and Update Progress -->
		<script>
		
		$(document).ready(function () {

    // Add new row
    $('#treatment_rows').on('click', '.add-row', function () {
        var newRow = `
        <div class="row treatment-row align-items-end mb-3">
            <div class="col-md-3">
                <?php
				$selected = "";
				echo render_select('treatment_type[]', $treatments, ['treatment_id', 'treatment_name'], '', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
				?>
            </div>

             <div class="col-md-2">
				
						<input type="number" name="duration_value[]" class="form-control" min="1" placeholder="Number">
					
					
			</div>

            <div class="col-md-2">
                <input type="number" name="improvement[]" class="form-control improvement-input" min="0" max="100" placeholder="Enter Percentage">
            </div>

            <div class="col-md-4">
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>

            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-row"><i class="fa fa-minus"></i> </button>
            </div>
        </div>`;
        
        // Append the new row
        $('#treatment_rows').append(newRow);

        // Refresh selectpicker
        $('.selectpicker').selectpicker('refresh');
		 $('#treatment_rows .improvement-input').last().on('input', function () {
        if (this.value > 100) this.value = 100;
        if (this.value < 0) this.value = 0;
    });
    });

    // Remove row
    $('#treatment_rows').on('click', '.remove-row', function () {
        // Ensure that there is at least one row left
        if ($('.treatment-row').length > 1) {
            $(this).closest('.treatment-row').remove();
        }
    });

    // Update progress bar based on percentage improvement input
    $('#treatment_rows').on('input', '.improvement-input', function () {
        var value = $(this).val();
        value = Math.max(0, Math.min(100, value)); // Ensure it's between 0 and 100
        var progressBar = $(this).closest('.treatment-row').find('.progress-bar');
		if(value == 0 || value == ''){
			value = 0;
		}
		
        progressBar.css('width', value + '%').attr('aria-valuenow', value).text(value + '%');
    });
});


		</script>



		<!-- Presenting Complaints -->
		<div class="form-group mtop20">
			<label for="presenting_complaints" class="control-label">
				<?php echo _l('presenting_complaints'); ?>
			</label>
			<textarea id="presenting_complaints" name="presenting_complaints" class="form-control tinymce" rows="6"> <?php //isset($casesheet_data['presenting_complaints']) ? htmlspecialchars($casesheet_data['presenting_complaints']) : '' ?></textarea>
		</div>
		
		<!-- Presenting Complaints -->
		<div class="form-group mtop20">
			<label for="complaint" class="control-label">
				<?php echo _l('complaints'); ?>
			</label>
			<textarea id="complaint" name="complaint" class="form-control tinymce" rows="6"> <?php //isset($casesheet_data['complaint']) ? htmlspecialchars($casesheet_data['complaint']) : '' ?></textarea>
		</div>
        </div>
      </div>
    </div>

    <!-- Clinical Observation Tab -->
    <div class="card">
	<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapseClinicalObservation" aria-expanded="false" aria-controls="collapseClinicalObservation" style="width: 100%">
      <div class="card-header" id="headingClinicalObservation">
        <h5 class="mb-0">
          
            <strong><?php echo _l('clinical_observation'); ?></strong> <i class="fa fa-chevron-down toggle-icon" id="icon-clinicalObservation"></i>
         
        </h5>
      </div>
	   </button>
      <div id="collapseClinicalObservation" class="<?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingClinicalObservation" data-parent="#casesheetAccordion">
	  
	  
        <div class="card-body">
          <!-- Clinical Observation Content -->
          <div class="row mtop10">
           
            <div class="col-md-12">
              <label for="clinical_observation"><?php echo _l('clinical_observation'); ?> <span class="text-danger">*</span></label>
              <textarea name="clinical_observation" id="clinical_observation" class="form-control tinymce" rows="6"><?php //isset($casesheet_data['clinical_observation']) ? htmlspecialchars($casesheet_data['clinical_observation']) : '' ?></textarea>
            </div>
          </div>

          

        </div>
      </div>
    </div>

    <!-- Personal History Tab -->
    <div class="card">
	<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapsePersonalHistory" aria-expanded="false" aria-controls="collapsePersonalHistory" style="width: 100%">
      <div class="card-header" id="headingPersonalHistory">
        <h5 class="mb-0">
          
            <strong><?php echo _l('personal_history'); ?></strong> <i class="fa fa-chevron-down toggle-icon" id="icon-personalHistory"></i>
          
        </h5>
      </div>
	  </button>
      <div id="collapsePersonalHistory" class=" <?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingPersonalHistory" data-parent="#casesheetAccordion">
        <div class="card-body">
		  <div class="row">

			<!-- Row 1 -->
			<div class="col-md-4 mb-3">
			  <label><?= _l('appetite'); ?>:</label>
			  <input type="text" name="appetite" class="form-control" placeholder="<?= _l('appetite'); ?>"
					 value="<?= isset($casesheet_data['appetite']) ? htmlspecialchars($casesheet_data['appetite']) : '' ?>">
			</div>

			<!-- Row 1 -->
			<div class="col-md-4 mb-3">
			  <label><?= _l('thirst'); ?>:</label>
			  <input type="text" name="thirst" class="form-control" placeholder="<?= _l('thirst'); ?>"
					 value="<?= isset($casesheet_data['thirst']) ? htmlspecialchars($casesheet_data['thirst']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <label><?= _l('desires'); ?>:</label>
			  <input type="text" name="desires" class="form-control" placeholder="<?= _l('desires'); ?>"
					 value="<?= isset($casesheet_data['desires']) ? htmlspecialchars($casesheet_data['desires']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <label><?= _l('aversion'); ?>:</label>
			  <input type="text" name="aversion" class="form-control" placeholder="<?= _l('aversion'); ?>"
					 value="<?= isset($casesheet_data['aversion']) ? htmlspecialchars($casesheet_data['aversion']) : '' ?>">
			</div>

			<!-- Row 2 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('tongue'); ?>:</label>
			  <input type="text" name="tongue" class="form-control" placeholder="<?= _l('tongue'); ?>"
					 value="<?= isset($casesheet_data['tongue']) ? htmlspecialchars($casesheet_data['tongue']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('urine'); ?>:</label>
			  <input type="text" name="urine" class="form-control" placeholder="<?= _l('urine'); ?>"
					 value="<?= isset($casesheet_data['urine']) ? htmlspecialchars($casesheet_data['urine']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('bowels'); ?>:</label>
			  <input type="text" name="bowels" class="form-control" placeholder="<?= _l('bowels'); ?>"
					 value="<?= isset($casesheet_data['bowels']) ? htmlspecialchars($casesheet_data['bowels']) : '' ?>">
			</div>

			<!-- Row 3 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('sweat'); ?>:</label>
			  <input type="text" name="sweat" class="form-control" placeholder="<?= _l('sweat'); ?>"
					 value="<?= isset($casesheet_data['sweat']) ? htmlspecialchars($casesheet_data['sweat']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('sleep'); ?>:</label>
			  <input type="text" name="sleep" class="form-control" placeholder="<?= _l('sleep'); ?>"
					 value="<?= isset($casesheet_data['sleep']) ? htmlspecialchars($casesheet_data['sleep']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('sun_headache'); ?>:</label>
			  <input type="text" name="sun_headache" class="form-control" placeholder="<?= _l('sun_headache'); ?>"
					 value="<?= isset($casesheet_data['sun_headache']) ? htmlspecialchars($casesheet_data['sun_headache']) : '' ?>">
			</div>

			<!-- Row 4 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('thermals'); ?>:</label>
			  <input type="text" name="thermals" class="form-control" placeholder="<?= _l('thermals'); ?>"
					 value="<?= isset($casesheet_data['thermals']) ? htmlspecialchars($casesheet_data['thermals']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('habits'); ?>:</label>
			  <input type="text" name="habits" class="form-control" placeholder="<?= _l('habits'); ?>"
					 value="<?= isset($casesheet_data['habits']) ? htmlspecialchars($casesheet_data['habits']) : '' ?>">
			</div>

			<!-- Row 5 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('addiction'); ?>:</label>
			  <input type="text" name="addiction" class="form-control" placeholder="<?= _l('addiction'); ?>"
					 value="<?= isset($casesheet_data['addiction']) ? htmlspecialchars($casesheet_data['addiction']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('side'); ?>:</label>
			  <input type="text" name="side" class="form-control" placeholder="<?= _l('side'); ?>"
					 value="<?= isset($casesheet_data['side']) ? htmlspecialchars($casesheet_data['side']) : '' ?>">
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('dreams'); ?>:</label>
			  <textarea name="dreams" class="form-control" placeholder="<?= _l('dreams'); ?>"><?= isset($casesheet_data['dreams']) ? htmlspecialchars($casesheet_data['dreams']) : '' ?></textarea>
			</div>

			<!-- Row 6 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('diabetes'); ?>:</label>
			  <textarea name="diabetes" class="form-control" placeholder="<?= _l('diabetes'); ?>"><?= isset($casesheet_data['diabetes']) ? htmlspecialchars($casesheet_data['diabetes']) : '' ?></textarea>
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('thyroid'); ?>:</label>
			  <textarea name="thyroid" class="form-control" placeholder="<?= _l('thyroid'); ?>"><?= isset($casesheet_data['thyroid']) ? htmlspecialchars($casesheet_data['thyroid']) : '' ?></textarea>
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('hypertension'); ?>:</label>
			  <textarea name="hypertension" class="form-control" placeholder="<?= _l('hypertension'); ?>"><?= isset($casesheet_data['hypertension']) ? htmlspecialchars($casesheet_data['hypertension']) : '' ?></textarea>
			</div>

			<!-- Row 7 -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('hyperlipidemia'); ?>:</label>
			  <textarea name="hyperlipidemia" class="form-control" placeholder="<?= _l('hyperlipidemia'); ?>"><?= isset($casesheet_data['hyperlipidemia']) ? htmlspecialchars($casesheet_data['hyperlipidemia']) : '' ?></textarea>
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('menstrual_obstetric_history'); ?>:</label>
			  <textarea name="menstrual_obstetric_history" class="form-control" placeholder="<?= _l('menstrual_obstetric_history'); ?>"><?= isset($casesheet_data['menstrual_obstetric_history']) ? htmlspecialchars($casesheet_data['menstrual_obstetric_history']) : '' ?></textarea>
			</div>

			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('family_history'); ?>:</label>
			  <textarea name="family_history" class="form-control" placeholder="<?= _l('family_history'); ?>"><?= isset($casesheet_data['family_history']) ? htmlspecialchars($casesheet_data['family_history']) : '' ?></textarea>
			</div>

			<!-- Final Row -->
			<div class="col-md-4 mb-3">
			  <br>
			  <label><?= _l('past_treatment_history'); ?>:</label>
			  <textarea name="past_treatment_history" class="form-control" placeholder="<?= _l('past_treatment_history'); ?>"><?= isset($casesheet_data['past_treatment_history']) ? htmlspecialchars($casesheet_data['past_treatment_history']) : '' ?></textarea>
			</div>

		  </div>
		</div>

      </div>
    </div>

    <!-- General Examination Tab -->
    <div class="card">
	<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapseGeneralExamination" aria-expanded="false" aria-controls="collapseGeneralExamination" style="width: 100%">
      <div class="card-header" id="headingGeneralExamination">
        <h5 class="mb-0">
            <strong><?php echo _l('general_examination'); ?></strong> <i class="fa fa-chevron-down toggle-icon" id="icon-generalExamination"></i>
         
        </h5>
      </div>
	   </button>
      <div id="collapseGeneralExamination" class="<?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingGeneralExamination" data-parent="#casesheetAccordion">
        <div class="card-body">
          <!-- General Examination Content -->
          <div class="row">
			<div class="col-md-2">
				<label><?php echo _l('bp'); ?>:</label>
				<input type="text" name="bp" class="form-control" placeholder="120/80">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('pulse'); ?>:</label>
				<input type="text" name="pulse" class="form-control" placeholder="Pulse">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('weight'); ?>:</label>
				<input type="text" name="weight" class="form-control" placeholder="WT.(KG)">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('height'); ?>:</label>
				<input type="text" name="height" class="form-control" placeholder="HT.">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('temperature'); ?>:</label>
				<input type="text" name="temperature" class="form-control" placeholder="TEMP.">
			</div>
			<div class="col-md-2">
				<label><?php echo _l('bmi'); ?>:</label>
				<input type="text" name="bmi" class="form-control" placeholder="BMI">
			</div>
		</div>


		<?php
		$fields = [
			['mental_generals', 'pg', 'particulars'],
			['miasmatic_diagnosis', 'analysis_evaluation', 'reportorial_result'],
			['management', 'diet', 'exercise'],
			['critical', 'level_of_assent', 'dos_and_donts'],
			['level_of_assurance', 'criteria_future_plan_rx', 'nutrition']
		];

		$labels = [];
		foreach (array_merge(...$fields) as $field) {
			$labels[$field] = _l($field);
		}
		?>


		<?php foreach ($fields as $row): ?>
		<div class="row mtop15">
			<?php foreach ($row as $field): ?>
				<div class="col-md-4">
					<label><?php echo _l($labels[$field]); ?>:</label>
					<?php if ($field == 'nutrition'): ?>
						<select name="nutrition" class="form-control">
						  <option value=""><?php echo _l('select'); ?></option>
						  <option value="normal"><?php echo _l('normal'); ?></option>
						  <option value="poor"><?php echo _l('poor'); ?></option>
						  <option value="excessive"><?php echo _l('excessive'); ?></option>
						</select>

					<?php else: ?>
						<textarea name="<?php echo $field; ?>" class="form-control" rows="2" placeholder="<?php echo $labels[$field]; ?>"></textarea>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Mind Tab -->
    <div class="card">
	<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapseMind" aria-expanded="false" aria-controls="collapseMind" style="width: 100%">
      <div class="card-header" id="headingMind">
        <h5 class="mb-0">
            <strong><?php echo _l('mind'); ?></strong> <i class="fa fa-chevron-down toggle-icon" id="icon-mind"></i>
          
        </h5>
      </div>
	  </button>
      <div id="collapseMind" class="<?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingMind" data-parent="#casesheetAccordion">
        <div class="card-body">
          <!-- Mind Content -->
          <div class="form-group mtop20">
			<label for="mind" class="control-label">
				<?php echo _l('mind'); ?>
			</label>
			<textarea id="mind" name="mind" class="form-control tinymce" rows="6"><?= isset($casesheet_data['mind']) ? htmlspecialchars($casesheet_data['mind']) : '' ?></textarea>
		</div>
        </div>
      </div>
    </div>
	
	
	<div class="card">
	<style>
	  .prescription-medicine-table td select.form-control {
		height: auto !important;
		min-height: 36px;
		padding: 6px;
	  }
	</style>

	<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapsePrescription" aria-expanded="false" aria-controls="collapsePrescription" style="width: 100%">
      <div class="card-header" id="headingPrescription">
        <h5 class="mb-0">
            <strong><?php echo _l('prescription'); ?></strong> <i class="fa fa-chevron-down toggle-icon" id="icon-mind"></i>
          
        </h5>
      </div>
	  </button>
      <div id="collapsePrescription" class="<?php echo $isOpen ? 'show' : ''; ?>" aria-labelledby="headingPrescription" data-parent="#casesheetAccordion">
        <div class="card-body">
         
      <?php
	/* $medicine_options = [];
	foreach ($medicines as $m) {
		$medicine_options[] = ['id' => $m['medicine_name'], 'name' => $m['medicine_name']];
	}

	$potency_options = [];
	foreach ($potencies as $p) {
		$potency_options[] = ['id' => $p['medicine_potency_name'], 'name' => $p['medicine_potency_name']];
	}

	$dose_options = [];
	foreach ($doses as $d) {
		$dose_options[] = ['id' => $d['medicine_dose_name'], 'name' => $d['medicine_dose_name']];
	}

	$timing_options = [];
	foreach ($timings as $t) {
		$timing_options[] = ['id' => $t['medicine_timing_name'], 'name' => $t['medicine_timing_name']];
	} */
	?>

	<!--<table class="prescription-medicine-table table" id="prescriptionMedicineTable">
  <thead>
    <tr>
      <th><?= _l('medicine_name'); ?></th>
      <th><?= _l('potency'); ?></th>
      <th><?= _l('dose'); ?></th>
      <th><?= _l('timings'); ?></th>
      <th><?= _l('remarks'); ?></th>
      <th><?= _l('action'); ?></th>
    </tr>
  </thead>
  <tbody id="prescriptionMedicineBody">
    <tr>
      <td>
        <?= render_select('prescription_medicine_name[]', $medicine_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-name'); ?>
      </td>
      <td>
        <?= render_select('prescription_medicine_potency[]', $potency_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-potency'); ?>
      </td>
      <td>
        <?= render_select('prescription_medicine_dose[]', $dose_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-dose'); ?>
      </td>
      <td>
        <?= render_select('prescription_medicine_timings[]', $timing_options, ['id', 'name'], '', '', [], [], '', 'prescription-medicine-timings'); ?>
      </td>
      <td>
        <input type="text" class="form-control prescription-medicine-remarks" name="prescription_medicine_remarks[]" />
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-success btn-sm" id="addPrescriptionRowBtn">
          <i class="fa fa-plus"></i>
        </button>
      </td>
    </tr>
  </tbody>
</table>-->

    

      <table class="medicine-table" id="medicineTable">
        <thead>
          <tr>
            <th><?= _l('medicine_name'); ?></th>
            <th><?= _l('potency'); ?></th>
            <th><?= _l('dose'); ?></th>
            <th><?= _l('timings'); ?></th>
            <th><?= _l('remarks'); ?></th>
            <th><?= _l('action'); ?></th>
          </tr>
        </thead>
        <tbody id="medicineBody">
          <!-- Rows will be dynamically added here -->
        </tbody>
      </table>

    
   
<script>
  // Pass PHP arrays to JS
  const medicineOptions = <?= json_encode($medicine_options); ?>;
  const potencyOptions = <?= json_encode($potency_options); ?>;
  const doseOptions = <?= json_encode($dose_options); ?>;
  const timingOptions = <?= json_encode($timing_options); ?>;

  function buildOptions(options) {
  return `<option value="">--Select--</option>` + options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('');
}


  function createNewRow() {
  const medicineOpts = buildOptions(medicineOptions);
  const potencyOpts = buildOptions(potencyOptions);
  const doseOpts = buildOptions(doseOptions);
  const timingOpts = buildOptions(timingOptions);

  return `
    <tr>
      <td><select name="prescription_medicine_name[]" class="form-control prescription-medicine-name" style="width: 100%">${medicineOpts}</select></td>
      <td><select name="prescription_medicine_potency[]" class="form-control prescription-medicine-potency" style="width: 100%">${potencyOpts}</select></td>
      <td><select name="prescription_medicine_dose[]" class="form-control prescription-medicine-dose" style="width: 100%">${doseOpts}</select></td>
      <td><select name="prescription_medicine_timings[]" class="form-control prescription-medicine-timings" style="width: 100%">${timingOpts}</select></td>
      <td><input type="text" name="prescription_medicine_remarks[]" class="form-control prescription-medicine-remarks" /></td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm remove-row-btn">
          <i class="fa fa-minus"></i>
        </button>
      </td>
    </tr>
  `;
}


  // Add new row event
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('addPrescriptionRowBtn').addEventListener('click', function () {
      document.getElementById('prescriptionMedicineBody').insertAdjacentHTML('beforeend', createNewRow());
      // Initialize any JS plugins if needed here
    });
	
	
	document.getElementById('prescriptionMedicineBody').addEventListener('change', function (e) {
  if (e.target.classList.contains('prescription-medicine-timings')) {
    const row = e.target.closest('tr');
    const allFilled = Array.from(row.querySelectorAll('select')).every(sel => sel.value !== '');
    if (allFilled) {
      document.getElementById('prescriptionMedicineBody').insertAdjacentHTML('beforeend', createNewRow());
    }
  }
});


    // Delegate remove row event (handles dynamically added rows)
	document.getElementById('prescriptionMedicineBody').addEventListener('click', function (e) {
	  if (e.target.closest('.remove-row-btn')) {
		e.target.closest('tr').remove();
	  }
	});

  });
</script>




<div class="row">
			<div class="col-md-4">
              <label for="documents"><?php echo _l('documents'); ?></label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
            </div>
            <div class="col-md-4">
              <label for="medicine_days"><?php echo _l('medicine_period').'(In Days)'; ?><!-- <span class="text-danger">*</span> --></label>
              <input type="number" name="medicine_days" id="medicine_days" class="form-control" min="1">
            </div>
			<div class="col-md-4">
			<?PHP
			foreach($master_settings as $master){
				if($master['title'] == 'medicine_followup_days'){
					$medicine_followup_days = $master['options'];
				}
				
			}
			?>
              <label for="followup_date"><?php echo _l('followup_date').': ('.$medicine_followup_days.' Days)'; ?></label>
			  <input type="date" name="followup_date" id="followup_date" class="form-control" readonly>

            </div>
            <!--<div class="col-md-4">
			<br>
               <?php
			$selected = "";
			echo render_select('patient_status', $patient_status, ['patient_status_id', 'patient_status_name'], ''. _l('patient_status').'', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
			?>
            </div>-->
		</div>


        </div>
		
		
      </div>
	  
	  
    </div>

  </div> <!-- End of Accordion -->

  <!-- Form Actions -->
  <div class="form-actions">
  <?PHP
  /* if($callback_url){
	  ?>
	    <a href="<?php echo admin_url('client/'.$callback_url.'/' . $client->userid . '/tab_casesheet');?>"><button type="button" class="btn btn-primary"><?= _l('back'); ?></button></a>
	  <?PHP
  }else{ */
	 
	   ?>
	   <?php if (!$is_break){ ?>
	   <button type="button" class="btn btn-primary" id="saveCasesheetBtn">
  <?= _l('save'); ?>
</button>
	   <button type="button" class="btn btn-primary" id="saveCallNextBtn">
  <?= _l('save_and_call_next_patient'); ?>
</button>

<?PHP
	   //}
	  
  }
  ?>
  
    <!--<button type="button" class="btn btn-secondary" onclick="toggleCaseSheetForm()"><?= _l('cancel'); ?></button>-->
  </div>

</form>



  </div>

    <br>
	
<?= render_datatable([
		_l('s_no'),
		_l('consulted_date'),
		_l('doctor_name'),
		_l('clinical_observation'),
		_l('suggested_diagnostics'),
		//_l('appointment_type'),
		_l('medicine_days'), 
		_l('pharmacy_medicine_days'), 
		_l('prescription'),
		_l('action'),
	], 'casesheet'); ?>
   

</div>

<?PHP
}
?>
<script>
let consultationStartTime = null;
let consultationInterval = null;
    function toggleCaseSheetForm() {
	   consultationStartTime = new Date(); // start timer
		document.getElementById('consultation_timer_container').style.display = 'inline-block'; // show timer
		startConsultationTimer(); // start counter
		const form = document.getElementById('casesheet-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
	
	function startConsultationTimer() {
		const timerElement = document.getElementById('consultation_timer');

		// Clear any existing interval (avoid duplicates)
		if (consultationInterval) clearInterval(consultationInterval);

		consultationInterval = setInterval(() => {
			const now = new Date();
			const durationSeconds = Math.floor((now - consultationStartTime) / 1000);
			const minutes = Math.floor(durationSeconds / 60);
			const seconds = durationSeconds % 60;

			// Format as MM:SS
			timerElement.textContent = `${pad(minutes)}:${pad(seconds)}`;

			// Update hidden input for form submission
			const hiddenField = document.getElementById('consultation_duration');
			if (hiddenField) {
				hiddenField.value = durationSeconds;
			}
		}, 1000);
	}

	function pad(value) {
		return value < 10 ? '0' + value : value;
	}
</script>



<?PHP
if (staff_can('view_visits', 'customers')) {
?>

<div role="tabpanel" class="tab-pane" id="tab_visits">
    <div>
        <div class="patient-section-title mt-4"><?= _l('visits'); ?></div><br>

<?= render_datatable([
    _l('s_no'),
    _l('visit_id'),
    _l('appointment_date'),
    _l('treatment'),
    _l('visit_status'),
    _l('consulted_date'),
    _l('medicine_given_days'),
    _l('appointment_type'),
    _l('consulted_doctor'),
], 'visit-appointment-table'); ?>

    </div>
</div>
<?PHP
}if (staff_can('create', 'estimates')) {
	
?>

<div role="tabpanel" class="tab-pane" id="tab_estimation">

	<div class="table-responsive">
                <?php
                if(staff_can('create_estimation', 'customers')){
                  ?>
                <button class="btn btn-primary btn-sm" onclick="toggleEstimationForm()" style="float: right; margin-top: 6px; margin-right: 5px;">+<?php echo _l('add_package');?></button>
                  <?php
                }
                ?>
                <!-- Title Section -->
              <div class="patient-section-title mt-4"><?php echo _l('package');?></div>
                <br>

                <!-- Hidden Form -->
                
				<div id="estimation-form" class="card p-3 mb-4" style="display: none; margin-top: -19px;">
				
				<div class="col-md-12 form-group">
  <label><strong><?php echo _l('package_summary'); ?></strong></label>
 
  <div id="durationInfo" class="alert alert-success" style="font-weight: 500;">
  ✅ Dr. <strong><?php echo $latest_casesheet_package->firstname . ' ' . $latest_casesheet_package->lastname; ?></strong>
  has suggested a <strong><?php echo $latest_casesheet_package->duration_value; ?> months</strong> package to heal the problem of
  <strong><?php echo $latest_casesheet_package->treatment_name; ?></strong> for this patient.
  
  Initially, Dr. <strong><?php echo $latest_casesheet_package->firstname . ' ' . $latest_casesheet_package->lastname; ?></strong>
  had given <strong><?php echo $latest_casesheet_package->medicine_days; ?> days</strong> of medicine. Then follow-up visit will be there.
</div>

</div>

<style>
  .action-toggle-btn {
    display: inline-block;
    padding: 10px 20px;
    border: 2px solid #000;
    border-radius: 6px;
    background-color: #fff;
    color: #000;
    font-weight: 500;
    cursor: pointer;
    margin-left: 10px;
  }

  .action-toggle-btn.active {
    background-color: #000;
    color: #fff;
  }

  input[name="package_action"] {
    display: none;
  }
</style>

<div class="col-md-12 form-group mt-3">
  <div class="text-right">
  
  <label><strong><?php echo _l('select_action'); ?></strong></label>
    <!-- Hidden Radios -->
    <input type="radio" name="package_action" id="actionCallback" value="callback">
    <input type="radio" name="package_action" id="actionAddPackage" value="add_package">

    <!-- Styled Buttons -->
    <label class="action-toggle-btn" for="actionCallback" id="btnCallback"><?php echo _l('get_back'); ?></label>
	<?PHP
	$selected_duration = $latest_casesheet_package->duration_value ?? ''; 
	if($selected_duration>0){
	?>
    <label class="action-toggle-btn" for="actionAddPackage" id="btnAddPackage"><?php echo _l('add_package'); ?></label>
	<?PHP
	}
	?>
  </div>
</div>


<!-- Callback Section -->
<div id="callback-section" style="display: none; margin-top: 20px;">
<form id="requestcallLogEntryForm" style="margin-left: 10px">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="patientid" id="clientid" value="<?= $client->userid; ?>">
  <input type="hidden" name="not_registered_patient_week1" id="not_registered_patient_week1" value="not_registered_patient_week1">
  <div class="row">
     <div class="col-md-3">
          
				<label for="criteria_id"><?php echo _l('patient_response'); ?></label>
				<select class="selectpicker form-control criteria_id" data-width="100%" name="criteria_id" id="criteria_id" required>
					<?php
						$allowed_status_names = ['Call back'];

						// Convert allowed status names to lowercase once
						$allowed_status_names_lower = array_map('strtolower', $allowed_status_names);

						foreach ($statuses as $status) {
							if (in_array(strtolower($status['name']), $allowed_status_names_lower)) {
								?>
								<option value="<?= $status['id'] ?>"><?= $status['name'] ?></option>
								<?php
							}
						}
						?>

				</select>
			

			  
			</div>
			<div class="col-md-3">
			  <label><?php echo _l('next_calling_date'); ?></label>
			  <input type="date" name="next_calling_date" class="form-control" required>
			</div>

    <div class="col-md-12 form-group">
      <label><?php echo _l('remarks'); ?></label>
      <textarea name="comments" class="form-control" rows="3"></textarea>
    </div>
  </div>
  <div class="col-md-12 text-right mt-3">
	<button type="submit" class="btn btn-success" style="margin-top: 25px;"><?= _l('Save'); ?></button>
	</div>
	</form>
</div>

<div id="invoice-details-section" style="display: none; margin-top: 20px;" class="border p-3 rounded bg-light">
 <form id="estimationForm" style="margin-left: 10px">
   <input type="hidden" name="patient_treatment_id" id="patient_treatment_id" value="<?= $latest_casesheet_package->patient_treatment_id; ?>">
 <div class="row">
    <div class="col-md-4 form-group">
      <label><?php echo _l('treatment_name'); ?></label>
      <input type="text" name="invoice_treatment_name" class="form-control" value="<?php echo $latest_casesheet_package->treatment_name;?>" readonly>
    </div>
    <div class="col-md-4 form-group">
      <label><?php echo _l('dr_suggested_duration'); ?></label>
      <input type="text" name="dr_duration" class="form-control" value="<?php echo $latest_casesheet_package->duration_value;?> Months" readonly>
    </div>
		<?php $selected_duration = $latest_casesheet_package->duration_value ?? ''; 
		
		
		foreach($master_settings as $master){
				if($master['title'] == 'invoice_acknowledge'){
					$invoice_acknowledge = $master['options'];
				}
				if($master['title'] == 'invoice_minimum_period_settings'){
					$invoice_minimum_period_settings = $master['options'];
				}
				if($master['title'] == 'discount_limit_settings'){
					$discount_limit_settings = $master['options'];
				}
				
			}
			
		?>

	<div class="col-md-4 form-group">
	  <label><?php echo _l('accepted_duration'); ?></label>
	<select name="invoice_period" class="form-control" id="invoicePeriod" style="padding: 1px">
  <?php
    $min = (int)$invoice_minimum_period_settings;
    $max = (int)$selected_duration;

    for ($i = $max; $i >= $min; $i--) {
        $selected = ($i == $selected_duration) ? 'selected' : '';
        echo "<option value=\"$i\" $selected>$i Months</option>";
    }
  ?>
</select>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('invoicePeriod');
    const expiryInput = document.getElementById('expiryDate');

    function updateExpiryDate(months) {
        const today = new Date();
        today.setMonth(today.getMonth() + parseInt(months));

        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        expiryInput.value = `${yyyy}-${mm}-${dd}`;
    }

    // Initial update on load (in case pre-selected)
    if (select.value) {
        updateExpiryDate(select.value);
    }

    // Update on change
    select.addEventListener('change', function () {
        updateExpiryDate(this.value);
    });
});
</script>


	</div>

  </div>

  <div class="mt-2" id="periodWarning" style="display: none;">
     <div class="col-md-12 form-group">
      <label><?php echo _l('remarks'); ?></label>
	  <div id="durationWarning" class="alert alert-warning" style="font-weight: 500; display: none;">
  ⚠️ Why are you creating a <span id="selectedMonths"></span> package by invoice period when Dr.<?php echo $latest_casesheet_package->firstname.' '.$latest_casesheet_package->lastname;?> suggested for <strong><?= $selected_duration ?> months</strong>?
</div>
      <textarea name="adminnote" class="form-control" rows="3"></textarea>
    </div>
  </div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const invoiceSelect = document.getElementById('invoicePeriod');
    const warningBox = document.getElementById('durationWarning');
    const selectedSpan = document.getElementById('selectedMonths');
    const suggested = <?= $selected_duration ?>;

    function checkDurationWarning() {
      const selected = parseInt(invoiceSelect.value);
      if (selected !== suggested) {
        selectedSpan.textContent = selected + ' months';
        warningBox.style.display = 'block';
      } else {
        warningBox.style.display = 'none';
      }
    }

    // Initial check (in case it's not equal on load)
    checkDurationWarning();

    // Listen for changes
    invoiceSelect.addEventListener('change', checkDurationWarning);
  });
</script>
 
 <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="clientid" id="clientid" value="<?= $client->userid; ?>">
  
  <?php
	   $next_estimate_number = get_option('next_estimate_number');
	   $format               = get_option('estimate_number_format');
	
		if (isset($estimate)) {
			$format = $estimate->number_format;
		}

	   $prefix = get_option('estimate_prefix');

	   if ($format == 1) {
		   $__number = $next_estimate_number;
		   if (isset($estimate)) {
			   $__number = $estimate->number;
			   $prefix   = '<span id="prefix">' . $estimate->prefix . '</span>';
		   }
	   } elseif ($format == 2) {
		   if (isset($estimate)) {
			   $__number = $estimate->number;
			   $prefix   = $estimate->prefix;
			   $prefix   = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($estimate->date)) . '</span>/';
		   } else {
			   $__number = $next_estimate_number;
			   $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
		   }
	   } elseif ($format == 3) {
		   if (isset($estimate)) {
			   $yy       = date('y', strtotime($estimate->date));
			   $__number = $estimate->number;
			   $prefix   = '<span id="prefix">' . $estimate->prefix . '</span>';
		   } else {
			   $yy       = date('y');
			   $__number = $next_estimate_number;
		   }
	   } elseif ($format == 4) {
		   if (isset($estimate)) {
			   $yyyy     = date('Y', strtotime($estimate->date));
			   $mm       = date('m', strtotime($estimate->date));
			   $__number = $estimate->number;
			   $prefix   = '<span id="prefix">' . $estimate->prefix . '</span>';
		   } else {
			   $yyyy     = date('Y');
			   $mm       = date('m');
			   $__number = $next_estimate_number;
		   }
	   }

	   $_estimate_number     = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
	   $isedit               = isset($estimate) ? 'true' : 'false';
	   $data_original_number = isset($estimate) ? $estimate->number : 'false';
	   ?>
				   
   <input type="hidden" name="number" class="form-control" value="<?php echo e($_estimate_number); ?>" data-isedit="<?php echo e($isedit); ?>" data-original-number="<?php echo e($data_original_number); ?>">
   <input type="hidden" name="billing_street" id="billing_street" value="1">
	<?php
	// Filter treatments with empty or 0 estimation_id
	$available_treatments = array_filter($patient_treatment, function($t) {
		return empty($t['estimation_id']) || $t['estimation_id'] == 0;
	});

	$today = new DateTime();
	if ($selected_duration > 0) {
		$today->modify("+$selected_duration months");
	}
	$expiry_date = $today->format('Y-m-d');
	?>


		<input type="hidden" name="expirydate" id="expiryDate" class="form-control">

	  <input type="hidden" name="currency" class="form-control" value="1">

	  <input type="hidden" name="status" class="form-control" value="1">

	  <input type="hidden" name="sale_agent" class="form-control" value="<?php echo get_staff_user_id();?>">

	  <input type="hidden" name="show_quantity_as" class="form-control" value="1">
							
		
	<input type="hidden" name="date" class="form-control" value="<?php echo date('Y-m-d');?>">
  <div class="row">
  <!--<div class="col-md-4 form-group">
	  <label>Expiry Date</label>
	  <input type="date" name="expirydate" class="form-control" value="<?= $expiry_date; ?>" style="width: 100%">

	</div>-->
   
	</div>

    
	<input type="hidden" name="newitems[1][description]" id="itemDescription" class="form-control" readonly>
 <input type="hidden" name="newitems[1][qty]" id="itemQty" class="form-control" value="1">

      <input type="hidden" step="0.01" name="newitems[1][rate]" id="itemRate" class="form-control" readonly>
    

    
 <?php 
 //echo $latest_casesheet->treatment_type_id;?>
  <div class="row mt-3">
   <?php
$show_package_dropdown = false;
$matched_package_item = null;

foreach ($items as $_group_items) {
    if (isset($_group_items[0]['group_name']) && $_group_items[0]['group_name'] == 'Package') {
        foreach ($_group_items as $item) {
            if ($item['id'] == $latest_casesheet_package->treatment_type_id) {
                $show_package_dropdown = true;
                $matched_package_item = $item;
                break 2; // Exit both loops
            }
        }
    }
}
?>

<?php if ($show_package_dropdown && $matched_package_item): ?>
  <div class="col-md-4 form-group">
    <label><span style="color: #f00">*</span><?php echo _l('package'); ?></label>
    <select name="item_select" class="form-control selectpicker" data-live-search="true" id="itemSelect" required>
      <option value=""></option>
      <option value="<?= e($matched_package_item['id']); ?>">
        <?= e($matched_package_item['description']); ?> (<?= e(app_format_number($matched_package_item['rate'])); ?>)
      </option>
    </select>
  </div>
<?php endif; ?>

  <div class="col-md-4 form-group">
      <label><?php echo _l('company_price'); ?></label>
      <input type="text" name="subtotal" id="itemTotal" class="form-control" readonly>
    </div>
	<?PHP
	/*
	?>
   <div class="col-md-4 form-group">
  <label><?php echo _l('coupon_discount'); ?></label>
  <div class="input-group" id="discount-wrapper">
    <!-- Percentage Input -->
    <?php
  $max_discount = isset($discount_limit_settings) && $discount_limit_settings > 0
    ? $discount_limit_settings
    : 100; // default fallback
?>

<input type="number"
  class="form-control input-discount-percent"
  name="discount_percent"
  min="0"
  max="<?php echo $max_discount; ?>"
  value="0">


    <!-- Fixed Input -->
    <input type="number" class="form-control input-discount-fixed d-none" name="discount_total" >


    <!-- Dropdown for Type -->
    <div class="input-group-addon">
      <div class="dropdown">
        <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
          data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
          <span class="discount-total-type-selected">%</span>
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" id="discount-total-type-dropdown" aria-labelledby="dropdown_menu_tax_total_type">
          <li><a href="#" class="discount-total-type discount-type-percent selected">%</a></li>
          <!--<li><a href="#" class="discount-total-type discount-type-fixed">₹ Fixed Amount</a></li>-->
        </ul>
      </div>
    </div>
  </div>
</div>
<?PHP
*/
?>
<div class="col-md-4 form-group">
  <label><?php echo _l('coupon_discount'); ?></label>
  <div class="input-group" id="discount-wrapper">
    <!-- Percentage Input -->
    <?php
  $max_discount = isset($discount_limit_settings) && $discount_limit_settings > 0
    ? $discount_limit_settings
    : 100; // default fallback
?>

<input type="number"
  class="form-control input-discount-percent"
  name="discount_percent"
  min="0"
  max="<?php echo $max_discount; ?>"
  value="0" readonly>


    <!-- Fixed Input -->
    <input type="hidden" class="form-control input-discount-fixed d-none" name="discount_total" >


    <!-- Dropdown for Type -->
    <div class="input-group-addon">
      <div class="dropdown">
        <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
          data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
          <span class="discount-total-type-selected">%</span>
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" id="discount-total-type-dropdown" aria-labelledby="dropdown_menu_tax_total_type">
          <li><a href="#" class="discount-total-type discount-type-percent selected">%</a></li>
          <!--<li><a href="#" class="discount-total-type discount-type-fixed">₹ Fixed Amount</a></li>-->
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="col-md-4 form-group">
  <label><?php echo _l('Additional'); ?></label>
  <input type="number" class="form-control" id="adjustmentField" name="adjustment" value="0">
</div>

    <!--<div class="col-md-4 form-group">
      <label>Subtotal</label>
     
    </div>-->
 <input type="hidden" class="form-control" id="subtotalField" readonly>
    <div class="col-md-4 form-group">
      <label><?php echo _l('suggested_price'); ?></label>
      <input type="text" class="form-control" name="total" id="finalTotalField" readonly>
    </div>
	
	
	  <!-- Pay Now Field -->
	  <div class="col-md-4 form-group">
		<label><?php echo _l('pay_now'); ?></label>
		<input type="number" class="form-control" name="paying_amount" value="0" id="paying_amount"
		<?= ($invoice_acknowledge === 'before_payment') ? 'readonly' : ''; ?>>

	  </div>
	  
	  <div class="col-md-4 form-group">
      <label><?php echo _l('utr_no'); ?></label>
      <input type="text" name="utr_no" id="utr_no" class="form-control">
    </div>
	<div class="col-md-4">
		  <div class="form-group">
		  <label><span style="color: #f00">*</span> <?= _l('payment_category'); ?></label>
			  <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true" required>
				<option value=""></option>
				<?php foreach ($appointment_type as $app): ?>
				  <option value="<?= $app['appointment_type_id']; ?>"><?= $app['appointment_type_name']; ?></option>
				<?php endforeach; ?>
			  </select>
		  </div>
		</div>
	  
	  <input type="hidden" name="invoice_acknowledge" value="<?php echo $invoice_acknowledge;?>">
		
	 <div class="col-md-4 form-group">
  <label for="paymentmode" class="control-label">
    <?= _l('payment_mode'); ?>
    <?php if ($invoice_acknowledge != 'before_payment') : ?>
      <span class="text-danger">*</span>
    <?php endif; ?>
  </label>

  <select class="selectpicker form-control"
    name="paymentmode"
    data-width="100%"
    data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
    <?= ($invoice_acknowledge != 'before_payment') ? 'required' : ''; ?>>
    
    <option value=""><?= _l('select_payment_mode'); ?></option>

    <?php foreach ($payment_modes as $mode): ?>
      <?php if ($mode['active'] == 1): ?>
        <option value="<?= $mode['id']; ?>">
          <?= htmlspecialchars($mode['name']); ?>
        </option>
      <?php endif; ?>
    <?php endforeach; ?>
  </select>
</div>
<div class="col-md-3">
	 <?= render_select(
			'branch_id', // name
			$branch,   // options array
			['id', 'name'], // option keys
			_l('branch') . '*', // label
			isset($home_branch_id) ? $home_branch_id : ($patient['groupid'] ?? ''), // selected
			[
				'id' => 'branch_id', // 👈 Add your ID here
				'data-none-selected-text' => _l('dropdown_non_selected_tex'),
				'required' => 'required'
			]
		) ?>

	  </div>


	  <!-- Submit Button -->
	 <div class="col-md-4 form-group">
	 <br>
	 
  <button type="submit" class="btn btn-success" style="margin-top: 6px"><?php echo _l('save_and_pay'); ?></button>
</div>
  </div>


  <br>
  <br>
  <br>

</form>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    const itemSelect = document.getElementById('itemSelect');
    const itemDescription = document.getElementById('itemDescription');
    const itemQty = document.getElementById('itemQty');
    const itemRate = document.getElementById('itemRate');
    const itemTotal = document.getElementById('itemTotal');
    const subtotalField = document.getElementById('subtotalField');
    const finalTotalField = document.getElementById('finalTotalField');
    const adjustmentField = document.getElementById('adjustmentField'); // New adjustment input

    const discountPercent = document.querySelector('.input-discount-percent');
    const discountFixed = document.querySelector('.input-discount-fixed');
    const selectedTypeLabel = document.querySelector('.discount-total-type-selected');

    let lastPercentValue = '';
    let lastFixedValue = '';

    // Save current discount field values on input
    discountPercent.addEventListener('input', function () {
      lastPercentValue = this.value;
      updateTotals();
    });

    discountFixed.addEventListener('input', function () {
      lastFixedValue = this.value;
      updateTotals();
    });

    // Update totals when adjustment changes
    adjustmentField.addEventListener('input', updateTotals);

    // On item change, update description and rate
    itemSelect.addEventListener('change', function () {
      const selectedText = itemSelect.options[itemSelect.selectedIndex].textContent.trim();
      const description = selectedText.replace(/\(.*?\)/, '').trim();
      const rateMatch = selectedText.match(/\(([\d,\.]+)\)/);
      const rate = rateMatch ? parseFloat(rateMatch[1].replace(/,/g, '')) : 0;

      itemDescription.value = description;
      itemRate.value = rate;

      updateTotals();
    });

    itemQty.addEventListener('input', updateTotals);

    // Handle discount type switch
    document.querySelectorAll('.discount-total-type').forEach(el => {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.discount-total-type').forEach(btn => btn.classList.remove('selected'));
        this.classList.add('selected');

        const isPercent = this.classList.contains('discount-type-percent');
        selectedTypeLabel.textContent = this.textContent;

        if (isPercent) {
          discountPercent.classList.remove('d-none');
          discountPercent.value = lastPercentValue || '';
          discountFixed.classList.add('d-none');
        } else {
          discountFixed.classList.remove('d-none');
          discountFixed.value = lastFixedValue || '';
          discountPercent.classList.add('d-none');
        }

        updateTotals();
      });
    });

    function updateTotals() {
      const qty = parseFloat(itemQty.value) || 0;
      const rate = parseFloat(itemRate.value) || 0;
      const total = qty * rate;

      itemTotal.value = total.toFixed(2);
      subtotalField.value = total.toFixed(2);

      let discountAmount = 0;
      const isPercent = document.querySelector('.discount-type-percent').classList.contains('selected');

      if (isPercent) {
        const percent = parseFloat(discountPercent.value) || 0;
        discountAmount = (total * percent) / 100;
      } else {
        const fixed = parseFloat(discountFixed.value) || 0;
        discountAmount = fixed;
      }

      const adjustment = parseFloat(adjustmentField.value) || 0;

      let finalTotal = total - discountAmount + adjustment;
      finalTotal = finalTotal < 0 ? 0 : finalTotal;

      finalTotalField.value = finalTotal.toFixed(2);
    }

    updateTotals();
  });
</script>






<style>
  .discount-type-btn.selected {
    background-color: #007bff;
    color: white;
  }
  
</style>


</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const callbackBtn = document.getElementById('btnCallback');
    const addPackageBtn = document.getElementById('btnAddPackage');
    const callbackRadio = document.getElementById('actionCallback');
    const addPackageRadio = document.getElementById('actionAddPackage');
    const callbackSection = document.getElementById('callback-section');
    const invoiceSection = document.getElementById('invoice-details-section');
    const invoicePeriod = document.getElementById('invoicePeriod');
    const periodWarning = document.getElementById('periodWarning');

    // Function to toggle sections
    function updateSelection() {
      if (callbackRadio.checked) {
        callbackBtn.classList.add('active');
        addPackageBtn.classList.remove('active');
        callbackSection.style.display = 'block';
        invoiceSection.style.display = 'none';
      } else if (addPackageRadio.checked) {
        addPackageBtn.classList.add('active');
        callbackBtn.classList.remove('active');
        callbackSection.style.display = 'none';
        invoiceSection.style.display = 'block';
      }
    }

    // Button click events
    callbackBtn.addEventListener('click', function () {
      callbackRadio.checked = true;
      updateSelection();
    });

    addPackageBtn.addEventListener('click', function () {
      addPackageRadio.checked = true;
      updateSelection();
    });

    // Invoice period validation
    invoicePeriod.addEventListener('change', function () {
      const selected = parseInt(this.value);
      const suggested = 24; // assume doctor suggested 24 months
      periodWarning.style.display = selected < suggested ? 'block' : 'none';
    });

    // Optional: Init with nothing selected
    callbackSection.style.display = 'none';
    invoiceSection.style.display = 'none';
  });
</script>
  <script>
				const itemData = <?= json_encode(array_column(array_merge(...array_values($items)), null, 'id')); ?>;

				document.getElementById('itemSelect').addEventListener('change', function () {
				  const selectedId = this.value;
				  if (itemData[selectedId]) {
					const item = itemData[selectedId];
					document.getElementById('itemDescription').value = item.description;
					document.getElementById('itemRate').value = item.rate;

					const qty = parseFloat(document.getElementById('itemQty').value) || 1;
					document.getElementById('itemTotal').value = (qty * parseFloat(item.rate)).toFixed(2);
				  } else {
					document.getElementById('itemDescription').value = '';
					document.getElementById('itemRate').value = '';
					document.getElementById('itemTotal').value = '';
				  }
				});

				document.getElementById('itemQty').addEventListener('input', function () {
				  const selectedId = document.getElementById('itemSelect').value;
				  if (itemData[selectedId]) {
					const qty = parseFloat(this.value) || 1;
					const rate = parseFloat(itemData[selectedId].rate);
					document.getElementById('itemTotal').value = (qty * rate).toFixed(2);
				  }
				});
				</script>
				<script>
					$(document).ready(function () {
					  $('#treatmentSelect').on('change', function () {
						const selectedOption = $(this).find('option:selected');
						const duration = parseInt(selectedOption.data('duration'));

						if (!isNaN(duration)) {
						  const today = new Date();
						  today.setMonth(today.getMonth() + duration);

						  const yyyy = today.getFullYear();
						  const mm = String(today.getMonth() + 1).padStart(2, '0');
						  const dd = String(today.getDate()).padStart(2, '0');

						  const newExpiryDate = `${yyyy}-${mm}-${dd}`;
						  $('input[name="expirydate"]').val(newExpiryDate);
						}
					  });
					});
					</script>

</div>

                
              </div>

                <br>
				<?= render_datatable([
				_l('estimate_dt_table_heading_number'),
				_l('estimate_dt_table_heading_amount'),
				_l('branch_name'),
				_l('treatment'),
				_l('payment_category'),
				// _l('estimate_dt_table_heading_client'), // optional, currently commented
				_l('estimate_dt_table_heading_date'),
				_l('estimate_dt_table_heading_expirydate'),
				_l('estimate_dt_table_heading_status'),
				_l('remarks'),
				_l('action'),
			], 'estimates-table'); ?>


              </div>

<?PHP
}if (staff_can('view_payments', 'customers')) {
?>
<div role="tabpanel" class="tab-pane" id="tab_payments">
    <!--<p><?= _l('no_payments_recorded'); ?></p>-->
    <div class="table-responsive">
        <div class="patient-section-title mt-4"><?= _l('invoice'); ?></div><br>
		<div id="invoicePaymentFormSection" style="display: none;">
  <div class="text-end mb-3">
    <button type="button" class="btn btn-secondary" id="btnBackToTable">← <?php echo _l('back_to_list'); ?>
</button>
  </div>

  <?= form_open(admin_url('invoices/record_payment'), ['id' => 'invoice_payment_form', 'target' => '_blank']); ?>

    
	<input type="hidden" name="invoiceid" id="invoice_id_hidden">
    <div class="row">
      <div class="form-group col-md-4" app-field-wrapper="amount">
        <label for="invoice_payment_amount" class="control-label">
          <span style="color: #f00">*</span><?= _l('payment_amount'); ?>
        </label>
        <input type="number"
               id="invoice_payment_amount"
               name="amount"
               class="form-control"
               min="1"
               step="0.01"
               required>
        <small class="text-muted" id="invoice_payment_amount_hint" style="display:none;"></small>
        <small class="text-danger" id="invoice_payment_amount_error" style="display:none;"></small>
      </div>
      <!--<div class="form-group col-md-4">
        <?= render_date_input('date', 'Payment Date', _d(date('Y-m-d'))); ?>
      </div>-->
	 
      <div class="form-group col-md-4">
    <label for="payment_method" class="control-label">
        <span style="color: #f00">*</span><?php echo _l('payment_method'); ?>
    </label>
    <select class="selectpicker" 
            name="paymentmode" 
            id="paymentmode"
            data-width="100%" 
            data-live-search="true"
            data-container="body"
            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" 
            required>
        <?php foreach ($payment_modes as $mode) { ?>
            <option value="<?= e($mode['id']); ?>" data-name="<?= e($mode['name']); ?>">
                <?= e($mode['name']); ?>
            </option>
        <?php } ?>
    </select>
</div>

<div class="form-group col-md-3">
    <?= render_input('utr_no', 'UTR No', '', 'text', ['id' => 'utr_no']); ?>
</div>
<script>
$(document).ready(function () {
    function toggleUtrRequirement() {
        var selected = $('#paymentmode option:selected').data('name');
		//alert(selected);
        if (selected === "Cash") {
            $('#utr_no').prop('required', false);
        } else {
            $('#utr_no').prop('required', true);
        }
    }

    // Initial check
    toggleUtrRequirement();

    // On change
    $('#paymentmode').on('change', toggleUtrRequirement);
});
</script>

      <!--<div class="form-group col-md-6">
        <?= render_input('transactionid', 'Transaction ID'); ?>
      </div>-->
      <div class="form-group col-md-3">
        <?= render_input('note', 'Note'); ?>
      </div>
		  <div class="form-group">
		  <button type="submit" class="btn btn-success"  style="margin-top: 26px"><?php echo _l('submit_payment'); ?> </button>
		</div>
    </div>

    
  <?= form_close(); ?>
</div>


		

		<div id="payment_table">
		<?= render_datatable([
			_l('S.No'),
			_l('invoice_number'),
			_l('branch_name'),
			_l('payment_category'),
			_l('amount'),
			_l('paid_amount'),
			_l('due_amount'),
			_l('date'),
			_l('package'),
			_l('due_date'),
			_l('status'),
			_l('action'),
		], 'invoice-payments'); ?>
        

        <br>
		
        <div class="patient-section-title mt-4"><?= _l('payment_receipts'); ?></div><br>
<?= render_datatable([
    _l('payment_number'),
    _l('branch'),
    _l('utr_no'),
    _l('receipt_no'),
    _l('payment_mode'),
    _l('date'),
    _l('received_by'),
    _l('amount'),
    _l('action'),
], 'payment-table'); ?>
       
		</div>
    </div>
</div>

<script>
  $(function () {
    var $invoicePaymentForm = $('#invoice_payment_form');

    if (!$invoicePaymentForm.length) {
      return;
    }

    var $amountInput = $('#invoice_payment_amount');
    var $errorMessage = $('#invoice_payment_amount_error');
    var $hint = $('#invoice_payment_amount_hint');

    function resetInvoicePaymentValidation() {
      $amountInput.removeClass('is-invalid');
      $errorMessage.hide().text('');
    }

    function setInvoicePaymentHint(due) {
      if (typeof due === 'number' && !isNaN(due) && due > 0) {
        $hint.text("<?= _l('due_amount'); ?>: " + due.toFixed(2)).show();
      } else {
        $hint.hide().text('');
      }
    }

    window.updateInvoicePaymentDueAmount = function (due) {
      if (typeof due === 'number' && !isNaN(due)) {
        var formatted = due.toFixed(2);
        $amountInput.attr('data-due', formatted);
        $amountInput.attr('max', formatted);
        setInvoicePaymentHint(due);
      } else {
        $amountInput.removeAttr('data-due');
        $amountInput.removeAttr('max');
        setInvoicePaymentHint(null);
      }

      $amountInput.val('');
      resetInvoicePaymentValidation();
    };

    function getDueAmount() {
      var due = parseFloat($amountInput.attr('data-due'));
      return isNaN(due) ? null : due;
    }

    function validateInvoicePaymentAmount(showFeedback) {
      var value = parseFloat($amountInput.val());

      if (isNaN(value)) {
        resetInvoicePaymentValidation();
        return true;
      }

      var due = getDueAmount();
      if (due !== null && value > due + 0.0001) {
        if (showFeedback) {
          $amountInput.addClass('is-invalid');
          var message = "<?= _l('paying_amount_cannot_exceed_due_amount'); ?>";
          message += ' (<?= _l('due_amount'); ?>: ' + due.toFixed(2) + ')';
          $errorMessage.text(message).show();
        }
        return false;
      }

      resetInvoicePaymentValidation();
      return true;
    }

    $amountInput.on('input', function () {
      if (!$amountInput.val()) {
        resetInvoicePaymentValidation();
        return;
      }
      validateInvoicePaymentAmount(true);
    });

    $invoicePaymentForm.on('submit', function (e) {
      if (!validateInvoicePaymentAmount(true)) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }

      // Wait briefly to ensure the new tab opens
      setTimeout(function () {
        location.reload();
      }, 500);

      return true;
    });

    $(document).on('click', '#btnBackToTable', function () {
      $amountInput.val('');
      $amountInput.removeAttr('data-due');
      $amountInput.removeAttr('max');
      setInvoicePaymentHint(null);
      resetInvoicePaymentValidation();
    });
  });
</script>

<?PHP

}if (staff_can('view_feedback', 'customers')) {
?>
          <div role="tabpanel" class="tab-pane" id="tab_feedback" style="min-height: 300px;">

            
                <button class="btn btn-primary btn-sm" onclick="toggleFeedbackForm()" style="float: right; margin-top: 6px; margin-right: 5px;"><?php echo _l('send_request'); ?>
</button>

				<!-- Title Section -->
				<div class="patient-section-title mt-4"><?php echo _l('feedback'); ?>
</div>

				<div id="feedback-form" class="card p-3 mb-4" style="display: none;">
				<br>
				  <form id="feedbackEntryForm" method="post">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
					<input type="hidden" name="patientid" value="<?= $client->userid; ?>">
				
					<div class="row">
					  <div class="col-md-3">
						<label><strong><?php echo _l('do_you_want_to_send_email'); ?>
</strong></label><br>
						<div class="form-check form-check-inline">
						  <input class="form-check-input" type="radio" name="send_email" value="1" id="emailYes">
						  <label class="form-check-label" for="emailYes"><?php echo _l('yes'); ?>
</label>
						   <input class="form-check-input" type="radio" name="send_email" value="0" id="emailNo" checked>
						  <label class="form-check-label" for="emailNo"><?php echo _l('no'); ?>
</label>
						</div>
					  </div>

					  <div class="col-md-3">
						<label><strong><?php echo _l('do_you_want_to_send_sms'); ?>
</strong></label><br>
						<div class="form-check form-check-inline">
						  <input class="form-check-input" type="radio" name="send_sms" value="1" id="smsYes">
						  <label class="form-check-label" for="smsYes"><?php echo _l('yes'); ?>
</label>
						    <input class="form-check-input" type="radio" name="send_sms" value="0" id="smsNo" checked>
						  <label class="form-check-label" for="smsNo"><?php echo _l('no'); ?>
</label>
						</div>
					  </div>

					  <div class="col-md-3">
						<label><strong><?php echo _l('do_you_want_to_send_whatsapp'); ?>
</strong></label><br>
						<div class="form-check form-check-inline">
						  <input class="form-check-input" type="radio" name="send_whatsapp" value="1" id="whatsappYes">
						  <label class="form-check-label" for="whatsappYes"><?php echo _l('yes'); ?>
</label>
						  <input class="form-check-input" type="radio" name="send_whatsapp" value="0" id="whatsappNo" checked>
						  <label class="form-check-label" for="whatsappNo"><?php echo _l('no'); ?>
</label>
						</div>
					  </div>
					  <?php
						echo render_select(
						  'feedback_id',                     // Name
						  $testimonials,                     // Options array
						  ['id', ['title']],           // Value field, Label field
						  'Select Feedback',                 // Label text
						  '',                                // Selected value
						  ['required' => 'required'],        // Select attributes (required here)
						  [],                                // Label attributes
						  'col-md-3'                         // Wrapper class
						);
						?>

					</div>

					<div class="row">
					<br>
					  <div class="col-md-12 text-right">
						<button type="submit" id="feedbackButton" class="btn btn-success"><?= _l('share'); ?></button>
					  </div>
					</div>

				  </form>
				</div>
<?= render_datatable([
    _l('feedback_title'),
    _l('feedback_description'),
    _l('view'),
], 'feedback-table'); ?>

				

			
          </div>
<?PHP
}
if (staff_can('view_call_log', 'customers')) {
	?>
          <div role="tabpanel" class="tab-pane" id="tab_calls">
              <div class="table-responsive">
                <?php
                if(staff_can('create_call_log', 'customers')){
                  ?>
                <!--<a href="<?= admin_url('client/add_client/'.$client->userid.'/Patient'); ?>"><button class="btn btn-success btn-sm" style="float: right; margin-top: 6px; margin-right: 3px;"><?php echo _l('book_appointment'); ?>
</button></a>-->
                <button class="btn btn-primary btn-sm" onclick="toggleCallLogForm()" style="float: right; margin-top: 6px; margin-right: 5px;"><?php echo _l('add_call'); ?>
</button>
                  <?php
                }
                ?>
                <!-- Title Section -->
              <div class="patient-section-title mt-4"><?php echo _l('call_logs'); ?>
</div>
                <br>

                <!-- Hidden Form -->
                <div id="call-log-form" class="card p-3 mb-4" style="display: none;">
                <form id="callLogEntryForm">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                  <input type="hidden" name="patientid" value="<?= $client->userid; ?>">
                  <div class="row">
                    <div class="col-md-3">
				<?= render_select(
				'groupid',
				$branch,
				['id', 'name'],
				_l('branch') . '*',
				isset($current_branch_id) ? $current_branch_id : ($patient['groupid'] ?? ''),
				['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => 'required']
			) ?>
            </div>
                    <?PHP
					$select_options = array_map(function ($type) {
						return [
							'id' => $type['criteria_id'],
							'name' => $type['criteria_name'],
						];
					}, $criteria);
					?>
					<div class="col-md-3">
    <label for="criteria_id">
        <span style="color: #f00">*</span> <?php echo _l('call_type'); ?>
    </label>
    <select class="form-control criteria_id_val" name="criteria_id" style="padding:6px 8px; line-height:1.5;"  id="criteria_id" required>
        <option value=""><?php echo _l('select_call_type'); ?></option>
        <?php
            // Example: allow only specific call types
            $allowed_call_types = ['General Calling', 'Medicine Calling', 'Feedback Calling'];

            // Convert allowed names to lowercase once
            $allowed_call_types_lower = array_map('strtolower', $allowed_call_types);

            foreach ($select_options as $option) {
                //if (in_array(strtolower($option['name']), $allowed_call_types_lower)) {
                    ?>
                    <option value="<?= $option['id'] ?>"
                        <?= (isset($selected_criteria) && $selected_criteria == $option['id']) ? 'selected' : '' ?>>
                        <?= $option['name'] ?>
                    </option>
                    <?php
                //}
            }
        ?>
    </select>
</div>

					<!-- Container where dynamic fields will be added -->
					<div id="medicine-calling-fields" class="row" style="display:none; margin-top:10px;">
						<div class="col-md-3">
							<label>Pharmacy Medicine Days <span style="color:red;">*</span></label>
							<input type="number" name="pharmacy_medicine_days" id="pharmacy_medicine_days" class="form-control">
						</div>
						<div class="col-md-3">
							<label>Patient Took Medicine Days <span style="color:red;">*</span></label>
							<input type="number" name="patient_took_medicine_days" id="patient_took_medicine_days" class="form-control">
						</div>
					</div>


					<script>
					$(document).ready(function() {
						$('.criteria_id_val').on('change', function() {
							var selectedText = $.trim($(".criteria_id_val option:selected").text()); // ✅ trim spaces

							if (selectedText === "Medicine Calling") {
								$("#medicine-calling-fields").show();
								$("#pharmacy_medicine_days, #patient_took_medicine_days").attr("required", true);
							} else {
								$("#medicine-calling-fields").hide();
								$("#pharmacy_medicine_days, #patient_took_medicine_days").removeAttr("required");
							}
						});
					});
					</script>

					
					<div class="col-md-3">
						<label for="patient_response_id"><span style="color: #f00">*</span> <?php echo _l('patient_response');
						 ?>
</label>
						<select class="form-control patient_response_id" name="patient_response_id" style="padding: 0" id="patient_response_id_1" required>
							<option value=""><?php echo _l('select_response'); ?>
</option>
							<?php
							
								if($staff_data->role_name == "Service Doctor"){
									
									$allowed_status_names = ['No Response', 'Call back', 'No Feedback', 'On Appointment', 'Call Received'];
								}else{
								
									$allowed_status_names = ['No Response', 'Call back', 'No Feedback', 'On Appointment', 'Paid Appointment', 'Call Received'];	
								}

								// Convert allowed status names to lowercase once
								$allowed_status_names_lower = array_map('strtolower', $allowed_status_names);

								foreach ($statuses as $status) {
									if (in_array(strtolower($status['name']), $allowed_status_names_lower)) {
										?>
										<option value="<?= $status['id'] ?>"><?= $status['name'] ?></option>
										<?php
									}
								}
								?>

						</select>
					</div>
                    <div class="col-md-3">
                      <label><?php echo _l('next_calling_date'); ?>

					  <span id="next_calling_required_indicator" style="color:red; display: none;">*</span>
					  </label>
					  <?php
						$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
						?>
                      <input type="datetime-local" name="next_calling_date" id="next_calling_date" class="form-control" min="<?php echo $now; ?>">
                    </div>
					
		
                   
                  <div class="col-md-3">
                      <?php
						$options = [
							['id' => 'Satisfied Patient (80%, 90% & 100%)', 'name' => 'Satisfied Patient (80%, 90% & 100%)'],
							['id' => 'Happy Patient (50%, 60% & 70%)', 'name' => 'Happy Patient (50%, 60% & 70%)'],
							['id' => 'Unsatisfied Patient (30% & 40%)', 'name' => 'Unsatisfied Patient (30% & 40%)'],
							['id' => 'Dissatisfied Patient (0%, 10% & 20%)', 'name' => 'Dissatisfied Patient (0%, 10% & 20%)'],
						];

						echo render_select('better_patient', $options, ['id', 'name'], 'Better Patient', isset($patient) ? $patient->better_patient : '', []);
						?>

                      
                    </div>
					<style>
.lead_with_doctor_section.hide,
.appointment_payment_section.hide {
    display: none;
}

</style>

<div class="lead_with_doctor_section" style="margin-top:-10px; margin-left: 10px">
	<div class="row" style="padding: 10px;">
  <!-- Doctor -->
  <div class="col-md-3">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('doctor'); ?></label>
      <select class="form-control selectpicker" name="doctor_id" id="doctor_id_1" data-live-search="true">
        <option value=""></option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?= $doc['staffid']; ?>"><?= $doc['firstname'] . ' ' . $doc['lastname']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Appointment Date -->
 <div class="col-md-3">
  <div class="form-group">
  <label class="form-label"><span style="color: #f00">*</span> <?= _l('appointment_date') ?></label>
     <?php
	$now = date('Y-m-d\TH:i'); // Correct format for datetime-local input
	?>
	<input type="datetime-local" class="form-control" name="appointment_date" value="<?php echo $now; ?>" min="<?php echo $now; ?>" required>
  </div>
</div>


<div class="col-md-3">
  <div class="form-group">
  <label><span style="color: #f00">*</span> <?= _l('appointment_type'); ?></label>
      <select class="form-control selectpicker" name="appointment_type_id" id="appointment_type_id" data-live-search="true">
        <option value=""></option>
        <?php foreach ($appointment_type as $app): ?>
          <option value="<?= $app['appointment_type_id']; ?>"><?= $app['appointment_type_name']; ?></option>
        <?php endforeach; ?>
      </select>
  </div>
</div>
  <!-- Treatment -->
 <div class="col-md-3">
    <div class="form-group">
      <label><span style="color: #f00">*</span> <?= _l('treatment'); ?></label>
      <select name="treatment_id" class="form-control selectpicker" data-live-search="true" id="treatment_id_1">
        <option value=""></option>
        <?php foreach ($items as $group_id => $_items) {
          if (isset($_items[0]['group_name']) && $_items[0]['group_name'] == 'Package') {
            foreach ($_items as $item) { ?>
              <option value="<?= e($item['id']); ?>"><?= e($item['description']); ?></option>
            <?php }
          }
        } ?>
      </select>
    </div>
  </div>

  <!-- Consultation Fee -->
  <div class="col-md-3">
  <div class="form-group">
	<label><span style="color: #f00">*</span> <?= _l('consultation_fees'); ?></label>
	<?php
	$has_consultation_fee = false;
	foreach ($items as $_group_items) {
	  if (isset($_group_items[0]['group_name']) && $_group_items[0]['group_name'] == "Consultation Fee") {
		$has_consultation_fee = true;
		break;
	  }
	}
	?>
	<select name="item_select" class="form-control selectpicker" data-live-search="true" id="consultation_fee_id_1">
	  <option value=""></option>
	  <?php foreach ($items as $group_id => $_items) {
		$group_name = $_items[0]['group_name'] ?? '';
		if ($has_consultation_fee && $group_name != "Consultation Fee") {
		  continue;
		} ?>
		<optgroup data-group-id="<?= e($group_id); ?>" label="<?= $group_name; ?>">
		 <?php foreach ($_items as $item) { 
		  if($staff_data->role_name == "Service Doctor"){
			if($item['rate'] != "0.00"){
				continue;
			}  
		  }
		  ?>
			<option value="<?= e($item['rate']); ?>"
					data-rate="<?= e($item['rate']); ?>"
					data-subtext="<?= strip_tags(mb_substr($item['long_description'], 0, 200)); ?>">
						<?= e(app_format_number($item['rate'])); ?>
			</option>
		  <?php } ?>
		</optgroup>
	  <?php } ?>
	</select>
  </div>
</div>

 <div class="appointment_payment_section">
  <!-- Payment Amount -->
  <div class="col-md-3">
    <div class="form-group">
	
	   <span id="payment_amount_required_indicator" style="color:red; display: none;">*</span>
      <label><?= _l('payment_amount'); ?></label>
      <input type="number" class="form-control" id="paying_amount_1" name="payment_amount" min="0" step="0.01"
             placeholder="<?= _l('enter_payment_amount'); ?>">
      <small class="text-danger" id="amountError" style="display: none;">
        <?= _l('amount_exceeds_item'); ?>
      </small>
    </div>
  </div>

  <!-- Attachment -->
  <div class="col-md-3">
    <div class="form-group">
      <label><?= _l('attachment_optional'); ?></label>
      <input type="file" class="form-control" id="paymentAttachment" name="attachment"
             accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
    </div>
  </div>

  <!-- Payment Mode -->
  <div class="col-md-3">
    <div class="form-group">
	 <span id="payment_mode_required_indicator" style="color:red; display: none;">*</span>
      <label><?= _l('payment_mode'); ?></label>
      <select class="selectpicker form-control" name="paymentmode" data-width="100%"
              data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
        <option value=""></option>
        <?php foreach ($payment_modes as $mode) { ?>
          <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
</div>

  
</div>

</div>
					<div class="col-md-6">
						<label for="comments"><?php echo _l('comments'); ?>
</label>
						<textarea name="comments" class="form-control" rows="2" placeholder="<?= _l('enter_comments'); ?>"></textarea>
					</div>

                  <div class="col-md-3">
                  <label>&nbsp;<br></label>
                    <button type="submit" class="btn btn-success" style="margin-top: 25px;"><?= _l('submit'); ?></button>
                  </div>
                  </div>

                  <br>

                  
                </form>
              </div>
<script>
$(document).ready(function () {
    function toggleFollowupRequired() {
        var selectedText = $('#patient_response_id_1 option:selected').text().toLowerCase().trim();
        if (selectedText === 'call back') {
            $('#next_calling_date').attr('required', true);
            $('#next_calling_required_indicator').show();
        } else {
            $('#next_calling_date').removeAttr('required');
            $('#next_calling_required_indicator').hide();
        }
    }

    $('#patient_response_id_1').on('change', toggleFollowupRequired);

    // Run once on load in case form is prefilled
    toggleFollowupRequired();
});
</script>
<script>
$(document).ready(function() {
    // When consultation fee is selected
    $('#consultation_fee_id_1').change(function() {
        const selectedOption = $(this).find('option:selected');
        const feeValue = parseFloat(selectedOption.data('rate')) || 0;

        $('#paying_amount_1').val('');
        $('#amountError').hide();
    });

    // When payment amount is typed
    $('#paying_amount_1').on('input', function() {
        const selectedRate = parseFloat($('#consultation_fee_id_1 option:selected').data('rate')) || 0;
        const enteredAmount = parseFloat($(this).val()) || 0;

        if (enteredAmount > selectedRate) {
            alert_float('danger', '<?php echo _l('paying_amount_cannot_exceed_due_amount'); ?>');
            $(this).val('');
            $(this).addClass('is-invalid');
        } else {
            $('#amountError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>

<script>
$(function () {
    function toggleFieldsByResponse() {
        const response = $('#patient_response_id_1 option:selected').text().toLowerCase().trim();
		//alert(response);
        // Common mandatory fields
        const doctor = $('#doctor_id_1');
        const appointmentDate = $('#appointment_date_1');
        const treatment = $('#treatment_id_1');
        const consultation = $('#consultation_fee_id_1');

        const paymentAmount = $('#paying_amount_1');
        const paymentMode = $('select[name="paymentmode"]');
        const paymentSection = $('.appointment_payment_section');

        // Reset required
        doctor.removeAttr('required');
        appointmentDate.removeAttr('required');
        treatment.removeAttr('required');
        consultation.removeAttr('required');
        paymentAmount.removeAttr('required');
        paymentMode.removeAttr('required');

        // Hide payment section by default
        paymentSection.hide();

        if (response === 'on appointment' || response === 'paid appointment') {
            $('.lead_with_doctor_section').removeClass('hide').slideDown();

            // Common fields
            doctor.attr('required', true);
            appointmentDate.attr('required', true);
            treatment.attr('required', true);
            consultation.attr('required', true);
        } else {
            $('.lead_with_doctor_section').slideUp().addClass('hide');
        }

        if (response === 'paid appointment') {
            paymentSection.show();
            paymentAmount.attr('required', true);
            paymentMode.attr('required', true);
			$('#payment_amount_required_indicator').show();
			$('#payment_mode_required_indicator').show();
        }
    }

    toggleFieldsByResponse();

    $('#patient_response_id_1').on('change', toggleFieldsByResponse);
});
</script>

                <br>
				<?= render_datatable([
    _l('s_no'),
    _l('called_by'),
    _l('call_type'),
    _l('next_calling_date'),
    _l('better_patient'),
    _l('pharmacy_medicine_days'),
    _l('patient_took_medicine_days'),
    _l('created_date'),
    _l('comments'),
], 'call-logs-table'); ?>
                <!-- Table -->
                

              </div>
            </div>
<?PHP
}if (staff_can('view_message_log', 'customers')) {
	?>
          <div role="tabpanel" class="tab-pane" id="message_log">
              
			  
			  <?php if (staff_can('view_call_log', 'customers')) : ?>
			<?= render_datatable([
				_l('s_no'),
				_l('status'),
				_l('message_type'),
				_l('message'),
				_l('response'),
				_l('datetime'),
			], 'message-log-table'); ?>

		<?php endif; ?>
			  
	<script>
function toggleCallLogForm() {
    const form = document.getElementById('call-log-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

/* $(function() {
    initDataTable('.table-message-log-table', admin_url + 'client/message_log_table/' + <?= $client->userid ?>, undefined, undefined, '', [0, 'desc']);
}); */

</script>

<script>
$(function () {
    let messageLogInitialized = false;

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).attr("href");

        if (target === '#message_log' && !messageLogInitialized) {
            initDataTable(
                '.table-message-log-table',
                admin_url + 'client/message_log_table/' + <?= $client->userid ?>,
                undefined, undefined, '', [0, 'desc']
            );
            messageLogInitialized = true;
        }
    });
});
</script>


<script>
$(document).ready(function() {
    // List of trigger responses passed from PHP
    const triggerResponses = <?= json_encode($enabled_responses) ?>;
    function checkCallReceivedTrigger() {
        const selectedText = $('.patient_response_id option:selected').text().trim();
        if (triggerResponses.includes(selectedText)) {
            $('#call_received').show();
        } else {
            $('#call_received').hide();
        }
    }

    // Check on select change
    $('.patient_response_id').on('change', checkCallReceivedTrigger);

    // Also check on page load in case it's pre-selected
    checkCallReceivedTrigger();
});
</script>

<script>
$(document).ready(function () {
    $('#itemSelect').on('changed.bs.select change', function () {
        const selectedRate = parseFloat($(this).find('option:selected').data('rate')) || 0;
        $('#paymentAmount').attr('max', selectedRate);
        $('#paymentAmount').trigger('input'); // recheck if value already exists
    });

    $('#paymentAmount').on('input', function () {
        const rate = parseFloat($('#itemSelect').find('option:selected').data('rate')) || 0;
        const val = parseFloat($(this).val()) || 0;

        if (val > rate) {
            $('#amountError').show();
            $(this).addClass('is-invalid');
        } else {
            $('#amountError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>		  
            </div>
<?PHP
}
?>
<div role="tabpanel" class="tab-pane" id="patient_reminders">
	
	<?php render_datatable([_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')], 'patient-remainders-table');
	?>
</div>

<script>
$(function () {
    let patientRemindersInitialized = false;

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).attr("href");

        if (target === '#patient_reminders' && !patientRemindersInitialized) {
            initDataTable(
                '.table-patient-remainders-table',
                admin_url + 'client/patient_reminders_table/' + <?= $client->userid ?>,
                undefined, undefined, '', [0, 'desc']
            );
            patientRemindersInitialized = true;
        }

        // Optional: initialize the lead reminders only once too
        if (target === '#patient_reminders' && typeof initRemindersOnce === 'function') {
            initRemindersOnce();
        }
    });

    // For optional use if you want to move inline onclick to JS
    window.initRemindersOnce = (function () {
        let initialized = false;
        return function () {
            if (!initialized) {
                initDataTable(
                    '.table-reminders-leads',
                    admin_url + 'misc/get_reminders/' + <?= e($lead->id); ?> + '/lead',
                    undefined, undefined, undefined, [1, 'asc']
                );
                initialized = true;
            }
        };
    })();
});
</script>

<?php
if (staff_can('view_activity_log', 'customers')) {
?>

            <div role="tabpanel" class="tab-pane" id="tab_activity">
            <div class="activity-feed">
              <div class="patient-section-title mt-4"><?= _l('patient_activity_logs'); ?></div><br>
              <?php 
              if(staff_can('view_activity_log', 'customers')){
              foreach ($patient_activity_log as $log) { ?>
                <div class="feed-item">
                  <div class="date">
                    <span class="text-has-action" data-toggle="tooltip" data-title="<?= _dt($log['date']); ?>">
                      <?= time_ago($log['date']); ?>
                    </span>
                  </div>
                  <div class="text">
                    <?php if ($log['staffid'] != 0) { ?>
                      <a href="<?= admin_url('profile/' . $log['staffid']); ?>">
                        <?= staff_profile_image($log['staffid'], ['staff-profile-xs-image pull-left mright5']); ?>
                      </a>
                    <?php } ?>
                    <?= e($log['full_name']) . ' - '; ?>
                    <?= ($log['custom_activity'] == 0) ? _l($log['description']) : process_text_content_for_display($log['description']); ?>
					
					<?php 
					if (!empty($log['additional_data'])) {
					$changes = json_decode($log['additional_data'], true);

					if (is_array($changes)) {
						echo "<ul style='margin: 0; padding-left: 18px;'>";
						foreach ($changes as $field => $change) {
							$field_label = ucwords(str_replace('_', ' ', $field));
							$old = isset($change['old']) ? $change['old'] : '';
							$new = isset($change['new']) ? $change['new'] : '';

							echo "<li><strong>$field_label</strong> changed from <em>\"$old\"</em> to <em>\"$new\"</em></li>";
						}
						echo "</ul>";
					} else {
						// fallback to raw output if not JSON
						echo "<br>" . $log['additional_data'];
					}
				}

					?>
                  </div>
                </div>
              <?php }
              }
              ?>
            </div>

            <div class="col-md-12">
              <input type="hidden" name="patientid" value="<?php echo $client->userid; ?>">
              <?= render_textarea('patient_activity_textarea', '', '', ['placeholder' => _l('enter_activity')], [], 'mtop15'); ?>
              <div class="text-right mtop10">
                <button id="patient_enter_activity" class="btn btn-primary"><?= _l('submit'); ?></button>
              </div>
            </div>
          </div>
<?PHP
}
?>

        </div>
        
<div class="modal fade child-modal" id="caseSheetModal" tabindex="-1" role="dialog" aria-labelledby="caseSheetModalLabel" style="margin-top: 35px;" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #f2f9ff">
        <h5 class="modal-title"><?php echo _l('case_sheet_details'); ?></h5>
        
      </div>
      <div class="modal-body" id="caseSheetModalBody"></div>

    </div>
  </div>
</div>



<!-- DataTables core -->
<!--<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>-->



<script>

$(document).ready(function () {
  const pathParts = window.location.pathname.split('/');
  const lastPart = pathParts[pathParts.length - 1];

  if (lastPart.startsWith('tab_')) {
    const tabId = lastPart;

    // Activate nav tab link
    $('.nav-tabs a[href="#' + tabId + '"]').tab('show');

    // Activate tab content pane
    $('#' + tabId).addClass('active').siblings('.tab-pane').removeClass('active');

    // Optionally, scroll to the tab
    setTimeout(() => {
      const $tab = $('#' + tabId);
      if ($tab.length) {
        $('html, body').animate({
          scrollTop: $tab.offset().top - 100
        }, 300);
      }
    }, 200);
  }
});

  function toggleCallLogForm() {
    const form = document.getElementById('call-log-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }

  function toggleFeedbackForm() {
    const form = document.getElementById('feedback-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }

 
  function toggleEstimationForm() {
    const form = document.getElementById('estimation-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
	
  }

 
  // Add patient activity via AJAX
  $("body").on("click", "#patient_enter_activity", function () {
    var message = $("#patient_activity_textarea").val();
    var patientId = $('input[name="patientid"]').val();

    if (message === "") return;

    $.post(admin_url + "client/add_patient_activity", {
        patientid: patientId,
        activity: message,
    })
    .done(function (response) {
        response = JSON.parse(response);
        if (response.success) {
            // ✅ Redirect after success
            window.location.href = response.redirect;
        } else {
            alert_float("danger", response.message);
        }
    })
    .fail(function (data) {
        alert_float("danger", data.responseText);
    });
});


// Add patient call log via AJAX
$("body").on("submit", "#callLogEntryForm", function (e) {
  e.preventDefault(); // Prevent default form submission

  var $form = $(this);
  var $btn = $form.find('button[type="submit"]'); // find submit button
  $btn.prop("disabled", true).text("Please wait..."); // disable + update label

  var formData = $form.serialize(); // Serialize all fields

  $.post(admin_url + "client/add_patient_call_log", formData)
    .done(function (response) {
      response = JSON.parse(response);

      if (response.success) {
        var admin_url = "<?= admin_url(); ?>";
        var selectedClientId = <?= $client->userid; ?>;
        var callback_url = "<?= isset($callback_url) ? $callback_url : ''; ?>";

        if (selectedClientId) {
          if (callback_url) {
            window.location.href =
              admin_url + "client/" + callback_url + "/" + selectedClientId + "/tab_calls";
          } else {
            window.location.href =
              admin_url + "client/get_patient_list/" + selectedClientId + "/tab_calls";
          }
        }
      } else {
        alert_float("danger", response.message || "Failed to save call log.");
        $btn.prop("disabled", false).text("<?= _l('submit'); ?>"); // re-enable on failure
      }
    })
    .fail(function (xhr) {
      alert_float("danger", xhr.responseText || "Error occurred while submitting.");
      $btn.prop("disabled", false).text("<?= _l('submit'); ?>"); // re-enable on error
    });
});



// Add patient call log via AJAX
$("body").on("submit", "#requestcallLogEntryForm", function (e) {
  e.preventDefault(); // Prevent the default form submission

  var $form = $(this);
  var formData = $form.serialize(); // Serialize all form fields including hidden CSRF and patientid

  $.post(admin_url + "client/add_patient_call_log", formData)
    .done(function (response) {
      response = JSON.parse(response);

      if (response.success) {
		  var admin_url = "<?= admin_url(); ?>";
		  var selectedClientId = <?= $client->userid; ?>;
			var callback_url = "<?= isset($callback_url) ? $callback_url : ''; ?>";
			if (selectedClientId) {
				if (callback_url) {
					window.location.href = admin_url + "client/" + callback_url + "/" + selectedClientId + "/tab_calls";
				} else {
					window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_calls";
				}
			}
        //window.location.href = response.redirect;
      } else {
        alert_float("danger", response.message || "Failed to save call log.");
      }
    })
    .fail(function (xhr) {
      alert_float("danger", xhr.responseText || "Error occurred while submitting.");
    });
});


// Add Estimation via AJAX
$("body").on("submit", "#estimationForm", function (e) {
  e.preventDefault();

  var $form = $(this);
  var $btn = $form.find('button[type="submit"]'); // get submit button
  $btn.prop("disabled", true).text("Please wait..."); // disable + change text

  var formData = $form.serializeArray(); 
  var selectedClientId = $('#clientid').val();
  var selectedTreatmentId = $('#treatmentSelect').val();
  var expiryDate = $('input[name="expirydate"]').val();

  // ✅ Remove treatment_id from being sent
  formData = formData.filter(field => field.name !== 'treatment_id');

  $.ajax({
    url: admin_url + "client/add_estimation",
    method: "POST",
    data: $.param(formData),
    dataType: "json",
    success: function (response) {
      if (response.success) {
        var estimateId = response.id;
        alert_float("success", "Package added successfully.");

        // ✅ Update treatment AFTER estimation is created
        if (selectedTreatmentId) {
          $.post(admin_url + "client/update_treatment_after_estimation", {
            treatment_id: selectedTreatmentId,
            estimation_id: estimateId,
            expirydate: expiryDate
          });
        }

        // ✅ Log journey
        $.post(admin_url + "client/log_patient_journey", {
          userid: selectedClientId,
          status: 'package_created',
          remarks: 'Package Created. ID: ' + estimateId
        });

        setTimeout(function () {
          window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_estimation";
        }, 1000);
      } else {
        alert_float("danger", response.message || "Error occurred.");
        $btn.prop("disabled", false).text("<?php echo _l('save_and_pay'); ?>"); // re-enable
      }
    },
    error: function (xhr, status, error) {
      alert_float("danger", "Failed to add package.");
      console.error("AJAX error:", xhr.responseText || error);
      $btn.prop("disabled", false).text("<?php echo _l('save_and_pay'); ?>"); // re-enable
    }
  });
});







function togglePrescriptionForm() {
    const form = document.getElementById('prescription-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }

  $("body").on("submit", "#prescriptionForm", function (e) {
  e.preventDefault();

  var form = $(this);
  var formData = form.serializeArray();
  console.log("Serialized array:", formData);

  // Optional: Convert to object for easier manipulation (if needed)
  let payload = {};
  formData.forEach(field => {
    if (payload[field.name]) {
      // If already exists, convert to array or push to it
      if (!Array.isArray(payload[field.name])) {
        payload[field.name] = [payload[field.name]];
      }
      payload[field.name].push(field.value);
    } else {
      payload[field.name] = field.value;
    }
  });

  console.log("Payload object:", payload);

  // Submit to the controller
  $.post(admin_url + "client/save_prescription", form.serialize())
    .done(function (response) {
      let res = JSON.parse(response);
      if (res.success) {
       // alert(res.message);
        if (res.redirect) {
          window.location.href = res.redirect;
        }
      } else {
        alert("Error: " + res.message);
      }
    })
    .fail(function () {
      alert("Something went wrong while saving the prescription.");
    });
});

/* tinymce.init({
  selector: 'textarea.tinymce',
  setup: function (editor) {
    let debounceTimer;

    const debounceSave = function () {
      const $form = $('#casesheetForm');
      const recordId = $('#record_id').val();

      if (!recordId) {
        $form.submit(); // First time insert
      } else {
        updateCaseSheet(recordId); // Subsequent updates
      }
    };

    editor.on('keyup change', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(debounceSave, 500); // Debounce: wait 500ms after last keyup
    });
  }
});


let inputDebounceTimer;

$(document).on('input change click keydown', '#casesheetForm :input', function () {
  clearTimeout(inputDebounceTimer);
  inputDebounceTimer = setTimeout(() => {
    const recordId = $('#record_id').val();
    const $form = $('#casesheetForm');

    if (!recordId) {
      $form.submit(); // First time insert
    } else {
      updateCaseSheet(recordId); // Update
    }
  }, 5000); // 500ms debounce
}); */




let isSaving = false; // global flag

function handleSave($btn, isCallNext = false) {
  if (isSaving) return; // already saving → block
  isSaving = true;

  $btn.prop('disabled', true).text('<?= _l("please_wait") ?>...');

  const $form = $('#casesheetForm');
  const recordId = $('#record_id').val();
  const selectedClientId = <?= $client->userid; ?>;
  const redirectUrl = "<?= admin_url('client/save_and_call_next_patient/' . $client->userid . '/' . get_staff_user_id()); ?>";

  if (consultationStartTime) {
    const now = new Date();
    const durationSeconds = Math.floor((now - consultationStartTime) / 1000);
    $('#consultation_duration').val(durationSeconds);
    console.log("Duration captured:", durationSeconds, "seconds");
  }

  tinyMCE.triggerSave();
  $(window).off('beforeunload');

  const handleError = (msg) => {
    alert(msg || "Error while saving. Please try again.");
    $btn.prop('disabled', false).text(isCallNext ? '<?= _l("save_and_call_next_patient") ?>' : '<?= _l("save") ?>');
    isSaving = false; // allow retry
  };

  const handleSuccess = () => {
    if (isCallNext) {
      if (confirm("The case sheet has been saved. Do you want to proceed to the next patient?")) {
        window.location.href = redirectUrl;
      }
    } else {
      alert_float("success", 'Casesheet Saved');
      window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_casesheet";
    }
  };

  // Update or Insert
  if (recordId) {
    $.ajax({
      url: '<?= admin_url('client/update_casesheet') ?>/' + recordId,
      type: 'POST',
      data: $form.serialize(),
      success: function () {
        $('.table-casesheet').DataTable().ajax.reload(null, false);
        handleSuccess();
      },
      error: function () {
        handleError();
      }
    });
  } else {
    $.post("<?= admin_url('client/save_casesheet') ?>", $form.serialize())
      .done(function (response) {
        let res = JSON.parse(response);
        if (res.success) {
          $('#record_id').val(res.id);
          handleSuccess();
        } else {
          handleError("Save failed: " + res.message);
        }
      })
      .fail(function () {
        handleError("Something went wrong while saving.");
      });
  }
}

$('#saveCasesheetBtn').on('click', function () {
  handleSave($(this), false);
});

$('#saveCallNextBtn').on('click', function () {
  handleSave($(this), true);
});



// Optional: Custom function to handle update (AJAX version)
function updateCaseSheet(id) {
	tinyMCE.triggerSave(); // Sync editor to textarea
let formData = $('#casesheetForm').serialize();
  //const formData = $('#casesheetForm').serialize();
  $.ajax({
    url: '<?= admin_url('client/update_casesheet') ?>/' + id, // your controller path
    type: 'POST',
    data: formData,
    success: function (res) {
		$('.table-casesheet').DataTable().ajax.reload(null, false);
      console.log('Case sheet updated');
    }
  });
}



$("body").on("submit", "#casesheetForm", function (e) {
  e.preventDefault();

  var form = $(this);
  var formData = form.serializeArray();
  console.log("Serialized array:", formData);

  // Optional: Convert to object for easier manipulation (if needed)
  let payload = {};
  formData.forEach(field => {
    if (payload[field.name]) {
      // If already exists, convert to array or push to it
      if (!Array.isArray(payload[field.name])) {
        payload[field.name] = [payload[field.name]];
      }
      payload[field.name].push(field.value);
    } else {
      payload[field.name] = field.value;
    }
  });

  console.log("Payload object:", payload);

  // Submit to the controller
  $.post(admin_url + "client/save_casesheet", form.serialize())
    .done(function (response) {
      let res = JSON.parse(response);
      if (res.success) {
		  //alert(res.id);
		  $('#record_id').val(res.id); // sets value in the hidden input

        
      } else {
        alert("Error: " + res.message);
      }
    })
    .fail(function () {
      alert("Something went wrong while saving the prescription.");
    });
});

function _patient_init_data(data, id) {
  var hash = window.location.hash;

  var $modal = $("#client-modal"); // Assuming your patient modal ID is #client-modal

  $modal.find(".data").html(data.patientView.data);
  $modal.modal({
    show: true,
    backdrop: "static",
  });

  init_tags_inputs();
  init_selectpicker();
  init_datepicker();
  init_color_pickers();
  custom_fields_hyperlink();
  validate_client_form(); // Use correct validator if it's not leads

  var hashes = [
    "#tab_client_profile",
    "#tab_contacts",
    "#tab_activity",
    "#tab_calls",
    "#attachments",
  ];

  if (hashes.indexOf(hash) > -1) {
    window.location.hash = hash;
  }

  // Example: refresh call log datatable if present
  if ($.fn.DataTable && $.fn.DataTable.isDataTable("#my-custom-table")) {
    $("#my-custom-table").DataTable().ajax.reload();
  }

  // Set latest activity
  var latest_activity = $modal
    .find("#tab_activity .feed-item:last-child .text")
    .html();
  if (typeof latest_activity != "undefined") {
    $modal.find("#patient-latest-activity").html(latest_activity);
  } else {
    $modal
      .find(".patient-latest-activity > .info-heading")
      .addClass("hide");
  }
}

</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById('prescription-form');
  
  // Fetch medicine and related data from PHP
  const medicineObjects = <?php echo json_encode($medicines); ?>;
  const potencyObjects = <?php echo json_encode($potencies); ?>;
  const doseObjects = <?php echo json_encode($doses); ?>;
  const timingObjects = <?php echo json_encode($timings); ?>;

  // Extract only active medicine names
  const medicineOptions = medicineObjects
    .filter(m => m.medicine_status === "1")
    .map(m => m.medicine_name);

  const potencyOptions = potencyObjects.map(p => p.medicine_potency_name);
  const doseOptions = doseObjects.map(d => d.medicine_dose_name);
  const timingOptions = timingObjects.map(t => t.medicine_timing_name);

  // Add one row on initial load
  addMedicineRow();

  // Global exposure for addMedicineRow so onclick can access it
  window.addMedicineRow = addMedicineRow;

  function addMedicineRow() {
    const tbody = document.getElementById("medicineBody");
    const tr = document.createElement("tr");

    // Create Medicine dropdown
    const tdMedicine = document.createElement("td");
    tdMedicine.appendChild(createSearchableSelect(medicineOptions, "medicine-name"));

    // Create Potency dropdown
    const tdPotency = document.createElement("td");
    tdPotency.appendChild(createSearchableSelect(potencyOptions, "medicine-potency"));

    // Create Dose dropdown
    const tdDose = document.createElement("td");
    tdDose.appendChild(createSearchableSelect(doseOptions, "medicine-dose"));

    // Create Timing dropdown
    const tdTiming = document.createElement("td");
	const timingSelect = createSearchableSelect(timingOptions, "medicine-timing");

	timingSelect.addEventListener('change', function(event) {
		//alert();
	  addMedicineRow();
	});

	tdTiming.appendChild(timingSelect);
		

    // Create Remarks textarea
    const tdRemarks = document.createElement("td");
    const remarks = document.createElement("textarea");
    remarks.className = "medicine-textarea";
    remarks.rows = 2;
    remarks.setAttribute('name', 'medicine_remarks[]');  // Add remarks name
    tdRemarks.appendChild(remarks);

    // Create Remove Button
	// Create Action Buttons Cell
	const tdAction = document.createElement("td");
	const btnGroup = document.createElement("div");
	btnGroup.className = "btn-group";

	// Remove (-) button
	const removeBtn = document.createElement("button");
	removeBtn.type = "button";
	removeBtn.className = "btn btn-danger btn-sm remove-row";
	removeBtn.innerHTML = '<i class="fa fa-minus"></i>';
	removeBtn.onclick = () => tr.remove();

	// Add (+) button that calls `addMedicineRow()`
	const addBtn = document.createElement("button");
	addBtn.type = "button";
	addBtn.className = "btn btn-success btn-sm add-row";
	addBtn.innerHTML = '<i class="fa fa-plus"></i>';
	addBtn.onclick = () => addMedicineRow();

	// Append buttons to button group
	btnGroup.appendChild(removeBtn);
	btnGroup.appendChild(addBtn);

	// Append button group to action cell
	tdAction.appendChild(btnGroup);

	// Append all td elements to the row
	tr.appendChild(tdMedicine);
	tr.appendChild(tdPotency);
	tr.appendChild(tdDose);
	tr.appendChild(tdTiming);
	tr.appendChild(tdRemarks);
	tr.appendChild(tdAction);



    // Append tr to tbody
    tbody.appendChild(tr);
  }
  
  


  // Toggle visibility of the prescription form
  window.togglePrescriptionForm = function () {
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  };

  // Reusable searchable select
  function createSearchableSelect(options, className) {
    const container = document.createElement('div');
    container.className = `medicine-select-container`;

    const input = document.createElement('input');
    input.className = `medicine-select-input ${className}`;
    input.setAttribute("placeholder", "Search...");
    input.setAttribute("autocomplete", "off");

    // Set proper name based on className
    if (className === 'medicine-name') {
      input.setAttribute('name', 'medicine_name[]');
    } else if (className === 'medicine-potency') {
      input.setAttribute('name', 'medicine_potency[]');
    } else if (className === 'medicine-dose') {
      input.setAttribute('name', 'medicine_dose[]');
    } else if (className === 'medicine-timing') {
      input.setAttribute('name', 'medicine_timing[]');
    }

    const optionsList = document.createElement('div');
    optionsList.className = 'medicine-select-options';

    input.addEventListener('input', () => {
      filterOptions(input.value, options, optionsList);
    });

    options.forEach(opt => {
      const option = document.createElement('div');
      option.className = 'medicine-select-option';
      option.textContent = opt;
      option.onclick = () => {
        input.value = opt;
        optionsList.style.display = 'none';
      };
      optionsList.appendChild(option);
    });

    container.appendChild(input);
    container.appendChild(optionsList);
    return container;
  }

  function filterOptions(query, options, optionsList) {
    optionsList.innerHTML = '';
    const filtered = options.filter(opt => opt.toLowerCase().includes(query.toLowerCase()));

    optionsList.style.display = filtered.length > 0 ? 'block' : 'none';

    filtered.forEach(opt => {
      const option = document.createElement('div');
      option.className = 'medicine-select-option';
      option.textContent = opt;
      option.onclick = () => {
        optionsList.previousElementSibling.value = opt;
        optionsList.style.display = 'none';
      };
      optionsList.appendChild(option);
    });
  }
});


</script>


<script>
$(document).on('click', '.view-case', function () {
  var caseId = $(this).data('id');

  $.ajax({
    url: '<?= site_url('admin/client/casesheet_view'); ?>/' + caseId,
    type: 'GET',
    success: function (response) {
      // Add blur to the first modal content
      $('.modal.show .modal-content').addClass('blurred');

      // Show second modal
      $('#caseSheetModalBody').html(response);
      $('#caseSheetModal').modal('show');
    },
    error: function () {
      alert('Failed to load case sheet details.');
    }
  });
});

// Remove blur when second modal closes
$('#caseSheetModal').on('hidden.bs.modal', function () {
  $('.modal .modal-content').removeClass('blurred');
});

$(document).on('show.bs.modal', '.modal', function () {
  var zIndex = 1040 + 10 * $('.modal:visible').length;
  $(this).css('z-index', zIndex);
  setTimeout(function () {
    $('.modal-backdrop').not('.modal-stack')
      .css('z-index', zIndex - 1)
      .addClass('modal-stack');
  }, 0);
});

$(document).on('hidden.bs.modal', '.modal', function () {
  if ($('.modal:visible').length) {
    $('body').addClass('modal-open');
  }
});

  // Pass PHP variable to JS (make sure this outputs a number, fallback 0)
  const medicine_followup_days = <?php echo isset($medicine_followup_days) ? (int)$medicine_followup_days : 0; ?>;

  document.getElementById('medicine_days').addEventListener('input', function () {
    let days = parseInt(this.value);
    if (!isNaN(days)) {
      let today = new Date();
      // Add medicine_days and medicine_followup_days (which can be negative)
      let totalDays = days + medicine_followup_days;
      today.setDate(today.getDate() + totalDays);
      let followUp = today.toISOString().split('T')[0]; // Format YYYY-MM-DD
      document.getElementById('followup_date').value = followUp;
    } else {
      document.getElementById('followup_date').value = '';
    }
  });



$(document).ready(function() {
    // Initialize all collapsible sections as collapsed by default
    $('.collapse').collapse('hide');
    
    // Toggle the icon when a section is opened/closed
    $('#casesheetAccordion .collapse').on('show.bs.collapse', function () {
        var icon = $(this).prev().find('.toggle-icon');
        icon.addClass('open');
    }).on('hide.bs.collapse', function () {
        var icon = $(this).prev().find('.toggle-icon');
        icon.removeClass('open');
    });
});


</script>

<script>
    document.querySelectorAll('.improvement-input').forEach(function(input) {
        input.addEventListener('input', function () {
            if (this.value > 100) this.value = 100;
            if (this.value < 0) this.value = 0;
        });
    });
	
	function convertToInvoice(estimateId) {
  if (!confirm('Are you sure you want to accept and convert this estimate?')) {
    return;
  }

  $.ajax({
    url: admin_url + 'estimates/convert_to_invoice/' + estimateId,
    type: 'GET',
    success: function(response) {
	  // Show floating success message
	  alert_float("success", 'Estimate converted to invoice successfully.');
	var selectedClientId = <?= $client->userid; ?>;
	$.post(admin_url + "client/log_patient_journey", {
        userid: selectedClientId,
        status: 'package_accepted',
        remarks: 'Estimation ID:' + estimateId
      });
	  // Wait for 1 second (1000ms), then reload the page
	  setTimeout(function() {
		location.reload();
	  }, 1000);
	},
    error: function(xhr) {
      alert('Failed to convert estimate.');
    }
  });
}
	function convertToInvoice1(estimateId) {
  if (!confirm('Are you sure you want to accept and convert this estimate?')) {
    return;
  }

  $.ajax({
    url: admin_url + 'estimates/convert_to_invoice/' + estimateId,
    type: 'GET',
    success: function(response) {
	  // Show floating success message
	  alert_float("success", 'Estimate converted to invoice successfully.');
	var selectedClientId = <?= $client->userid; ?>;
	$.post(admin_url + "client/log_patient_journey", {
        userid: selectedClientId,
        status: 'package_accepted',
        remarks: 'Estimation ID:' + estimateId
      });
	  // Wait for 1 second (1000ms), then reload the page
	  setTimeout(function() {
		location.reload();
	  }, 1000);
	},
    error: function(xhr) {
      alert('Failed to convert estimate.');
    }
  });
}


// Back button click handler
$("body").on("click", "#backToPaymentsBtn", function () {
  $('#paymentFormContainer').hide();
  $('#payment_table').show();
});



$('body').on('submit', '#record_payment_form', function(e) {
  e.preventDefault();

  var $form = $(this);
  var $submitBtn = $form.find('button[type="submit"]');
  var formData = $form.serialize();
  var selectedClientId = <?= $client->userid; ?>;
  var invoiceId = $('#record_payment_form').find('input[name="invoiceid"]').val();

  // Add loading spinner and disable the button
  var originalBtnHtml = $submitBtn.html();
  $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

  $.post($form.attr('action'), formData)
    .done(function(response) {
      alert_float('success', 'Payment recorded successfully.');
	  
	$.post(admin_url + "client/log_patient_journey", {
        userid: selectedClientId,
        status: 'payment_done',
        remarks: 'Invoice ID:' + invoiceId
      });
	  
	  
	  $.post(admin_url + "client/trigger_communication_event/" + selectedClientId + "/payment_done", formData, function(response) {
			console.log('Server Response:', response);
		});

	  
	$.post(admin_url + "client/register_patient", {
        userid: selectedClientId,
        invoiceId: invoiceId,
      });
	  
       setTimeout(function() {
        $('#paymentFormContainer').hide();
		$('#payment_table').show();
		 //window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_payments"; 
		 var admin_url = "<?= admin_url(); ?>";
		var callback_url = "<?= isset($callback_url) ? $callback_url : ''; ?>";
		if (selectedClientId) {
			if (callback_url) {
				window.location.href = admin_url + "client/" + callback_url + "/" + selectedClientId + "/tab_payments";
			} else {
				window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_payments";
			}
		}
      }, 1000);
    })
    .fail(function() {
      alert_float('danger', 'Error occurred while submitting.');
    })
    .always(function() {
      // Restore the original button state
      $submitBtn.prop('disabled', false).html(originalBtnHtml);
    });
});


$(document).ready(function () {
  $('#feedbackEntryForm').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var submitBtn = $('#feedbackButton');
	var selectedClientId = <?= $client->userid; ?>;

    // Disable button and update text
    submitBtn.prop('disabled', true).text('Sending...');

    $.ajax({
      type: 'POST',
      url: '<?= admin_url('client/share_testimonial'); ?>',
      data: form.serialize(),
      dataType: 'json',
      success: function (response) {
        // Re-enable button
        submitBtn.prop('disabled', false).text('Submit');

        if (response.success) {
          alert_float('success', 'Feedback sent successfully.');
          $('#feedback-form').hide();
          form[0].reset();
		  setTimeout(function () {
          window.location.href = admin_url + "client/get_patient_list/" + selectedClientId + "/tab_feedback";
        }, 1000);
        } else {
          alert_float('danger', 'Failed to send. Try again.');
        }
      },
      error: function () {
        submitBtn.prop('disabled', false).text('Submit');
        alert_float('danger', 'Server error occurred.');
      }
    });
  });
});


</script>

<script>
$(document).on('click', '.view-feedback', function () {
    const $icon = $(this);
    const id = $icon.data('title');
    const $row = $icon.closest('tr');
	
	// JS unserialize function (very basic)
function jsUnserialize(str) {
    try {
        // only handles arrays like: a:1:{i:0;s:36:"filename.jpg";}
        let match = str.match(/s:\d+:"([^"]+)"/g);
        if (!match) return [];

        return match.map(m => m.match(/"([^"]+)"/)[1]);
    } catch (e) {
        return [];
    }
}

// Convert base64 + unserialize like PHP
function flextestimonial_perfect_unserialize_js(encoded) {
    try {
        const decoded = atob(encoded);
        return jsUnserialize(decoded);
    } catch (e) {
        return jsUnserialize(encoded);
    }
}

// Build full media URL
function flextestimonial_media_url(url) {
    if (url.startsWith('http')) return url;
    return `${site_url}uploads/flextestimonial/${url}`;
}
function renderStars(rating) {
    rating = parseInt(rating) || 0;

    // Determine star color based on rating
    let starColor = '';
    if (rating <= 2) {
        starColor = 'red';
    } else if (rating === 3) {
        starColor = 'orange';
    } else if (rating >= 4) {
        starColor = 'green';
    }

    let stars = '';
    for (let i = 1; i <= 5; i++) {
        const isActive = i <= rating ? 'active' : '';
        const iconType = i <= rating ? 'fas' : 'far'; // filled or outline star
        const iconColor = i <= rating ? starColor : '#ccc'; // filled = dynamic, empty = gray

        stars += `
            <a href="#" class="flex-testimonial-rating-star ${isActive}" data-rating="${i}">
                <i class="${iconType} fa-star" style="color: ${iconColor};"></i>
                <input type="radio" name="text_rating" value="${i}" class="flex-testimonial-rating-star-input" style="display: none;">
            </a>
        `;
    }

    return `<div class="flex-testimonial-rating">${stars}</div>`;
}




    // Remove existing .response-row if already present
    $('.response-row').remove();

    // Insert new row after clicked row
    const $newRow = $(`
        <tr class="response-row">
            <td colspan="${$row.find('td').length}">
                <div class="response-content">
                    <div class="text-center text-muted p-2">Loading responses...</div>
                </div>
            </td>
        </tr>
    `);
    $row.after($newRow);

    const $responseDiv = $newRow.find('.response-content');

    // Fetch response data
    $.ajax({
        url: admin_url + 'client/get_testimonial_responses',
        method: 'POST',
        data: { 
            id: id, 
            <?= json_encode($this->security->get_csrf_token_name()) ?>: <?= json_encode($this->security->get_csrf_hash()) ?>
        },
        success: function (res) {
            let html = '<table class="table table-striped table-bordered">';
            html += '<thead><tr>' +
                        '<th>Name & Email</th>' +
                        '<th>Rating</th>' +
                        '<th>Text Response</th>' +
                        '<th>Images</th>' +
                        '<th>Show in Wall</th>' +
                        '<th>Video Response</th>' +
                        '<th>Created At</th>' +
                    '</tr></thead><tbody>';

            if (!Array.isArray(res) || res.length === 0) {
                html += '<tr><td colspan="7" class="text-center text-muted">No responses found.</td></tr>';
            } else {
                res.forEach(item => {
    html += `<tr>
        <td>${item.name || '-'}<br><small>${item.email || '-'}</small></td>
        <td>${renderStars(item.rating)}</td>
        <td>${item.text_response || '-'}</td>
        <td>${
            item.images
                ? flextestimonial_perfect_unserialize_js(item.images).map(image => {
                    return `<a href="${flextestimonial_media_url(image)}" target="_blank">
                        <img src="${flextestimonial_media_url(image)}" alt="Image" style="max-width:50px; max-height:50px;" />
                    </a>`;
                  }).join('')
                : '—'
        }</td>
        <td>${item.featured == 1 ? 'Yes' : 'No'}</td>
        <td>${
            item.video_url 
                ? `<a href="${site_url}uploads/flextestimonial/${item.video_url}" target="_blank">Video</a>`
                : '—'
        }</td>
        <td>${item.created_at && item.created_at !== '0000-00-00 00:00:00' ? item.created_at : '-'}</td>
    </tr>`;
});

            }

            html += '</tbody></table>';
            $responseDiv.html(html);
        },
        error: function () {
            $responseDiv.html('<div class="text-danger p-2">Failed to fetch data. Please try again.</div>');
        }
    });
});

</script>

<script>
  function openPaymentForm(invoiceId) {
	  // Hide the invoice table
	  $('#invoiceTableSection').hide();
	  $('#payment_table').hide();

	  // Load invoice data
	  $.ajax({
		url: admin_url + 'client/ajax_get_invoice_payment_data/' + invoiceId,
		type: 'GET',
		dataType: 'json',
		success: function(response) {
		  if (response.error) {
			alert(response.error);
			$('#invoiceTableSection').show();
			return;
		  }

		  // Populate form fields
		  $('#invoice_id_hidden').val(invoiceId);
		  var dueAmount = parseFloat(response.amount);
		  if (typeof updateInvoicePaymentDueAmount === 'function') {
			updateInvoicePaymentDueAmount(!isNaN(dueAmount) ? dueAmount : null);
		  } else {
			var $amountInput = $('#invoice_payment_amount');
			$amountInput.val('');
			if (!isNaN(dueAmount)) {
			  var formattedDue = dueAmount.toFixed(2);
			  $amountInput.attr('data-due', formattedDue);
			  $amountInput.attr('max', formattedDue);
			  $('#invoice_payment_amount_hint').text("<?= _l('due_amount'); ?>: " + formattedDue).show();
			} else {
			  $amountInput.removeAttr('data-due');
			  $amountInput.removeAttr('max');
			  $('#invoice_payment_amount_hint').hide().text('');
			}
			$('#invoice_payment_amount_error').hide().text('');
			$amountInput.removeClass('is-invalid');
		  }

		  $('input[name="date"]').val(response.date);

		  // Show the form section
		  $('#invoicePaymentFormSection').fadeIn();
		},
		error: function() {
		  alert('Error loading invoice data');
		  $('#invoiceTableSection').show();
		}
	  });
	}


  // Back button
  $(document).on('click', '#btnBackToTable', function () {
    $('#invoicePaymentFormSection').hide();
    $('#invoiceTableSection').fadeIn();
    $('#payment_table').show();
  });
</script>

<script>
$(function () {
  let casesheetInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  // Initialize if already active on load
  if ($('#tab_casesheet').length && $('#tab_casesheet').hasClass('active')) {
    if (!$.fn.DataTable.isDataTable('.table-casesheet')) {
      initDataTable(
        '.table-casesheet',
        admin_url + 'client/get_casesheet_table_data/' + client_id,
        [1], // order by column index 1
        [1]  // disable ordering on column index 1
      );
      casesheetInitialized = true;
    }
  }

  // Initialize on manual tab switch
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr('href');
    if (target === '#tab_casesheet' && !casesheetInitialized) {
      if (!$.fn.DataTable.isDataTable('.table-casesheet')) {
        initDataTable(
          '.table-casesheet',
          admin_url + 'client/get_casesheet_table_data/' + client_id,
          [1], // order by column index 1
          [1]  // disable ordering on column index 1
        );
        casesheetInitialized = true;
      }
    }
  });
});
</script>


<script>
$(function () {
  let prescriptionInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  // Initialize if already active on page load
  if ($('#tab_prescription').length && $('#tab_prescription').hasClass('active')) {
    if (!$.fn.DataTable.isDataTable('.table-doctor-prescription')) {
      initDataTable(
        '.table-doctor-prescription',
        admin_url + 'client/get_doctor_prescription_table_data/' + client_id,
        [1], // order column
        [1]  // disable ordering on column
      );
      prescriptionInitialized = true;
    }
  }

  // Initialize on manual tab switch
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr('href');
    if (target === '#tab_prescription' && !prescriptionInitialized) {
      if (!$.fn.DataTable.isDataTable('.table-doctor-prescription')) {
        initDataTable(
          '.table-doctor-prescription',
          admin_url + 'client/get_doctor_prescription_table_data/' + client_id,
          [1],
          [1]
        );
        prescriptionInitialized = true;
      }
    }
  });
});
</script>

<script>
$(function () {
  let invoicePaymentsInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  function initInvoicePaymentsTables() {
    if (!$.fn.DataTable.isDataTable('.table-invoice-payments')) {
      initDataTable('.table-invoice-payments', admin_url + 'client/get_invoice_payments_table_data/' + client_id, [1], [1]);
    }
    if (!$.fn.DataTable.isDataTable('.table-payment-table')) {
      initDataTable('.table-payment-table', admin_url + 'client/get_payments_table_data/' + client_id, [1], [1]);
    }
    invoicePaymentsInitialized = true;
  }

  // Initialize if already active on page load
  if ($('#tab_payments').length && $('#tab_payments').hasClass('active')) {
    initInvoicePaymentsTables();
  }

  // Initialize on tab shown
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr('href');
    if (target === '#tab_payments' && !invoicePaymentsInitialized) {
      initInvoicePaymentsTables();
    }
  });
});
</script>

<script>
$(function () {
  let visitAppointmentsInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  function initVisitAppointmentsTable() {
    if (!$.fn.DataTable.isDataTable('.table-visit-appointment-table')) {
      initDataTable('.table-visit-appointment-table', admin_url + 'client/get_visit_appointment_table_data/' + client_id, [1], [1]);
    }
    visitAppointmentsInitialized = true;
  }

  // If the tab is already active on page load
  if ($('#tab_visits').length && $('#tab_visits').hasClass('active')) {
    initVisitAppointmentsTable();
  }

  // On tab switch
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr('href');
    if (target === '#tab_visits' && !visitAppointmentsInitialized) {
      initVisitAppointmentsTable();
    }
  });
});
</script>

<script>
$(function () {
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  // Check if #tab_calls is active on page load
  if ($('#tab_calls').length && $('#tab_calls').hasClass('active')) {
    if (!$.fn.DataTable.isDataTable('.table-call-logs-table')) {
      initDataTable('.table-call-logs-table', admin_url + 'client/get_call_logs_table_data/' + client_id, [1], [1]);
    }
  }

  // Also bind for manual tab switch
  $('a[href="#tab_calls"]').on('shown.bs.tab', function () {
    if (!$.fn.DataTable.isDataTable('.table-call-logs-table')) {
      initDataTable('.table-call-logs-table', admin_url + 'client/get_call_logs_table_data/' + client_id, [1], [1]);
    }
  });
});
</script>

<script>
$(function () {
  let estimatesInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  function initEstimatesTable() {
    if (!$.fn.DataTable.isDataTable('.table-estimates-table')) {
      initDataTable(
        '.table-estimates-table',
        admin_url + 'client/get_estimates_table_data/' + client_id,
        [1], // order column index
        [1]  // columns not orderable
      );
    }
    estimatesInitialized = true;
  }

  // Initialize if tab is already active on page load
  if ($('#tab_estimation').length && $('#tab_estimation').hasClass('active')) {
    initEstimatesTable();
  }

  // Initialize when the tab is manually clicked
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if ($(e.target).attr('href') === '#tab_estimation' && !estimatesInitialized) {
      initEstimatesTable();
    }
  });
});
</script>

<script>
$(function () {
  let feedbackInitialized = false;
  let client_id = <?= $client->userid ?>;
  let admin_url = "<?= admin_url(); ?>";

  function initFeedbackTable() {
    if (!$.fn.DataTable.isDataTable('.table-feedback-table')) {
      initDataTable(
        '.table-feedback-table',
        admin_url + 'client/get_feedback_table_data/' + client_id,
        [1], // order column index
        [1]  // columns not orderable
      );
    }
    feedbackInitialized = true;
  }

  // Initialize if tab is already active on page load
  if ($('#tab_feedback').length && $('#tab_feedback').hasClass('active')) {
    initFeedbackTable();
  }

  // Initialize when the tab is manually switched to
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if ($(e.target).attr('href') === '#tab_feedback' && !feedbackInitialized) {
      initFeedbackTable();
    }
  });
});
</script>


      </div>
      </div>
    </div>
  </div>
</div>
