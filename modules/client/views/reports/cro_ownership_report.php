<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    #calendar .fc-event {
        border: none;
        font-weight: 500;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #ddd;
    }
    .table th {
        background-color: #f5f5f5;
    }
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
    <?= _l($title); ?>&emsp;
</h4>

<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<form method="post" action="<?= admin_url('client/reports/cro_ownership_report'); ?>">
<div class="row align-items-end"> <!-- align-items-end for vertical alignment of labels and inputs -->

    <div class="col-md-3">
        <?php
			$selected_branch = isset($branch_id) ? $branch_id : ''; // Pre-select branch if available

			echo render_select(
				'branch',
				$branch,
				['id', ['name']],
				'<span style="color:red;">*</span> '._l('lead_branch'),
				$selected_branch,
				[
					'data-none-selected-text' => _l('dropdown_non_selected_tex'),
					'required' => 'required'
				]
			);
			?>
    </div>

    <div class="col-md-3">
        <?php
            $posted_date = $this->input->post('consulted_date');
            $default_date = date('Y-m-d'); // today's date
            $consulted_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_date" class="control-label"><?= _l('from_date'); ?></label>
        <input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">
    </div>

    <div class="col-md-3">
        <?php
            $posted_date = $this->input->post('consulted_to_date');
            $consulted_to_date_value = $posted_date ? $posted_date : $default_date;
        ?>
        <label for="consulted_to_date" class="control-label"><?= _l('to_date'); ?></label>
        <input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape($consulted_to_date_value) ?>">
    </div>

    <div class="col-md-2">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
		<br>
        <button type="submit" class="btn btn-success" style="width: 100%;margin-top: 5px"><?= _l('search'); ?></button>
    </div>

</div>
</form>
<br>
<div class="table-responsive">
  <table class="table table-bordered" id="branchReportTable">
    <thead>
      <tr>
        <th rowspan="3"><?= _l('branch_name'); ?></th>
        <th colspan="6"><?= _l('trainee'); ?></th>
        <th colspan="6"><?= _l('employee'); ?></th>
        <th colspan="12"><?= _l('total'); ?></th>
      </tr>
      <tr>
        <!-- Trainee Subheaders -->
        <th colspan="3"><?= _l('registration'); ?></th>
        <th rowspan="2"><?= _l('package_amount'); ?></th>
        <th rowspan="2"><?= _l('paid_amount'); ?></th>
        <th rowspan="2"><?= _l('due_amount'); ?></th>
        
        <!-- Employee Subheaders -->
        <th colspan="3"><?= _l('registration'); ?></th>
        <th rowspan="2"><?= _l('package_amount'); ?></th>
        <th rowspan="2"><?= _l('paid_amount'); ?></th>
        <th rowspan="2"><?= _l('due_amount'); ?></th>
        
        <!-- Total Subheaders -->
        <th colspan="3"><?= _l('registration'); ?></th>
        <th rowspan="2"><?= _l('package_amount'); ?></th>
        <th rowspan="2"><?= _l('paid_amount'); ?></th>
        <th rowspan="2"><?= _l('due_amount'); ?></th>
        <th colspan="3"><?= _l('expected'); ?></th>
        <th colspan="3"><?= _l('get_back'); ?></th>
      </tr>
      <tr>
        <!-- Trainee Registration Details -->
        <th><?= _l('total'); ?></th>
        <th><?= _l('active'); ?></th>
        <th><?= _l('inactive'); ?></th>
        
        <!-- Employee Registration Details -->
        <th><?= _l('total'); ?></th>
        <th><?= _l('active'); ?></th>
        <th><?= _l('inactive'); ?></th>
        
        <!-- Total Registration Details -->
        <th><?= _l('total'); ?></th>
        <th><?= _l('active'); ?></th>
        <th><?= _l('inactive'); ?></th>
        
        <!-- Expected Details -->
        <th><?= _l('package_amount'); ?></th>
        <th><?= _l('paid_amount'); ?></th>
        <th><?= _l('due_amount'); ?></th>
        
        <!-- Get Back Details -->
        <th><?= _l('amount'); ?></th>
        <th><?= _l('reg'); ?></th>
        <th><?= _l('back'); ?></th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Data Row (replace with actual PHP loop) -->
      <tr>
        <!-- Trainee Data -->
        <td>Main Branch</td>
        <td>3</td>
        <td>3</td>
        <td>0</td>
        <td>38200</td>
        <td>37300</td>
        <td>900</td>
        
        <!-- Employee Data -->
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        
        <!-- Total Data -->
        <td>3</td>
        <td>3</td>
        <td>0</td>
        <td>38200</td>
        <td>37300</td>
        <td>900</td>
        <td>38200</td>
        <td>37300</td>
        <td>900</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
      </tr>
    </tbody>
  </table>
</div>

</div>
</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<!-- FullCalendar CSS & JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
$(function(){
    /* var consulted_date = $('#consulted_date').val();
    var consulted_to_date = $('#consulted_to_date').val();
    initDataTable('#branchReportTable', '<?= admin_url("client/reports/$type/1/") ?>' + consulted_date + '/' + consulted_to_date, [1], [1]); */
});

// Search Button Click
$('#searchAppointmentsBtn').on('click', function () {
    let from = $('#consulted_date').val();
    let to = $('#consulted_to_date').val();
    if ($.fn.DataTable.isDataTable('#branchReportTable')) {
        $('#branchReportTable').DataTable().ajax.url(
            '<?= admin_url("client/reports/$type/1/") ?>' + from + '/' + to
        ).load();
    }
});
</script>

<?php if (isset($client_modal)) echo $client_modal; ?>

<script>
$(function () {
    <?php if (isset($clientid) && $clientid): ?>
        $('#client-model-auto').modal({
			backdrop: 'static',  // disables click outside to close
			keyboard: false      // disables ESC key to close
		});
    <?php endif; ?>
});
</script>

</body>
</html>