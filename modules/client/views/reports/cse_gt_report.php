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

<!-- Filter Form -->
<div class="row">
<form method="post" id="unitGTForm">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

   

    <div class="col-md-3">
        <label><?php echo _l('from_date'); ?></label>
        <input class="form-control" type="date" id="consulted_date" name="consulted_date" value="<?= html_escape(set_value('consulted_date') ?: date('Y-m-d')) ?>">
    </div>

    <div class="col-md-3">
        <label><?php echo _l('to_date'); ?></label>
        <input class="form-control" type="date" id="consulted_to_date" name="consulted_to_date" value="<?= html_escape(set_value('consulted_to_date') ?: date('Y-m-d')) ?>">
    </div>

    <div class="col-md-2">
        <br>
        <button type="submit" class="btn btn-success" style="margin-top: 5px;" id="searchAppointmentsBtn">Submit</button>
    </div>
</form>
</div>

<br>

<!-- Table -->
<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Define columns with labels
$columns = [
    _l('branch'),
    
    // Current
    'Current GT',
    'Current Consult Fee',

    // Grand Total
    'GT',
    'PROG',

    // Unit Visit
    'Unit ENQ',
    'Unit REF',
    'Unit Visit Count',

    // Unit Registration
	'Unit Reg Count',
    'Unit Reg GT',
    

    // Unit Grand Total
    'Unit GT GT',
    'Unit GT Consult Fee',
    'Unit GT Count',

];

// Render the table
echo render_datatable($columns, 'unit-gt-report'); // .table-unit-gt-report
?>

</div>
</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<script>
$(function () {
    function reloadUnitGTReport() {
        const from = $('#consulted_date').val();
        const to = $('#consulted_to_date').val();
        const branch = $('#branch').val();

        const url = '<?= admin_url("client/reports/$type/1/") ?>' + from + '/' + to;

        if ($.fn.DataTable.isDataTable('.table-unit-gt-report')) {
            $('.table-unit-gt-report').DataTable().ajax.url(url).load();
        } else {
            initDataTable('.table-unit-gt-report', url, [0], [0]);
        }
    }

    // Initial load
    reloadUnitGTReport();

    // On form submit
    $('#unitGTForm').on('submit', function (e) {
        e.preventDefault();
        reloadUnitGTReport();
    });
});

</script>

</body>
</html>
