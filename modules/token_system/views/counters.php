<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  .swal2-popup { font-size: 1.6rem !important; }
  .dataTables_wrapper .top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .dataTables_filter {
    margin-left: auto;
    max-width: 300px;
    width: 100%;
  }
  .dataTables_filter input {
    width: 100% !important;
  }
  .dataTables_length {
    display: none !important;
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
		  <div class="row">
		  <div class="col-md-10">
			<h4 class="no-margin"><?= _l($title); ?></h4>
		  </div>
		  <div class="col-md-2">
		  <div class="mb-2">
               <a href="<?= admin_url('token_system/add_counter'); ?>" class="btn btn-primary me-auto">
                <i class="fa fa-plus"></i> <?= _l('new_counter'); ?>
              </a>
            </div>
		  </div>
	  </div>
            <hr class="hr-panel-heading" />
            <div class="clearfix"></div>
            
            <!-- Datatable -->
           <?= render_datatable([
			  _l('id'),
			  _l('counter_name'),
			  _l('doctor_id'),
			  _l('display_id'),
			  _l('counter_status'),
			  _l('password'),
			  _l('public_url'), // ✅ NEW COLUMN
			  _l('actions')
			], 'counter-table'); ?>


          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
  $(function () {
    initDataTable('.table-counter-table', admin_url + 'token_system/get_counters_ajax', [], [], 'undefined', [0, 'desc']);
  });
</script>
<script>
function copyToClipboardUrl(url, btn) {
  // Create temp input
  const tempInput = document.createElement("input");
  document.body.appendChild(tempInput);
  tempInput.setAttribute("value", url);
  tempInput.select();
  tempInput.setSelectionRange(0, 99999); // For mobile
  document.execCommand("copy");
  document.body.removeChild(tempInput);

  // Change button text temporarily
  btn.innerText = "Copied!";
  setTimeout(() => { btn.innerText = "Copy URL"; }, 1500);
}
</script>
