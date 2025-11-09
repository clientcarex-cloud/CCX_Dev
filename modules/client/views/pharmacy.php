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
<div class="row">
<form method="post" action="<?= admin_url('client/pharmacy'); ?>">
  <div class="row">
    <div class="col-md-3">
      <?php
      $consulted_date_value = $this->input->post('consulted_date') ?: date('Y-m-d');
      ?>
      <label><?php echo _l('from_date');?></label>
      <input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape($consulted_date_value) ?>">
    </div>

    <div class="col-md-3">
      <?php
      $consulted_to_date_value = $this->input->post('consulted_to_date') ?: date('Y-m-d');
      ?>
      <label><?php echo _l('to_date');?></label>
      <input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape($consulted_to_date_value) ?>">
    </div>

    <?php if (staff_can('branch_filter', 'customers')) { ?>
    <div class="col-md-3">
      <?= render_select(
        'groupid',
        $branch,
        ['id', 'name'],
        _l('branch') . '*',
        $this->input->post('groupid') !== null ? $this->input->post('groupid') : ($current_branch_id ?? ''),
        [
          'id' => 'branch_id',
          'data-none-selected-text' => _l('dropdown_non_selected_tex'),
          'required' => 'required'
        ]
      ) ?>
    </div>
    <?php } ?>

    <div class="col-md-2">
      <br>
      <input type="submit" class="btn btn-success" name="Submit" value="Search">
    </div>

    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
  </div>
</form>

<br>
<?php echo render_datatable([
    _l('patient_name'),
	_l('mr_no'),
    _l('casesheet_created_date'),
    _l('casesheet_created_by'),
    _l('medicine_given'),
    _l('medicine_given_by'),
], 'pharmacy'); ?>
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
	var consulted_date = $('#consulted_date').val();
	var consulted_to_date = $('#consulted_to_date').val();
	var branch_id = $('#groupid').val();
    initDataTable('.table-pharmacy', '<?= admin_url("client/pharmacy/1/") ?>' + consulted_date + '/' + consulted_to_date + '/' + branch_id, [1], [1]);
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