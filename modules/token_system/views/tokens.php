<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  .swal2-popup {
    font-size: 1.6rem !important;
  }

  .length-select-wrapper {
    display: flex;
    align-items: center;
  }

  .dt-buttons .btn {
    margin-right: 5px;
    margin-bottom: 0;
  }

  /* Align the new display button and search box on one line */
  .dataTables_wrapper .top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap;
    margin-bottom: 1rem;
  }

  .dataTables_filter {
    margin-left: auto;
    max-width: 300px;
    width: 100%;
  }

  .dataTables_filter input {
    width: 100% !important;
    max-width: 100% !important;
    display: inline-block;
  }

  /* Hide the length (pagination) dropdown completely */
  .dataTables_length {
    display: none !important;
  }

  .dataTables_filter label {
    font-weight: normal;
    white-space: nowrap;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">

            <h4 class="no-margin"><?php echo _l($title); ?></h4>
            <hr class="hr-panel-heading" />
            <div class="clearfix"></div>
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
              <a href="<?= admin_url('token_system/add_token'); ?>" class="btn btn-primary me-auto">
                <i class="fa fa-plus"></i> <?= _l('add_token'); ?>
              </a>
            </div>
            <table id="token-table" class="table table-striped dt-table">
              <thead>
                <tr>
                  <th><?php echo _l('ID'); ?></th>
                  <th><?php echo _l('token_number'); ?></th>
                  <th><?php echo _l('patient_name'); ?></th>
                  <th><?php echo _l('doctor_name'); ?></th>
                  <th><?php echo _l('date'); ?></th>
                  <th><?php echo _l('token_status'); ?></th>
                  <!--<th><?php echo _l('Actions'); ?></th>-->
                </tr>
              </thead>
              <tbody>
                <?php 
				if (!empty($tokens)) {
                  foreach ($tokens as $token) { ?>
                    <tr>
                      <td><?php echo $token['token_id']; ?></td>
                      <td><?php echo e($token['token_number']); ?></td>
                      <td><?php echo e($token['patient_name']); ?></td>
                      <td><?php echo e($token['doctor_name']); ?></td>
                      <td><?php echo _d($token['date']); ?></td>
                      <td><?php echo e($token['token_status']); ?></td>
                      <!--<td>
                        <a target="_blank" href="<?= admin_url('token_system/create_public_view/' . $counter['counter_id']); ?>" class="btn btn-sm btn-success" style="color: #fff">View</a>
                        <a href="<?= admin_url('token_system/edit_counter/' . $counter['counter_id']); ?>" class="btn btn-sm btn-primary" style="color: #fff">Edit</a>
                      </td>-->
                    </tr>
                <?php }
                } else { ?>
                  <tr>
                    <td colspan="7"><?php echo _l('no_records_found'); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function () {
    if (!$.fn.DataTable.isDataTable('#token-table')) {
      $('#token-table').DataTable({
        dom:
          "<'top-row'<'customBtnWrapper'><'dataTables_filter'>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 10,
        lengthChange: false,  // This removes the dropdown entirely
        responsive: true
      });

      $('.customBtnWrapper').html(`
        <a href="<?= admin_url('token_system/add_counter'); ?>" class="btn btn-primary">
          <i class="fa fa-plus"></i> <?= _l('new_counter'); ?>
        </a>
      `);
    }
  });
</script>
