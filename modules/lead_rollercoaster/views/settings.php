<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><?php echo _l('lead_rollercoaster'); ?></h4>
                <hr class="hr-panel-heading" />
                <form method="post">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

                    <div class="form-group">
                        <label class="form-label d-block"><?php echo _l('toggle_to_activate'); ?></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" value="1"
                                <?= (isset($settings->is_enabled) && $settings->is_enabled == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_enabled"><?php echo _l('enable_lead_auto_assignment'); ?></label>
                        </div>
                    </div>

                    <?php
                    echo render_select(
                        'selected_roles[]',
                        $roles,
                        ['name', 'name'],
                        'select_user_roles',
                        (isset($settings->selected_roles) ? json_decode($settings->selected_roles) : []),
                        ['multiple' => true, 'data-actions-box' => true, 'data-live-search' => true]
                    );
                    ?>

                    <?php
                    echo render_select(
                        'strategy',
                        [['id' => 'round_robin', 'name' => _l('strategy_round_robin')]],
                        ['id', 'name'],
                        'assigning_strategy',
                        $settings->strategy ?? ''
                    );
                    ?>

                    <?php
                    echo render_select(
                        'fallback_option',
                        [
                            ['id' => 'last_logged', 'name' => _l('fallback_last_logged')],
                            ['id' => 'reporting_manager', 'name' => _l('fallback_reporting_manager')],
                            ['id' => 'manager_and_public', 'name' => _l('fallback_manager_and_public')],
                            ['id' => 'specific_user', 'name' => _l('fallback_specific_user')],
                            ['id' => 'business_timing_then_manager', 'name' => _l('fallback_business_timing_then_manager')],
                        ],
                        ['id', 'name'],
                        'if_no_active_logged_users',
                        $settings->fallback_option ?? '',
                        ['id' => 'fallback_option']
                    );
                    ?>

                    <div class="form-group" id="employee_fallback" style="display: none;">
                        <?php
                        echo render_select(
                            'fallback_employee_id[]',
                            $employees,
                            ['staffid', ['firstname', 'lastname']],
                            'select_employee',
                            (isset($settings->fallback_employee_id) ? json_decode($settings->fallback_employee_id) : []),
                            ['multiple' => true, 'data-actions-box' => true, 'data-live-search' => true]
                        );
                        ?>
                    </div>

                    <div class="row" id="business_timings" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo _l('business_timing_from'); ?></label>
                                <input type="time" name="business_timing_from" class="form-control" value="<?= $settings->business_timing_from ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo _l('business_timing_to'); ?></label>
                                <input type="time" name="business_timing_to" class="form-control" value="<?= $settings->business_timing_to ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <?php
                    echo render_select(
                        'selected_sources[]',
                        $sources,
                        ['id', 'name'],
                        'select_sources_of_leads',
                        (isset($settings->selected_sources) ? json_decode($settings->selected_sources) : []),
                        ['multiple' => true, 'data-actions-box' => true, 'data-live-search' => true]
                    );
                    ?>
					<hr class="hr-panel-heading" />

<div class="form-group">
    <label class="form-label d-block"><?php echo _l('avoid_empty_leads'); ?></label>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="avoid_empty_leads" name="avoid_empty_leads" value="1"
            <?= (isset($settings->avoid_empty_leads) && $settings->avoid_empty_leads == 1) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="avoid_empty_leads"><?php echo _l('avoid_empty_leads_label'); ?></label>
    </div>
</div>

<div class="form-group">
    <label class="form-label d-block"><?php echo _l('auto_convert_to_junk_leads'); ?></label>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="auto_junk_leads" name="auto_junk_leads" value="1"
            <?= (isset($settings->auto_junk_leads) && $settings->auto_junk_leads == 1) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="auto_junk_leads"><?php echo _l('enable_auto_junk_logic'); ?></label>
    </div>
</div>

<div id="junk_rules_section" style="display: none;">
    <label><?php echo _l('junk_lead_rules'); ?></label>
    <table class="table table-bordered" id="junk-rules-table">
        <thead>
            <tr>
                <th><?php echo _l('country_code'); ?></th>
                <th><?php echo _l('expected_length'); ?></th>
                <!--<th><button type="button" class="btn btn-sm btn-success" id="add-junk-rule"><i class="fa fa-plus"></i></button></th>-->
            </tr>
        </thead>
        <tbody>
            <?php
				$countries = get_all_countries();
				$junk_rules = isset($settings->junk_lead_rules) ? json_decode($settings->junk_lead_rules, true) : [];

				if (empty($junk_rules)) {
					$junk_rules = [['country_code' => '', 'expected_length' => '']];
				}

				foreach ($junk_rules as $index => $rule) {
					$selected_code = $rule['country_code'] ?? '';
					$expected_length = $rule['expected_length'] ?? '';
					?>
					<tr>
						<td>
							<select name="junk_lead_rules[<?= $index ?>][country_code]" class="form-control selectpicker country-code-select" data-live-search="true" required>
								<option value=""><?= _l('select_country_code'); ?></option>
								<?php foreach ($countries as $country): ?>
									<?php $code = '+' . $country['calling_code']; ?>
									<option value="<?= $code ?>" <?= $selected_code == $code ? 'selected' : '' ?>>
										<?= $code ?> (<?= $country['short_name'] ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="number" name="junk_lead_rules[<?= $index ?>][expected_length]" class="form-control" placeholder="10" value="<?= htmlspecialchars($expected_length) ?>"></td>
						<!--<td><button type="button" class="btn btn-sm btn-danger remove-junk-rule"><i class="fa fa-trash"></i></button></td>-->
					</tr>
				<?php } ?>


        </tbody>
    </table>
</div>


                    <button type="submit" class="btn btn-primary"><?php echo _l('save_settings'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function () {
    // Initialize fallback & junk toggle on page load
    function toggleFallbackFields() {
        var selected = $('#fallback_option').val();
        $('#employee_fallback').hide();
        $('#business_timings').hide();

        if (['reporting_manager', 'specific_user', 'manager_and_public'].includes(selected)) {
            $('#employee_fallback').show();
        }

        if (selected === 'business_timing_then_manager') {
            $('#employee_fallback').show();
            $('#business_timings').show();
        }

        $('.selectpicker').selectpicker('refresh');
    }

    $('#fallback_option').change(toggleFallbackFields);
    toggleFallbackFields();
    $('.selectpicker').selectpicker();

    // Toggle junk section
    function toggleJunkRulesSection() {
        if ($('#auto_junk_leads').is(':checked')) {
            $('#junk_rules_section').show();
        } else {
            $('#junk_rules_section').hide();
        }
    }

    $('#auto_junk_leads').change(toggleJunkRulesSection);
    toggleJunkRulesSection();

    let junkRuleIndex = $('#junk-rules-table tbody tr').length;

    // Gather selected codes
    function getUsedCountryCodes() {
        let used = [];
        $('.country-code-select').each(function () {
            let val = $(this).val();
            if (val) used.push(val);
        });
        return used;
    }

    // Build a new country code dropdown with options filtered
    function buildCountryCodeSelect(index) {
        let allOptions = <?php echo json_encode(array_map(function($c) {
            return ['code' => '+' . $c['calling_code'], 'name' => $c['short_name']];
        }, get_all_countries())); ?>;

        let used = getUsedCountryCodes();

        let options = '<option value=""><?= _l('select_country_code'); ?></option>';
        allOptions.forEach(opt => {
            if (!used.includes(opt.code)) {
                options += `<option value="${opt.code}">${opt.code} (${opt.name})</option>`;
            }
        });

        return `<select name="junk_lead_rules[${index}][country_code]" class="form-control selectpicker country-code-select" data-live-search="true" required>${options}</select>`;
    }

    // Add new row with filtered dropdown
    $('#add-junk-rule').on('click', function () {
        let newRow = `
            <tr>
                <td>${buildCountryCodeSelect(junkRuleIndex)}</td>
                <td><input type="number" name="junk_lead_rules[${junkRuleIndex}][expected_length]" class="form-control" placeholder="10"></td>
                <!--<td><button type="button" class="btn btn-sm btn-danger remove-junk-rule"><i class="fa fa-trash"></i></button></td>-->
            </tr>
        `;
        $('#junk-rules-table tbody').append(newRow);
        $('.selectpicker').selectpicker('refresh');
        junkRuleIndex++;
    });

    // Remove rule row and refresh dropdowns
    $('#junk-rules-table').on('click', '.remove-junk-rule', function () {
        $(this).closest('tr').remove();
        refreshAllCountryDropdowns();
    });

    // Refresh all dropdowns when values change
    $('#junk-rules-table').on('change', '.country-code-select', function () {
        refreshAllCountryDropdowns();
    });

    function refreshAllCountryDropdowns() {
        let used = getUsedCountryCodes();

        $('.country-code-select').each(function () {
            let current = $(this).val();
            let select = $(this);
            let index = select.attr('name').match(/\[(\d+)\]/)[1];

            // Regenerate options with current selected retained
            let allOptions = <?php echo json_encode(array_map(function($c) {
                return ['code' => '+' . $c['calling_code'], 'name' => $c['short_name']];
            }, get_all_countries())); ?>;

            let html = '<option value=""><?= _l('select_country_code'); ?></option>';
            allOptions.forEach(opt => {
                if (!used.includes(opt.code) || opt.code === current) {
                    html += `<option value="${opt.code}" ${opt.code === current ? 'selected' : ''}>${opt.code} (${opt.name})</option>`;
                }
            });

            select.html(html).selectpicker('refresh');
        });
    }
});
</script>
