<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
			
           
            <?php
              extract($master_data);
              $patient = json_decode(json_encode($client_data), true);
              if ($appointment_data) {
                $appointment_data = $appointment_data[0];
              }
              $appointment_data = json_decode(json_encode($appointment_data), true);

              if (!function_exists('add_form_group_class')) {
                  function add_form_group_class($html, $class = '')
                  {
                      $class = trim($class);
                      $replacement = 'class="form-group' . ($class ? ' ' . $class : '') . '"';
                      return preg_replace('/class="form-group"/', $replacement, $html, 1);
                  }
              }
            ?>

            <form method="post" action="<?= admin_url('client/update_client'); ?>">
              <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
              <?= form_hidden('userid', $patient['userid']); ?>

              <div class="form-group">
                <?= render_input('contact_number', 'contact_number', $patient['phonenumber'], 'text', [
					'required' => true,
					'readonly' => true
				]); ?>

              </div>

              <h4 class="customer-profile-group-heading"><?= _l('section_title_patient_information'); ?></h4>

              <div class="row" style="padding: 15px">
			  <?php
    $patient_inactive_fields = array_map('trim', explode(",", $patient_inactive_fields));
    $fields = [];
    // Collect only active fields for patient_name section
    if (!in_array('company', $patient_inactive_fields) || !in_array('salutation', $patient_inactive_fields) || !in_array('gender', $patient_inactive_fields)) {
        ob_start(); ?>
        <div class="form-group mtop15">
            <label class="form-label"><span class="text-danger">*</span> <?= _l('patient_name'); ?></label>
            <div class="row">
                <?php if (!in_array('salutation', $patient_inactive_fields)) { ?>
                    <div class="col-md-2 col-sm-4 col-xs-12 mtop10">
                        <select class="form-control" name="salutation">
                            <option value="Master" <?= ($patient['salutation'] ?? '') == 'Master' ? 'selected' : '' ?>>Master</option>
                            <option value="Baby" <?= ($patient['salutation'] ?? '') == 'Baby' ? 'selected' : '' ?>>Baby</option>
                            <option value="Mr." <?= ($patient['salutation'] ?? '') == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                            <option value="Mrs." <?= ($patient['salutation'] ?? '') == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                            <option value="Ms." <?= ($patient['salutation'] ?? '') == 'Ms.' ? 'selected' : '' ?>>Ms.</option>
                        </select>
                    </div>
                <?php } ?>
                <?php if (!in_array('company', $patient_inactive_fields)) { ?>
                    <div class="<?= !in_array('salutation', $patient_inactive_fields) && !in_array('gender', $patient_inactive_fields) ? 'col-md-6' : 'col-md-8'; ?> col-sm-8 col-xs-12 mtop10">
                        <input type="text" class="form-control" name="company" placeholder="<?= _l('enter_patient_name'); ?>" value="<?= htmlspecialchars($patient['company'] ?? '') ?>" required>
                    </div>
                <?php } ?>
                <?php if (!in_array('gender', $patient_inactive_fields)) { ?>
                    <div class="col-md-4 col-sm-6 col-xs-12 mtop10">
                        <select class="form-control" name="gender">
                            <option value="Male" <?= ($patient['gender'] ?? '') == 'Male' ? 'selected' : '' ?>><?= _l('male') ?></option>
                            <option value="Female" <?= ($patient['gender'] ?? '') == 'Female' ? 'selected' : '' ?>><?= _l('female') ?></option>
                            <option value="Other" <?= ($patient['gender'] ?? '') == 'Other' ? 'selected' : '' ?>><?= _l('other') ?></option>
                        </select>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $fields[] = ['html' => ob_get_clean(), 'col' => 12];
    }
	?>
	
	<?php
$has_valid_dob = !empty($patient['dob']) && $patient['dob'] != '1970-01-01';
// Get selected values as array
$selected_languages = isset($patient['default_language']) 
                      ? array_map('trim', explode(',', $patient['default_language'])) 
                      : [];
    // Map of field name => field HTML
$fields_map = [];

ob_start(); ?>
<div class="form-group mtop15">
    <label class="form-label"><span class="text-danger">*</span> <?= _l('age_dob_gender'); ?></label>
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12 mtop10">
            <select id="ageDobSelector" class="form-control" name="dob_type" required>
                <option value="age" <?= !$has_valid_dob ? 'selected' : '' ?>><?= _l('age'); ?></option>
                <option value="dob" <?= $has_valid_dob ? 'selected' : '' ?>><?= _l('date_of_birth'); ?></option>
            </select>
        </div>
        <div class="col-md-8 col-sm-6 col-xs-12 mtop10">
            <input type="<?= $has_valid_dob ? 'date' : 'text'; ?>" id="ageDobInput" class="form-control" required>
        </div>
    </div>
    <input type="hidden" name="age" id="hiddenAge" value="<?= htmlspecialchars($patient['age'] ?? '') ?>">
    <input type="hidden" name="dob" id="hiddenDob" value="<?= $has_valid_dob ? htmlspecialchars($patient['dob']) : '' ?>">
</div>
<?php
$fields_map['age_gender'] = [
    'col'  => 6,
    'html' => ob_get_clean(),
];

$fields_map['area'] = [
    'col'  => 4,
    'html' => add_form_group_class(render_input('area', 'area', $patient['area'] ?? '', 'text', ['placeholder' => _l('enter_area')]), 'mtop15'),
];

ob_start(); ?>
<div class="form-group mtop15">
    <label class="form-label"><span class="text-danger">*</span> <?= _l('contact_number'); ?></label>
    <div class="row">
        <div class="col-md-4 col-sm-5 col-xs-12 mtop10">
            <select name="calling_code" id="calling_code" class="form-control selectpicker" data-live-search="true" required>
                <option value=""><?= _l('select_country_code'); ?></option>
                <?php foreach (get_all_countries() as $country): ?>
                    <?php
                        $selected = '';
                        if (isset($lead) && $lead->country == $country['country_id']) {
                            $selected = 'selected';
                        } elseif (($country['short_name'] ?? '') === 'India' && empty($lead)) {
                            $selected = 'selected';
                        }
                    ?>
                    <option value="<?= htmlspecialchars($country['calling_code']); ?>" <?= $selected; ?>>
                        +<?= htmlspecialchars($country['calling_code']); ?> (<?= htmlspecialchars($country['short_name']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-8 col-sm-7 col-xs-12 mtop10">
            <input type="text" class="form-control" name="contact_number" placeholder="<?= _l('enter_contact_number'); ?>" value="<?= htmlspecialchars($patient['phonenumber'] ?? $contact_number); ?>" required>
        </div>
    </div>
</div>
<?php
$fields_map['contact_number'] = [
    'col'  => 6,
    'html' => ob_get_clean(),
];

$fields_map['alt_number1'] = [
    'col'  => 4,
    'html' => add_form_group_class(render_input('alt_number1', 'alternative_number1', $patient['alt_number1'] ?? '', 'text', ['placeholder' => _l('enter_alt_number1')]), 'mtop15') . '<input type="hidden" name="alt_number2" value="0">',
];

$fields_map['email_id'] = [
    'col'  => 4,
    'html' => add_form_group_class(render_input('email_id', 'email', $patient['email_id'] ?? '', 'email', ['placeholder' => _l('enter_email')]), 'mtop15'),
];

$fields_map['marital_status'] = [
    'col'  => 4,
    'html' => add_form_group_class(render_select(
        'marital_status',
        [
            ['id' => 'Single', 'name' => _l('single')],
            ['id' => 'Married', 'name' => _l('married')],
            ['id' => 'Divorced', 'name' => _l('divorced')],
            ['id' => 'Widowed', 'name' => _l('widowed')],
        ],
        ['id', 'name'],
        _l('marital_status'),
        $patient['marital_status'] ?? '',
        ['data-none-selected-text' => _l('dropdown_non_selected_tex')],
        [],
        '',
        'selectpicker'
    ), 'mtop15'),
];

$language_select_html = render_select(
    'default_language[]',
    $languages,
    ['languages_name', 'languages_name'],
    _l('speaking_languages'),
    $selected_languages,
    [
        'multiple' => true,
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'data-live-search' => 'true'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['language_select'] = [
    'col'  => 6,
    'html' => add_form_group_class($language_select_html, 'mtop15'),
];

$selected_country = $patient['country'] ?? ($lead->country ?? '');
$country_select_html = render_select(
    'country',
    get_all_countries(),
    ['country_id', 'short_name'],
    _l('country'),
    $selected_country,
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'data-live-search' => 'true'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['country_select'] = [
    'col'  => 4,
    'html' => add_form_group_class($country_select_html, 'mtop15'),
];

$state_select_html = render_select(
    'state',
    $states,
    ['state_id', 'state_name'],
    _l('state'),
    ($patient['state'] ?? ''),
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'data-live-search' => 'true',
        'id' => 'state'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['state'] = [
    'col'  => 4,
    'html' => add_form_group_class($state_select_html, 'mtop15'),
];

$city_select_html = render_select(
    'city',
    $cities,
    ['city_id', 'city_name'],
    _l('city'),
    ($patient['city'] ?? ''),
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'data-live-search' => 'true',
        'id' => 'city'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['city'] = [
    'col'  => 4,
    'html' => add_form_group_class($city_select_html, 'mtop15'),
];

$pincode_select_html = render_select(
    'pincode',
    $pincodes,
    ['pincode_id', 'pincode_name'],
    _l('pincode'),
    ($patient['pincode'] ?? ''),
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'data-live-search' => 'true',
        'id' => 'pincode'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['pincode'] = [
    'col'  => 4,
    'html' => add_form_group_class($pincode_select_html, 'mtop15'),
];

$patient_source_select_html = render_select(
    'patient_source_id',
    $patient_source,
    ['id', 'name'],
    '<span class="text-danger">*</span> ' . _l('patient_source'),
    !empty($patient['patient_source_id'])
        ? $patient['patient_source_id']
        : ($enquiry_type[0]['patient_source_id'] ?? ''),
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'required' => 'required'
    ],
    [],
    '',
    'selectpicker'
);
$fields_map['patient_source_id'] = [
    'col'  => 4,
    'html' => add_form_group_class($patient_source_select_html, 'mtop15'),
];

    // Add all other active fields
    foreach ($fields_map as $key => $config) {
        if (!in_array($key, $patient_inactive_fields)) {
            $fields[] = $config;
        }
    }

    if ($total_due == 0) {
        $branch_select = render_select(
            'groupid',
            $branch,
            ['id', 'name'],
            '<span class="text-danger">*</span> ' . _l('branch'),
            isset($current_branch_id) ? $current_branch_id : ($patient['groupid'] ?? ''),
            [
                'id' => 'branch_id',
                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                'required' => 'required'
            ],
            [],
            '',
            'selectpicker'
        );
        $fields[] = [
            'col'  => 4,
            'html' => add_form_group_class($branch_select, 'mtop15'),
        ];
    } else {
        $fields[] = [
            'col'  => 12,
            'html' => '<div class="alert alert-info mtop15">' . _l('note') . ': Branch change option will be visible only when there are no dues.</div>',
        ];
    }

    echo '<div class="row">';
    $currentWidth = 0;
    foreach ($fields as $field) {
        $col = $field['col'] ?? 4;
        $col = (int) max(1, min(12, $col));
        if ($currentWidth + $col > 12) {
            echo '</div><div class="row">';
            $currentWidth = 0;
        }
        echo '<div class="col-md-' . $col . ' col-sm-12">' . $field['html'] . '</div>';
        $currentWidth += $col;
    }
    echo '</div>';

    $selected_treatment_id = $appointment_data['treatment_id'] ?? '';
    $selected_treatment_label = '';
    if (!empty($appointment_data)) {
        $selected_treatment_label = $appointment_data['description'] ?? ($appointment_data['treatment_name'] ?? '');
    }
    $selected_treatment_label = is_string($selected_treatment_label) ? trim($selected_treatment_label) : '';
    $treatments_available = !empty($items) && is_array($items);
    ?>

    <?php if ($treatments_available): ?>
        <div class="row" style="padding: 15px">
            <div class="col-md-4 col-sm-12">
                <div class="form-group mtop15">
                    <label class="form-label"><?= _l('treatment_name'); ?></label>
                    <?php if (isset($appointment_data['appointment_id'])): ?>
                        <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment_data['appointment_id']); ?>">
                    <?php endif; ?>
                    <select
                        name="treatment_id"
                        class="form-control selectpicker"
                        data-live-search="true"
                        data-none-selected-text="<?= htmlspecialchars(_l('dropdown_non_selected_tex')); ?>"
                    >
                        <option value=""><?= htmlspecialchars(_l('dropdown_non_selected_tex')); ?></option>
                        <?php
                        $treatment_selected_in_options = false;
                        foreach ($items as $group_id => $_items) {
                            if (empty($_items)) {
                                continue;
                            }
                            $group_name = $_items[0]['group_name'] ?? '';
                            if (strcasecmp((string) $group_name, 'Package') !== 0) {
                                continue;
                            }
                            foreach ($_items as $item) {
                                $item_id = $item['id'] ?? '';
                                $item_label = $item['description'] ?? '';
                                if ($item_id === '') {
                                    continue;
                                }
                                $is_selected = (string) $selected_treatment_id === (string) $item_id;
                                if ($is_selected) {
                                    $treatment_selected_in_options = true;
                                }
                                ?>
                                <option value="<?= htmlspecialchars($item_id); ?>" <?= $is_selected ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($item_label); ?>
                                </option>
                                <?php
                            }
                        }
                        if ($selected_treatment_id !== '' && !$treatment_selected_in_options && $selected_treatment_label !== '') {
                            ?>
                            <option value="<?= htmlspecialchars($selected_treatment_id); ?>" selected>
                                <?= htmlspecialchars($selected_treatment_label); ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    <?php endif; ?>
	
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('ageDobSelector');
    const input = document.getElementById('ageDobInput');
    const hiddenAge = document.getElementById('hiddenAge');
    const hiddenDob = document.getElementById('hiddenDob');

    if (!selector || !input || !hiddenAge || !hiddenDob) {
      return;
    }

    // Detect initially selected type
    function initializeInput() {
      if (selector.value === 'dob') {
        input.type = 'date';
        input.value = hiddenDob.value;
        input.max = new Date().toISOString().split('T')[0];
      } else {
        input.type = 'text';
        input.value = hiddenAge.value;
        input.placeholder = 'Enter Age';
        input.removeAttribute('max');
      }
    }

    // On selector change (dob <-> age)
    selector.addEventListener('change', function () {
      // Save current input value to hidden field before switching
      if (input.type === 'date') {
        hiddenDob.value = input.value;
      } else {
        hiddenAge.value = input.value;
      }

      // Switch input type
      if (this.value === 'dob') {
        input.type = 'date';
        input.value = hiddenDob.value;
        input.max = new Date().toISOString().split('T')[0];
        input.placeholder = '';
      } else {
        input.type = 'text';
        input.value = hiddenAge.value;
        input.placeholder = 'Enter Age';
        input.removeAttribute('max');
      }
    });

    // On input change, always update hidden field
    input.addEventListener('input', function () {
      if (selector.value === 'dob') {
        hiddenDob.value = this.value;
      } else {
        hiddenAge.value = this.value;
      }
    });

    initializeInput(); // initial render
  });
</script>


              </div>
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                <a href="<?= admin_url('client/get_patient_list'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
$(document).ready(function () {
  $('#state').on('change', function () {
    let stateId = $(this).val();
    if (stateId) {
      $.ajax({
        url: admin_url + 'client/get_cities_by_state/' + stateId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          let cityOptions = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';
          $.each(data, function (i, city) {
            cityOptions += '<option value="' + city.city_id + '">' + city.city_name + '</option>';
          });
          $('#city').html(cityOptions);
          $('#city').selectpicker('refresh'); // Required for Bootstrap select
        },
        error: function () {
          alert('Failed to fetch cities');
        }
      });
    } else {
      $('#city').html('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>').selectpicker('refresh');
    }
  });
});
</script>
<script>
$(document).ready(function () {
  $('#city').on('change', function () {
    let cityId = $(this).val();
    if (cityId) {
      $.ajax({
        url: admin_url + 'client/get_pincodes_by_city/' + cityId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          let pincodeOptions = '<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>';
          $.each(data, function (i, pin) {
            pincodeOptions += '<option value="' + pin.pincode_id + '">' + pin.pincode_name + '</option>';
          });
          $('#pincode').html(pincodeOptions).selectpicker('refresh');
        },
        error: function () {
          alert('Failed to fetch pincodes');
        }
      });
    } else {
      $('#pincode').html('<option value=""><?= _l('dropdown_non_selected_tex'); ?></option>').selectpicker('refresh');
    }
  });
});
</script>

</body>
</html>
