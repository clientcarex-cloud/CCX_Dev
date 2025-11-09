<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('gdpr_model');
$this->ci->load->model('leads_model');
$this->ci->load->model('staff_model');
$statuses = $this->ci->leads_model->get_status();

if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
    $consent_purposes = $this->ci->gdpr_model->get_consent_purposes();
}

$rules = [
    App_table_filter::new('name', 'TextRule')->label(_l('leads_dt_name')),
    //App_table_filter::new('lead_age', 'TextRule')->label(_l('lead_add_edit_age')),
    App_table_filter::new('lead_gender', 'TextRule')->label(_l('lead_gender')),
    App_table_filter::new('phonenumber', 'TextRule')->label(_l('leads_dt_phonenumber')),
    App_table_filter::new('country', 'SelectRule')->label(_l('lead_country'))->options(function ($ci) {
        return collect(get_all_countries())->map(fn ($country) => [
            'value' => $country['country_id'],
            'label' => $country['short_name'],
        ]);
    }),
    App_table_filter::new('city', 'TextRule')->label(_l('lead_city')),
    App_table_filter::new('state', 'TextRule')->label(_l('lead_state')),
    App_table_filter::new('zip', 'TextRule')->label(_l('lead_zip')),
    App_table_filter::new('is_public', 'BooleanRule')->label(_l('lead_public')),
    App_table_filter::new('lost', 'BooleanRule')->label(_l('lead_lost')),
    App_table_filter::new('junk', 'BooleanRule')->label(_l('lead_junk')),
    App_table_filter::new('lastcontact', 'DateRule')->label(_l('leads_dt_last_contact')),
    App_table_filter::new('dateadded', 'DateRule')->label(_l('date_created')),
    App_table_filter::new('dateassigned', 'DateRule')->label(_l('customer_admin_date_assigned')),
    App_table_filter::new('lead_value', 'NumberRule')->label(_l('lead_add_edit_lead_value')),
    App_table_filter::new('status', 'MultiSelectRule')->label(_l('lead_status'))->options(function () use ($statuses) {
        return collect($statuses)->map(fn ($status) => [
            'value'   => $status['id'],
            'label'   => $status['name'],
            'subtext' => $status['isdefault'] == 1 ? _l('leads_converted_to_client') : null,
        ]);
    }),
    App_table_filter::new('source', 'MultiSelectRule')->label(_l('lead_source'))->options(function ($ci) {
        return collect($ci->leads_model->get_source())->map(fn ($source) => [
            'value' => $source['id'],
            'label' => $source['name'],
        ]);
    }),
];

$rules[] = App_table_filter::new('assigned', 'SelectRule')->label(_l('leads_dt_assigned'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->isVisible(fn () => staff_can('view', 'leads'))
    ->options(function ($ci) {
        $staff = $ci->staff_model->get('', ['active' => 1]);

        return collect($staff)->map(function ($staff) {
            return [
                'value' => $staff['staffid'],
                'label' => $staff['firstname'] . ' ' . $staff['lastname'],
            ];
        })->all();
    });

if (isset($consent_purposes)) {
    $rules[] = App_table_filter::new('gdpr_content', 'SelectRule')
        ->label(_l('gdpr_consent'))
        ->options(function () use ($consent_purposes) {
            return collect($consent_purposes)->map(fn ($purpose) => [
                'value' => $purpose['id'],
                'label' => $purpose['name'],
            ]);
        })->raw(function ($value, $operator, $sql_operator) {
            return db_prefix() . 'leads.id ' . $sql_operator . ' (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $value . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $value . ' AND lead_id=' . db_prefix() . 'leads.id))';
        });
}

return App_table::find('leads')
    ->outputUsing(function ($params) use ($statuses) {
        extract($params);

        $lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');
        $has_permission_delete = staff_can('delete', 'leads');
        $has_permission_edit = staff_can('edit', 'leads');
        $custom_fields         = get_table_custom_fields('leads');
        $consentLeads          = get_option('gdpr_enable_consent_for_leads');

        // These are the columns that will be DIRECTLY DISPLAYED in the table
        // The order and count here must match the <th> tags in your HTML
        $aColumns = [
            '1', // 0: Checkbox column (not from DB, but a display column)
            db_prefix() . 'leads.id as id', // 1: Lead ID
            db_prefix() . 'leads.name as name', // 2: Lead Name
            db_prefix() . 'leads.assigned as assigned', // 3: Lead Age
            db_prefix() . 'leads.phonenumber as phonenumber', // 4: Phone Number
            db_prefix() . 'leads.lead_gender as lead_gender', // 5: Lead Gender
            'latest_treatment.treatment_name',
        ];

        // Conditionally add GDPR consent column if enabled and needed for display
        if (is_gdpr() && $consentLeads == '1') {
            $aColumns[] = '1'; // 7 (if GDPR is enabled)
        }

        // Add the remaining DISPLAYED columns
        $aColumns = array_merge($aColumns, [
            db_prefix() . 'leads_sources.name as source_name', // 7 or 8 (depending on GDPR)
            db_prefix() . 'leads.lead_priority as lead_priority', // 8 or 9 (depending on GDPR)
            db_prefix() . 'leads_status.name as status_name', // 9 or 10 (depending on GDPR)
            '(SELECT created_date FROM ' . db_prefix() . 'lead_call_logs WHERE leads_id = ' . db_prefix() . 'leads.id ORDER BY created_date DESC LIMIT 1) as called_date', // 10 or 11 (depending on GDPR)
            '(SELECT followup_date FROM ' . db_prefix() . 'lead_call_logs WHERE leads_id = ' . db_prefix() . 'leads.id ORDER BY created_date DESC LIMIT 1) as followup_date', // 11 or 12 (depending on GDPR)
            'dateadded', // 12 or 13 (depending on GDPR)
        ]);

        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'leads';

        $join = [
            'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',
            'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
            'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
        ];
		
		$join[] = 'LEFT JOIN (
			SELECT 
				a.userid,
				i.description AS treatment_name
			FROM ' . db_prefix() . 'appointment AS a
			JOIN ' . db_prefix() . 'items AS i ON i.id = a.treatment_id
			WHERE a.treatment_id IS NOT NULL
			GROUP BY a.userid
			ORDER BY a.appointment_id DESC
		) AS latest_treatment ON latest_treatment.userid = (
			SELECT c.userid FROM ' . db_prefix() . 'clients AS c WHERE c.leadid = ' . db_prefix() . 'leads.id LIMIT 1
		)';

        $customFieldsColumns = []; // Initialize this array
        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs); // Custom fields are added here
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'leads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        if (staff_cant('view', 'leads')) {
            array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }

        $aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        // Additional columns for data that is fetched but not necessarily displayed as its own column.
        // E.g., for logic, tooltips, or combining with other data in a single column.
        // Additional columns for data that is fetched but not necessarily displayed as its own column.
        // E.g., for logic, tooltips, or combining with other data in a single column.
        $additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
            'junk',
            'lost',
            'color',
            'status',
            'assigned',
            'lastname as assigned_lastname', // Used for assigned_firstname + lastname output
            'firstname as assigned_firstname', // Used for assigned_firstname + lastname output
            db_prefix() . 'leads.addedfrom as addedfrom',
            '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',
            'zip',
            db_prefix() . 'leads.email as email', // <--- CORRECTED LINE HERE
            'address', // Fetch address if you need it for any logic, but it's not a direct column
            'lead_value', // Fetch lead_value if you need it for any logic, but it's not a direct column
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags', // Fetch tags if you need it for any logic, but it's not a direct column
            'lastcontact', // Fetch lastcontact if you need it for any logic, but it's not a direct column
        ]);
		
        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        // Apply sorting by lead ID in descending order
        usort($rResult, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        foreach ($rResult as $aRow) {
            $row = [];

            // COLUMN 0: Checkbox
            $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

            $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
            
            // COLUMN 1: Lead ID
            $row[]    = '<a ' . $hrefAttr . ' class="tw-font-medium">' . $aRow['id'] . '</a>';

            // COLUMN 2: Name
            $nameRow = '<a ' . $hrefAttr . ' class="tw-font-medium">' . e(format_name($aRow['name'])) . '</a>';
            $nameRow .= '<div class="row-options">';
			
            if(staff_can('view', 'leads')){
                $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';
            }
           
            $locked = false;
            if ($aRow['is_converted'] > 0) {
                $locked = ((! is_admin() && $lockAfterConvert == 1) ? true : false);
            }

            if ($has_permission_edit) {
                $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
            }

            if ($has_permission_delete) {
                $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
            }
            $nameRow .= '</div>';
            $row[] = $nameRow;
			

            // COLUMN 4: Phone Number
            //$row[] = ($aRow['phonenumber'] != '' ? '<a href="tel:' . e($aRow['phonenumber']) . '">' . e($aRow['phonenumber']) . '</a>' : '');
			
			if (staff_can('mobile_masking', 'customers') && !is_admin()) {
				$phonenumber = mask_last_5_digits($aRow['phonenumber']);
			} else {
				$phonenumber = $aRow['phonenumber'];
			}
			$row[] = $phonenumber;
			

            // COLUMN 5: Lead Gender
            $row[] = e($aRow['lead_gender']);

            // COLUMN 6: Treatment Name
            $row[] = e($aRow['treatment_name']); 
            
            // COLUMN 7 (CONDITIONAL): GDPR Consent - ENSURE THIS IS UNCOMMENTED IF GDPR IS ACTIVE
            if (is_gdpr() && $consentLeads == '1') {
                $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
                $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

                foreach ($consents as $consent) {
                    $consentHTML .= '<p style="margin-bottom:0px;">' . e($consent['name']) . (! empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
                }
                $row[] = $consentHTML;
            }

            // The assignedOutput is generated but not added as its own $row[] cell.
            // If you intented it to be a column, you need to add $row[] = $assignedOutput; and a corresponding <th>
            // For now, it's not a separate column.
            $assignedOutput = '';
            if ($aRow['assigned'] != 0) {
                $full_name = e($aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname']);

                $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
                    'staff-profile-image-small',
                ]) . '</a>';

                $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
            }

            // Status output logic (no change needed here)
            $outputStatus = '';
            if ($aRow['status_name'] == null) {
                if ($aRow['lost'] == 1) {
                    $outputStatus = '<span class="label label-danger">' . _l('lead_lost') . '</span>';
                } elseif ($aRow['junk'] == 1) {
                    $outputStatus = '<span class="label label-warning">' . _l('lead_junk') . '</span>';
                }
            } else {
                if (! $locked) {
                    $outputStatus .= '<div class="dropdown inline-block table-export-exclude">';
                    $outputStatus .= '<label class="dropdown-toggle tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';" >';
                    $outputStatus .= e($aRow['status_name']);
                    $outputStatus .= '</label>';

                    $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';

                    foreach ($statuses as $leadChangeStatus) {
                        if ($aRow['status'] != $leadChangeStatus['id']) {
                            $outputStatus .= '<li><a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">' . e($leadChangeStatus['name']) . '</a></li>';
                        }
                    }
                    $outputStatus .= '</ul>';
                    $outputStatus .= '</div>';
                } else {
                    $outputStatus = '<span class="lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';">' . e($aRow['status_name']) . '</span>';
                }
            }
            
            // Remaining Columns (adjust index comments based on GDPR status)
            // If GDPR is ENABLED:
            // COLUMN 8: Source Name
            // COLUMN 9: Lead Priority
            // COLUMN 10: Status
            // COLUMN 11: Called Date
            // COLUMN 12: Followup Date
            // COLUMN 13: Date Added

            // If GDPR is DISABLED:
            // COLUMN 7: Source Name
            // COLUMN 8: Lead Priority
            // COLUMN 9: Status
            // COLUMN 10: Called Date
            // COLUMN 11: Followup Date
            // COLUMN 12: Date Added
			//$row[] = "Branch";
            $row[] = e($aRow['source_name']); 
            $row[] = e($aRow['lead_priority']); 
            $row[] = get_staff_full_name($aRow['assigned']);
            $row[] = $outputStatus; 
            $row[] = _d($aRow['called_date']); 
            $row[] = _d($aRow['followup_date']); 
            $row[] = '<span data-toggle="tooltip" data-title="' . e(_dt($aRow['dateadded'])) . '" class="text-has-action is-date">' . e(time_ago($aRow['dateadded'])) . '</span>'; 
            
            // Custom fields add values - these will continue the index dynamically
            foreach ($customFieldsColumns as $customFieldColumn) {
                $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
            }

            $row['DT_RowId']    = 'lead_' . $aRow['id'];
            $row['DT_RowClass'] = 'has-border-left';

            if ($aRow['assigned'] == get_staff_user_id()) {
                $row['DT_RowClass'] .= ' row-border-info';
            }

            if (isset($row['DT_RowClass'])) {
                $row['DT_RowClass'] .= ' has-row-options';
            } else {
                $row['DT_RowClass'] = 'has-row-options';
            }

            $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules($rules);
	
	function mask_last_5_digits($number) {
    $reversed = strrev($number);
    $masked = '';
    $digitCount = 0;

    for ($i = 0; $i < strlen($reversed); $i++) {
        $char = $reversed[$i];
        if (ctype_digit($char)) {
            if ($digitCount < 5) {
                $masked .= '*';
                $digitCount++;
            } else {
                $masked .= $char;
            }
        } else {
            $masked .= $char;
        }
    }

    return strrev($masked);
}