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
                        <h4 class="no-margin"><?= _l($title); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>

                        <div class="row">
                            <div class="col-md-12">
                                <form method="post" id="filterForm">
                                    <?php
                                        $posted_date = $this->input->post('consulted_date');
                                        $default_date = date('Y-m-d');
                                        $consulted_date_value = $posted_date ?: $default_date;
										
										if($type != "medicine_calling"){
                                    ?>

                                    <div class="form-group">
                                        <label><strong>Filter By:</strong></label><br>
                                        <label><input type="radio" name="date_filter" value="missed_calling"> Missed Calling</label>&nbsp;&nbsp;
                                        <label><input type="radio" name="date_filter" value="next_calling"> Next Calling</label>&nbsp;&nbsp;
                                        <label><input type="radio" name="date_filter" value="last_calling"> Last Calling</label>&nbsp;&nbsp;
										<?php
										if($type == "renewal_calling"){
										?>
                                        <label><input type="radio" name="date_filter" value="registration_end_date"> Registration End Date</label>
										<?php
										}else{
											?>
											<label><input type="radio" name="date_filter" value="registration_date"> Registration Date</label>
											<?php
										}
										?>
                                    </div>
									<?php
										}else{
											echo "<h5>&emsp;Medicine Followup</h5>";
										}
										?>
                                    <div class="form-row">
									<div class="col-md-3">
									<?php
									$selected_branches = $this->input->post('branch') ? $this->input->post('branch') : (isset($branch_id) ? [$branch_id] : []);
									
									echo render_select(
										'branch[]', // Note the [] for multi-select
										$branch,
										['id', ['name']],
										'<span style="color:red;">*</span> '._l('lead_branch'),
										$selected_branches,
										[
											'data-none-selected-text' => _l('dropdown_non_selected_tex'),
											'multiple' => true,
											'data-actions-box' => true,
											'required' => 'required'
										]
									);
									?>
								</div>
                                        <div class="form-group col-md-3">
                                            <label>From Date</label>
                                            <input class="form-control" type="date" id="from_date" name="from_date" value="<?= html_escape($consulted_date_value) ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>To Date</label>
                                            <input class="form-control" type="date" id="to_date" name="to_date" value="<?= html_escape($consulted_date_value) ?>">
                                        </div>
                                        <div class="form-group col-md-2" style="margin-top: 24px;">
                                            <button type="submit" class="btn btn-success">Get Details</button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                        value="<?= $this->security->get_csrf_hash(); ?>" />
                                </form>
                            </div>
                        </div>

                        <?php echo render_datatable([
                            _l('s_no'),
                            _l('patient_name'),
                            _l('mr_no'),
                            _l('patient_mobile'),
                            _l('appointment_date'),
                            _l('consulted_date'),
                            _l('registration_end_date'),
                            _l('next_calling_date'),
                            _l('last_calling_date'),
                            _l('missed_calling'),
                            _l('doctor'),
                            _l('called_by')
                        ], 'calling'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail();


 ?>
<?php if (isset($client_modal)) echo $client_modal; ?>
<script>
$(function () {
	<?php if (isset($clientid) && $clientid): ?>
        // If clientid is set, show patient modal popup
        //$('#client-model-auto').modal('show');
		$('#client-model-auto').modal({
			backdrop: 'static',  // disables click outside to close
			keyboard: false      // disables ESC key to close
		});
    <?php endif; ?>
    function loadCallingTable(filter, from_date, to_date, branchIds) {
        // Destroy previous table
        $('.table-calling').DataTable().destroy();

        // New URL
        const url = '<?= admin_url("client/calling/$type/null/") ?>' + filter + '/' + from_date + '/' + to_date + '/' + branchIds;

        // Reload table
        initDataTable('.table-calling', url, [1], [1]);
    }

    // On form submit
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();

        const filter = $('input[name="date_filter"]:checked').val();
        const from_date = $('#from_date').val();
        const to_date = $('#to_date').val();
		
		 // Get selected branches
    let branchSelect = $('select[name="branch[]"]');
    let branchIds = branchSelect.val() && branchSelect.val().length > 0 ? 
                   encodeURIComponent(branchSelect.val().join(',')) : 
                   'null';

        loadCallingTable(filter, from_date, to_date, branchIds);
    });

    // Initial load
    const init_filter = $('input[name="date_filter"]:checked').val();
    const init_from = $('#from_date').val();
    const init_to = $('#to_date').val();

    loadCallingTable(init_filter, init_from, init_to);
});
</script>
</body>
</html>
