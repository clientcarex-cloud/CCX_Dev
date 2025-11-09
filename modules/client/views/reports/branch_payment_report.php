<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?= _l($title); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-4">
                                <label><?= _l('from_date'); ?></label>
                                <input type="date" class="form-control" id="consulted_date" value="<?= html_escape($consulted_date ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label><?= _l('to_date'); ?></label>
                                <input type="date" class="form-control" id="consulted_to_date" value="<?= html_escape($consulted_to_date ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="col-md-2" style="margin-top: 24px;">
                                <button class="btn btn-success" id="searchAppointmentsBtn"><?= _l('search'); ?></button>
                            </div>
                        </div>

                        <br>

                        <?php
                        // Prepare DataTable columns: branch + each payment mode + total
                        $columns = [_l('branch')];
                        foreach ($payment_modes as $mode) {
							if (strtolower($mode['name']) != "free") {
                            $columns[] = _l(strtolower(str_replace(' ', '_', $mode['name'])));
							}
                        }
                        $columns[] = _l('total');

                        echo render_datatable($columns, 'appointments');
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
    function loadBranchPaymentTable() {
        let from = $('#consulted_date').val();
        let to = $('#consulted_to_date').val();

        if ($.fn.DataTable.isDataTable('.table-appointments')) {
            $('.table-appointments').DataTable().ajax.url(
                `<?= admin_url("client/reports/$type/1/") ?>${from}/${to}`
            ).load();
        } else {
            initDataTable('.table-appointments', `<?= admin_url("client/reports/$type/1/") ?>${from}/${to}`, [1], [1]);
        }
    }

    // Initial table load
    loadBranchPaymentTable();

    // On search button click
    $('#searchAppointmentsBtn').on('click', function (e) {
        e.preventDefault();
        loadBranchPaymentTable();
    });
});
</script>
</body>
</html>
