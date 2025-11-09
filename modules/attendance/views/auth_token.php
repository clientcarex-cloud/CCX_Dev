<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="no-margin">
    <?= _l('auth_tokens'); ?>
    <?php
    if(count($records) == 0) {
    ?>
    <a class="btn btn-info mbot30 pull-right" data-toggle="modal" data-target="#myModal"><?= _l('add_token'); ?></a>
    <?php
    }
    ?>
</h4>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>

<table class="table table-bordered">
<thead>
<tr>
<th><?= _l('id'); ?></th>
<th><?= _l('auth_token'); ?></th>
<th><?= _l('action'); ?></th>
</tr>
</thead>
<tbody>
<?php if (!empty($records)) {
    foreach ($records as $r) { ?>
<tr>
    <td><?= $r['id']; ?></td>
    <td><?= $r['token']; ?></td>
    <td>
        <a href="javascript:void(0);" onclick="editRecord(<?= $r['id']; ?>)" class="btn btn-sm btn-primary"><?= _l('edit'); ?></a>
        <a href="<?= base_url('attendance/delete/attendance_auth_token/' . $r['id']); ?>" class="btn btn-sm btn-danger _delete"><?= _l('delete'); ?></a>
    </td>
</tr>
<?php }
} else { ?>
<tr><td colspan="3"><?= _l('no_records_found'); ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>

<!-- Add Modal -->
<div id="myModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="<?= base_url('attendance/auth_token'); ?>">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= _l('add_auth_token'); ?></h4>
</div>
<div class="modal-body">
    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <label for="token"><?= _l('token'); ?></label>
    <input type="text" name="token" class="form-control" required>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
    <button type="submit" class="btn btn-success"><?= _l('save'); ?></button>
</div>
</form>
</div>
</div>
</div>

<!-- Edit Modal -->
<div id="myeditModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="<?= base_url('attendance/auth_token'); ?>">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= _l('edit_auth_token'); ?></h4>
</div>
<div class="modal-body">
    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" id="edit_id">
    <label for="edit_token"><?= _l('token'); ?></label>
    <input type="text" name="token" id="edit_token" class="form-control" required>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
    <button type="submit" class="btn btn-success"><?= _l('update'); ?></button>
</div>
</form>
</div>
</div>
</div>

</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<script>
function editRecord(id) {
    $.post("<?= base_url('attendance/get_record_by_id/attendance_auth_token'); ?>", {
        id: id,
        '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }, function(resp) {
        $('#edit_id').val(resp.id);
        $('#edit_token').val(resp.token);
        $('#myeditModal').modal('show');
    }, 'json');
}
</script>
</body>
</html>
