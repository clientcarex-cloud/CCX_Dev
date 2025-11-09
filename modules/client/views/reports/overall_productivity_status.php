<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
    <?= _l($type); ?>
</h4>
<hr class="hr-panel-heading" />

<?php
// Build columns
$columns = [
    _l('s_no'),
    _l('status_name'),
    _l('task_count'),
    _l('percentage'),
];

echo render_datatable($columns, 'status_report');
?>

</div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>

<script>
$(function(){
    // Initialize table
    initDataTable('.table-status_report', '<?= admin_url("client/reports/".$type) ?>', [1], [1]);
});
</script>
</body>
</html>
