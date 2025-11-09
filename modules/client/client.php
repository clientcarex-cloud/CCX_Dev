<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Dr.Care ERP Modules
Description: Dr.Care ERP Modules
Version: 1.0.0
*/

define('CLIENT_MODULE_NAME', 'client');
log_message('debug', 'client module loaded');

hooks()->add_action('admin_init', CLIENT_MODULE_NAME.'_init_menu_items');

function client_init_menu_items()
{
    $CI = &get_instance();
	if (staff_can('view_own', 'customers') || staff_can('view', 'customers') || staff_can('view_appointments', 'customers')) {
    $CI->app_menu->add_sidebar_menu_item('client', [
        'name'     => _l('patients'),
        'icon'     => 'fa fa-user',
        'href'     => admin_url('client/get_patient_list'),
        'position' => 2,
    ]);
	}
	
	if (staff_can('view_prescription', 'customers')) {
		$CI->app_menu->add_sidebar_menu_item('pharmacy', [
			'name'     => _l('pharmacy'),
			'icon'     => 'fa fa-file-medical',
			'href'     => admin_url('client/pharmacy'),
			'position' => 2,
		]);
	}
	
	if (staff_can('mis_reports', 'customers')) {
		$CI->app_menu->add_sidebar_menu_item('mis_reports', [
			'name'     => _l('mis_reports'),
			'icon'     => 'fa-solid fa-chart-line menu-icon',
			'href'     => admin_url('client/reports/mis_reports'),
			'position' => 60,
		]);
	}
	/* if (staff_can('view_own', 'customers') || staff_can('view', 'customers')) {
    $CI->app_menu->add_sidebar_children_item('client', [
        'slug'     => 'patients_list',
        'name'     => _l('patients_list'),
        'href'     => admin_url('client/get_patient_list'),
        'position' => 1,
        ]);
	} */
    /* $CI->app_menu->add_sidebar_children_item('client', [
        'slug'     => 'appointments',
        'name'     => _l('appointments_list'),
        'href'     => admin_url('client/appointments'),
        'position' => 3,
        ]); */
		
		/* if (staff_can('view_visits', 'customers')){
			$CI->app_menu->add_sidebar_children_item('client', [
				'slug'     => 'visits',
				'name'     => _l('visits'),
				'href'     => admin_url('client/visits'),
				'position' => 2,
				]);
		} */
	if (staff_can('view', 'doctor')) {
		$CI->app_menu->add_sidebar_menu_item('doctor', [
			'name'     => _l('doctor'),
			'icon'     => 'fa fa-user-md',
			'href'     => admin_url('client/doctor'),
			'position' => 10,
		]);
    }
	
	if (staff_can('calling', 'customers')) {
		$CI->app_menu->add_sidebar_menu_item('calling', [
			'name'     => _l('calling'),
			'icon'     => 'fa fa-phone',
			'href'     => admin_url('client/calling'),
			'position' => 10,
		]);
		
		
    }
	
	if (staff_can('cpot_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'CPOT Calling',
			'name'     => _l('cpot_calling'),
			'href'     => admin_url('client/calling/cpot_calling'),
			'position' => 1,
			'badge'    => [],
		]);
	}

	if (staff_can('ppot_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'PPOT Calling',
			'name'     => _l('ppot_calling'),
			'href'     => admin_url('client/calling/ppot_calling'),
			'position' => 2,
			'badge'    => [],
		]);
	}

	/* if (staff_can('fe_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'FE Calling',
			'name'     => _l('fe_calling'),
			'href'     => admin_url('client/calling/fe_calling'),
			'position' => 3,
			'badge'    => [],
		]);
	} */

	if (staff_can('reference_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'Refernce Calling',
			'name'     => _l('reference_calling'),
			'href'     => admin_url('client/calling/reference_calling'),
			'position' => 4,
			'badge'    => [],
		]);
	}

	if (staff_can('renewal_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'Renewal Calling',
			'name'     => _l('renewal_calling'),
			'href'     => admin_url('client/calling/renewal_calling'),
			'position' => 5,
			'badge'    => [],
		]);
	}

	if (staff_can('treatment_followup_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'Treatment Followup Calling',
			'name'     => _l('treatment_followup_calling'),
			'href'     => admin_url('client/calling/treatment_followup_calling'),
			'position' => 6,
			'badge'    => [],
		]);
	}

	if (staff_can('medicine_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'Medicine Calling',
			'name'     => _l('medicine_calling'),
			'href'     => admin_url('client/calling/medicine_calling'),
			'position' => 7,
			'badge'    => [],
		]);
	}

	/* if (staff_can('nroc_calling', 'customers')) {
		$CI->app_menu->add_sidebar_children_item('calling', [
			'slug'     => 'NROC Calling',
			'name'     => _l('nroc_calling'),
			'href'     => admin_url('client/calling/nroc_calling'),
			'position' => 8,
			'badge'    => [],
		]);
	} */

	
	/* if (staff_can('doctor_ownership_report_details', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'doctor_ownership_report_details',
            'name'     => _l('doctor_ownership_report_details'),
            'href'     => admin_url('client/reports/doctor_ownership_report_details'),
            'position' => 25,
            'badge'    => [],
        ]);
    } if (staff_can('appointment_report_fdo', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'appointment_report_fdo',
            'name'     => _l('appointment_report_fdo'),
            'href'     => admin_url('client/reports/appointment_report_fdo'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	if (staff_can('medicine_calling_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'medicine_calling_report',
            'name'     => _l('medicine_calling_report'),
            'href'     => admin_url('client/reports/medicine_calling_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	
	if (staff_can('doctor_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'doctor_report',
            'name'     => _l('doctor_report'),
            'href'     => admin_url('client/reports/doctor_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	
	if (staff_can('enquiry_doctor_ownership_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'enquiry_doctor_ownership_report',
            'name'     => _l('enquiry_doctor_ownership_report'),
            'href'     => admin_url('client/reports/enquiry_doctor_ownership_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
    
	if (staff_can('enquiry_doctor_performance_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'enquiry_doctor_performance_report',
            'name'     => _l('enquiry_doctor_performance_report'),
            'href'     => admin_url('client/reports/enquiry_doctor_performance_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	
	if (staff_can('critical_calling_gt_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'critical_calling_gt_report',
            'name'     => _l('critical_calling_gt_report'),
            'href'     => admin_url('client/reports/critical_calling_gt_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	
	if (staff_can('gt_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'gt_report',
            'name'     => _l('gt_report'),
            'href'     => admin_url('client/reports/gt_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('gt_report_modem', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'gt_report_modem',
            'name'     => _l('gt_report_modem'),
            'href'     => admin_url('client/reports/gt_report_modem'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('central_calling_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'central_calling_report',
            'name'     => _l('central_calling_report'),
            'href'     => admin_url('client/reports/central_calling_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('central_employee_calling_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'central_employee_calling_report',
            'name'     => _l('central_employee_calling_report'),
            'href'     => admin_url('client/reports/central_employee_calling_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('my_call_report_cc', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'my_call_report_cc',
            'name'     => _l('my_call_report_cc'),
            'href'     => admin_url('client/reports/my_call_report_cc'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	if (staff_can('team_calling_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'team_calling_report',
            'name'     => _l('team_calling_report'),
            'href'     => admin_url('client/reports/team_calling_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('my_ownership_report_cc', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'my_ownership_report_cc',
            'name'     => _l('my_ownership_report_cc'),
            'href'     => admin_url('client/reports/my_ownership_report_cc'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('renewal_doctor_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'renewal_doctor_report',
            'name'     => _l('renewal_doctor_report'),
            'href'     => admin_url('client/reports/renewal_doctor_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('renewal_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'renewal_report',
            'name'     => _l('renewal_report'),
            'href'     => admin_url('client/reports/renewal_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	 if (staff_can('payment_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'payment_report',
            'name'     => _l('payment_report'),
            'href'     => admin_url('client/reports/payment_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('refund_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'refund_report',
            'name'     => _l('refund_report'),
            'href'     => admin_url('client/reports/refund_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }  if (staff_can('cheque_status_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'cheque_status_report',
            'name'     => _l('cheque_status_report'),
            'href'     => admin_url('client/reports/cheque_status_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	 if (staff_can('cro_performance_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'cro_performance_report',
            'name'     => _l('cro_performance_report'),
            'href'     => admin_url('client/reports/cro_performance_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } if (staff_can('patient_package_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'patient_package_report',
            'name'     => _l('patient_package_report'),
            'href'     => admin_url('client/reports/patient_package_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	*/
	/* if (staff_can('doctor_ownership_reports', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'doctor_ownership_reports',
            'name'     => _l('doctor_ownership_reports'),
            'href'     => admin_url('client/reports/doctor_ownership_reports'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('pharmacy_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'pharmacy_report',
            'name'     => _l('pharmacy_report'),
            'href'     => admin_url('client/reports/pharmacy_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('appointment_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'appointment_report',
            'name'     => _l('appointment_report'),
            'href'     => admin_url('client/reports/appointment_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	
	if (staff_can('branch_visit_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'branch_visit_report',
            'name'     => _l('branch_visit_report'),
            'href'     => admin_url('client/reports/branch_visit_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('branch_registration_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'branch_registration_report',
            'name'     => _l('branch_registration_report'),
            'href'     => admin_url('client/reports/branch_registration_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('consult_fee_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'consult_fee_report',
            'name'     => _l('consult_fee_report'),
            'href'     => admin_url('client/reports/consult_fee_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	
	if (staff_can('doctor_appointment_list_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'doctor_appointment_list_report',
            'name'     => _l('doctor_appointment_list_report'),
            'href'     => admin_url('client/reports/doctor_appointment_list_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('doctor_appointment_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'doctor_appointment_report',
            'name'     => _l('doctor_appointment_report'),
            'href'     => admin_url('client/reports/doctor_appointment_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
    
	if (staff_can('enquiry_doctor_inactive_patient_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'enquiry_doctor_inactive_patient_report',
            'name'     => _l('enquiry_doctor_inactive_patient_report'),
            'href'     => admin_url('client/reports/enquiry_doctor_inactive_patient_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
    
	if (staff_can('my_appointment_report_cc', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'my_appointment_report_cc',
            'name'     => _l('my_appointment_report_cc'),
            'href'     => admin_url('client/reports/my_appointment_report_cc'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	//Count
	if (staff_can('appointment_report_cc', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'appointment_report_cc',
            'name'     => _l('appointment_report_cc'),
            'href'     => admin_url('client/reports/appointment_report_cc'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
    
    
	if (staff_can('branch_payment_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'branch_payment_report',
            'name'     => _l('branch_payment_report'),
            'href'     => admin_url('client/reports/branch_payment_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } 
	
	if (staff_can('cro_ownership_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'cro_ownership_report',
            'name'     => _l('cro_ownership_report'),
            'href'     => admin_url('client/reports/cro_ownership_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    }
	if (staff_can('tat_report', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'tat_report',
            'name'     => _l('tat_report'),
            'href'     => admin_url('client/reports/tat_report'),
            'position' => 25,
            'badge'    => [],
        ]);
    } */
  
 
}

hooks()->add_filter('after_render_single_custom_field', 'override_staff_select_branch_field', 10, 2);

hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName = _l('permission_view') . ' (' . _l('permission_global') . ')';
    $allPermissionsArray = [
        'view' => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    // For customers, this variable should be defined as it is used in array_merge
    $withNotApplicableViewOwn = [
        'view_own' => _l('permission_view_own'),
        'view'     => $viewGlobalName,
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    $permissions['doctor'] = [
        'name' => _l('doctor'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['customers'] = [
        'name' => _l('clients'),
        'capabilities' => [
			'view_own' => _l('permission_view_own'),
			'view'     => $viewGlobalName,
			'create'   => _l('permission_create'),
			'edit'     => _l('permission_edit'),
			'delete'   => _l('permission_delete'),
            'view_overview' => _l('permission_view_overview'),
            'create_prescription' => _l('permission_create_prescription'),
            'edit_prescription' => _l('permission_edit_prescription'),
            'view_prescription' => _l('permission_view_prescription'),
            'view_appointments_calendar' => _l('permission_view_appointments_calendar'),
            'create_appointment' => _l('create_appointment'),
            'view_appointments' => _l('permission_view_appointments'),
            'view_global_appointments' => _l('view_global_appointments'),
            'view_doctor_ownership' => _l('permission_view_doctor_ownership'),
            'view_invoice' => _l('permission_view_invoice'),
            'create_invoice' => _l('permission_create_invoice'),
            'edit_invoice' => _l('permission_edit_invoice'),
            'view_feedback' => _l('permission_view_feedback'),
            'create_feedback' => _l('permission_create_feedback'),
            'edit_feedback' => _l('permission_edit_feedback'),
            'view_payments' => _l('permission_view_payments'),
            'view_call_log' => _l('permission_view_call_log'),
            'create_call_log' => _l('permission_create_call_log'),
            'edit_call_log' => _l('permission_edit_call_log'),
            'view_activity_log' => _l('permission_view_activity_log'),
            'view_visits' => _l('view_visits'),
            'view_casesheet' => _l('view_casesheet'),
            'edit_casesheet' => _l('edit_casesheet'),
            'edit_limit_casesheet' => _l('edit_limit_casesheet'),
            'create_casesheet' => _l('create_casesheet'),
            'create_estimation' => _l('create_estimation'),
			//New
            'view_all_appointments' => _l('permission_view_all_appointments'),
            'add_task_attendance_api' => _l('permission_add_task_attendance_api'),
            'mobile_masking' => _l('permission_patient_mobile_masking'),
            'export_patients' => _l('permission_export_patients'),
            'import_patients' => _l('permission_import_patients'),
			'calling'          => _l('permission_calling'),
			'cpot_calling'          => _l('permission_cpot_calling'),
			'ppot_calling'          => _l('permission_ppot_calling'),
			'fe_calling'            => _l('permission_fe_calling'),
			'reference_calling'     => _l('permission_reference_calling'),
			'renewal_calling'       => _l('permission_renewal_calling'),
			'treatment_followup_calling' => _l('permission_treatment_followup_calling'),
			'medicine_calling'      => _l('permission_medicine_calling'),
			'nroc_calling'          => _l('permission_nroc_calling'),
			'doctor_ownership_reports' => _l('permission_doctor_ownership_reports'),
			'pharmacy_report' => _l('permission_pharmacy_report'),
			'appointment_slot_report' => _l('permission_appointment_slot_report'),
			'appointment_report' => _l('permission_appointment_report'),
			'branch_visit_report' => _l('permission_branch_visit_report'),
			'branch_registration_report' => _l('permission_branch_registration_report'),
			'consult_fee_report' => _l('permission_consult_fee_report'),
			'medicine_calling_report' => _l('permission_medicine_calling_report'),
			'unit_doctor_report' => _l('permission_unit_doctor_report'),
			'unit_doctor_ownership' => _l('permission_unit_doctor_ownership'),
			'doctor_appointment_list_report' => _l('permission_doctor_appointment_list_report'),
			'doctor_appointment_report' => _l('permission_doctor_appointment_report'),
			'doctor_report' => _l('permission_doctor_report'),
			'employee_incentive_report' => _l('permission_employee_incentive_report'),
			'enquiry_doctor_incentive_patient_report' => _l('permission_enquiry_doctor_incentive_patient_report'),
			'enquiry_doctor_ownership_report' => _l('permission_enquiry_doctor_ownership_report'),
			'enquiry_doctor_performance_report' => _l('permission_enquiry_doctor_performance_report'),
			'casesheet_patient_status_report' => _l('permission_casesheet_patient_status_report'),
			'critical_calling_gt_report' => _l('permission_critical_calling_gt_report'),
			'gt_report' => _l('permission_gt_report'),
			'gt_report_modem' => _l('permission_gt_report_modem'),
			'central_calling_report' => _l('permission_central_calling_report'),
			'central_employee_calling_report' => _l('permission_central_employee_calling_report'),
			'enquiry_doctor_inactive_patient_report' => _l('permission_enquiry_doctor_inactive_patient_report'),
			'my_call_report_cc' => _l('permission_my_call_report_cc'),
			'my_appointment_report_cc' => _l('permission_my_appointment_report_cc'),
			'team_calling_report' => _l('permission_team_calling_report'),
			'my_ownership_report_cc' => _l('permission_my_ownership_report_cc'),
			'renewal_doctor_report' => _l('permission_renewal_doctor_report'),
			'renewal_report' => _l('permission_renewal_report'),
			'branch_payment_report' => _l('permission_branch_payment_report'),
			'payment_report' => _l('permission_payment_report'),
			'refund_report' => _l('permission_refund_report'),
			'cheque_status_report' => _l('permission_cheque_status_report'),
			'cro_ownership_report' => _l('permission_cro_ownership_report'),
			'cro_performance_report' => _l('permission_cro_performance_report'),
			'patient_package_report' => _l('permission_patient_package_report'),
			'confirm_visit' => _l('confirm_visit'),
			'view_message_log' => _l('view_message_log'),
			'branch_filter' => _l('branch_filter'),
			'token_smart_queue' => _l('token_smart_queue'),
			'token_emergency_lunch_break' => _l('token_emergency_lunch_break'),
			'multiple_appointments_restriction' => _l('multiple_appointments_restriction'),
            'mis_reports' => _l('mis_reports'),
			'admin_reports' => _l('admin_reports'),
			'productivity_reports' => _l('productivity_reports'),
			'doctor_reports' => _l('doctor_reports'),
			'sales_reports' => _l('sales_reports'),
			'cce_reports' => _l('cce_reports'),
			'cse_reports' => _l('cse_reports'),
			'manager_reports' => _l('manager_reports'),
			'other_reports' => _l('other_reports'),

        ],
        'help' => [
            'view_own' => _l('permission_customers_based_on_admins'),
        ],
    ];

    return $permissions;
});




register_activation_hook(CLIENT_MODULE_NAME, 'client_module_activation_hook');

function client_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    client_install();
}

register_deactivation_hook(CLIENT_MODULE_NAME, 'client_module_uninstall_hook');

function client_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    client_uninstall();
}

register_language_files(CLIENT_MODULE_NAME, [CLIENT_MODULE_NAME]);



hooks()->add_action('app_admin_footer', function () {
    ?>
    <script>
        $(function () {
            setTimeout(function () {
                var select = $("select[data-fieldto='staff'][data-fieldid='1']");
                if (select.length) {
                    $.ajax({
                        url: admin_url + 'client/get_dynamic_options',
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            select.empty();
                            select.append('<option value=""></option>');
                            $.each(data, function (i, item) {
                                select.append($('<option>').val(item.value).text(item.label));
                            });

                            var currentVal = select.attr('data-custom-field-value');
                            if (currentVal) {
                                select.val(currentVal);
                                select.selectpicker('refresh');
                            } else {
                                select.selectpicker('refresh');
                            }
                        }
                    });
                }
            }, 500);
        });
    </script>
    <?php
});



hooks()->add_action('admin_navbar_start', 'fullscreen_toggle_button');

function fullscreen_toggle_button()
{
    if (staff_can('view', 'settings')) {
        echo '
        <li>
            <a href="javascript:void(0);" id="fullscreenToggleBtn" title="' . _l('fullscreen') . '">
                <i class="fa fa-compress" id="fullscreenIcon"></i>
            </a>
        </li>
        ';
    }
}


hooks()->add_action('app_admin_footer', 'fullscreen_toggle_js');

function fullscreen_toggle_js()
{
    ?>
    <script>
    $(document).ready(function () {
    function enterFullscreen() {
        let docElm = document.documentElement;
        if (docElm.requestFullscreen) {
            docElm.requestFullscreen();
        } else if (docElm.msRequestFullscreen) {
            docElm.msRequestFullscreen();
        } else if (docElm.mozRequestFullScreen) {
            docElm.mozRequestFullScreen();
        } else if (docElm.webkitRequestFullscreen) {
            docElm.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
        $('#fullscreenIcon').removeClass('fa-expand').addClass('fa-compress');
    }

    // Auto fullscreen on first interaction (not page load directly)
    function enableFullscreenOnce() {
        enterFullscreen();
       // $(document).off('click keydown', enableFullscreenOnce);
    }

    //$(document).on('click keydown', enableFullscreenOnce);

    // Manual toggle via button
    $('#fullscreenToggleBtn').on('click', function () {
        if (!document.fullscreenElement &&
            !document.mozFullScreenElement &&
            !document.webkitFullscreenElement &&
            !document.msFullscreenElement) {
            enterFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            $('#fullscreenIcon').removeClass('fa-compress').addClass('fa-expand');
        }
    });
});

    </script>
    <?php
}

hooks()->add_action('admin_init', 'add_feedback_templates_settings_tab');

function add_feedback_templates_settings_tab()
{
    $CI = &get_instance();

    $CI->app->add_settings_section('feedback_templates', [
        'title'    => _l('Feedback Templates'), // Tab Title
        'position' => 25,
        'children' => [
            [
                'name'     => _l('Feedback Template Setup'), // Link name in settings
                'view'     => 'client/feedback_template_setup', // Single view file
                'position' => 1,
                'icon'     => 'fa-regular fa-message',
            ],
        ],
    ]);
}

hooks()->add_action('admin_init', 'sms_whatsapp_email_template');

function sms_whatsapp_email_template()
{
    $CI = &get_instance();

    $CI->app->add_settings_section('sms_whatsapp_email_template', [
        'title'    => _l('SMS, Email, WhatsApp Templates'), // Tab Title
        'position' => 25,
        'children' => [
            [
                'name'     => _l('Templates'), // Link name in settings
                'view'     => 'client/sms_email_whatsapp_template_setup', // Single view file
                'position' => 1,
                'icon'     => 'fa-regular fa-message',
            ],
        ],
    ]);
	
	
}

