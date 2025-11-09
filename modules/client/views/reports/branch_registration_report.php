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
<form method="post" action="<?= admin_url('client/reports/branch_visit_report'); ?>">
<div class="row align-items-end"> <!-- align-items-end for vertical alignment of labels and inputs -->

    <!--<div class="col-md-3">
        <?php
            $selected_branch = isset($branch_id) ? $branch_id : ''; // Pre-select branch if available

            echo render_select(
                'branch',
                $branch,
                ['id', 'name'],
                '<span style="color:red;">*</span> '._l('lead_branch'),
                $selected_branch,
                [
                    'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                    'required' => 'required'
                ]
            );
        ?>
    </div>-->

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
        <button type="button" id="searchAppointmentsBtn" class="btn btn-success" style="width: 100%; margin-top: 5px;"><?= _l('search'); ?></button>
    </div>

</div>
</form>
<br>
<?= render_datatable([
    _l('s_no'),
    _l('branch_name'),
    _l('total_registrations'),
    _l('ref_registrations'),
    _l('ref_package_amount'),
    _l('ref_paid_amount'),
    _l('ref_due_amount'),
    _l('walkin_registrations'),
    _l('walkin_package_amount'),
    _l('walkin_paid_amount'),
    _l('walkin_due_amount'),
    _l('renewal_registrations'),
    _l('renewal_package_amount'),
    _l('renewal_paid_amount'),
    _l('renewal_due_amount'),
    _l('cce_registrations'),
    _l('cce_package_amount'),
    _l('cce_paid_amount'),
    _l('cce_due_amount'),
    _l('other_registrations'),
    _l('other_package_amount'),
    _l('other_paid_amount'),
    _l('other_due_amount'),
], 'appointments'); ?>



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
    function loadAppointmentsTable() {
        var consulted_date = $('#consulted_date').val();
        var consulted_to_date = $('#consulted_to_date').val();

        if ($.fn.DataTable.isDataTable('.table-appointments')) {
            $('.table-appointments').DataTable().ajax.url('<?= admin_url("client/reports/$type/1/") ?>' + consulted_date + '/' + consulted_to_date).load();
        } else {
            initDataTable('.table-appointments', '<?= admin_url("client/reports/$type/1/") ?>' + consulted_date + '/' + consulted_to_date, [1], [1]);
        }
    }

    // Load on page load
    loadAppointmentsTable();

    // Search button click
    $('#searchAppointmentsBtn').on('click', function () {
        loadAppointmentsTable();
    });
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
