<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$CI   = &get_instance();

if (!isset($slug)) {
    $slug = $CI->uri->segment(3); // e.g., 'city'
}

if (!isset($title)) {
    $title = $CI->uri->segment(3); // e.g., 'city'
}

if (!isset($field_name)) {
    $field_name = $CI->uri->segment(3); // e.g., 'city_name'
}

?>
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
<h4 class="no-margin">
<?php echo _l($title); ?>
<?php if (staff_can('create', 'customers')): ?>
    <a class="btn btn-info mbot30 pull-right" data-toggle="modal" data-target="#myModal">
    Add <?php echo $title; ?>
    </a>
<?php endif; ?>
</h4>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Name</th>
<?php if ($slug == 'medicine') { echo '<th>Medicine Code</th>'; } ?>

<?php if ($slug == 'city' || $slug == 'pincode') { echo '<th>State</th>'; } ?>
<?php if ($slug == 'pincode') { echo '<th>City</th>'; } ?>
<?php if ($slug == 'treatment_sub_type') { echo '<th>Treatment</th>'; echo '<th>Price</th>'; } ?>
<th><?php echo _l('Action'); ?></th>
</tr>
</thead>
<tbody>
<?php if (!empty($records)) {
    foreach ($records as $r) {
        $id_column = $slug . '_id'; // e.g., city_id
?>
<tr>
    <td><?php echo $r[$id_column]; ?></td>
    <td><?php echo $r[$field_name]; ?></td>
	<?php if ($slug == 'medicine') { echo '<td>' . $r['medicine_code'] . '</td>'; } ?>

    <?php if ($slug == 'city' || $slug == 'pincode') { echo '<td>' . $r['state_name'] . '</td>'; } ?>
    <?php if ($slug == 'pincode') { echo '<td>' . $r['city_name'] . '</td>'; } ?>
    <?php if ($slug == 'treatment_sub_type') { echo '<td>' . $r['treatment_type_name'] . '</td>'; echo '<td>' . $r['treatment_sub_type_price'] . '</td>'; } ?>
    <td>
        <?php if (staff_can('edit', $slug)): ?>
            <a href="javascript:void(0);" onclick="editRecord(<?php echo $r[$id_column]; ?>)" class="btn btn-sm btn-primary">Edit</a>
        <?php endif; ?>
        <?php if (staff_can('delete', $slug)): ?>
            <a href="<?php echo admin_url('master/delete/' . $slug . '/' . $r[$id_column]); ?>" class="btn btn-sm btn-danger _delete">Delete</a>
        <?php endif; ?>
    </td>
</tr>
<?php }
} else { ?>
<tr>
    <td colspan="3">No records found.</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<!-- Add Modal -->
<?php if (staff_can('create', $slug)): ?>
<div id="myModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form action="<?php echo admin_url('master/' . $slug); ?>" method="POST">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Add <?php echo ucfirst(str_replace('_', ' ', $slug)); ?></h4>
</div>
<div class="modal-body">
    <input type="hidden" name="<?= $CI->security->get_csrf_token_name(); ?>" value="<?= $CI->security->get_csrf_hash(); ?>">
	<?php if ($slug == 'medicine') { ?>
		<label for="medicine_code"><b>Medicine Code</b></label>
		<input type="text" id="medicine_code" name="medicine_code" class="form-control">
	<?php } ?>


    <label for="<?php echo $field_name; ?>">
        <b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Name</b></label>
    <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="form-control">
    <?php if ($slug == 'city') { ?>
    <label for="state_id"><b><?= _l('state'); ?></b></label>
    <select name="state_id" class="form-control" required>
        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
        <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id']; ?>"><?= $s['state_name']; ?></option>
        <?php endforeach; ?>
    </select>
    <?php } ?>
	
	<?php if ($slug == 'pincode') { ?>
   <?php
echo render_select(
    'city_id',
    $cities,
    ['city_id', ['city_name']],
    _l('city') . ' *',
    '',
    [
        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
        'required' => 'required',
        'class' => 'form-control selectpicker',
        'data-live-search' => 'true'
    ]
);
?>

    <?php } ?>
	
	<?php if ($slug == 'treatment_sub_type') { ?>
	<br>
    <label for="treatment_type_id"><b><?= _l('treatment_type'); ?></b></label>
    <select name="treatment_type_id" class="form-control" required>
        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
        <?php foreach ($treatment_type as $t): ?>
            <option value="<?= $t['treatment_type_id']; ?>"><?= $t['treatment_type_name']; ?></option>
        <?php endforeach; ?>
    </select>
	<br>
	<label for="<?php echo $field_name; ?>">
        <b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Price</b></label>
	 <input type="text" id="treatment_sub_type_price" name="treatment_sub_type_price" class="form-control">
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <input type="submit" value="Save" class="btn btn-success">
</div>
</form>
</div>
</div>
</div>
<?php endif; ?>

<!-- Edit Modal -->
<?php if (staff_can('edit', $slug)): ?>
<div id="myeditModal" class="modal fade" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<form action="<?php echo admin_url('master/' . $slug); ?>" method="POST">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Edit <?php echo ucfirst(str_replace('_', ' ', $title)); ?></h4>
</div>
<div class="modal-body">
    <input type="hidden" name="<?= $CI->security->get_csrf_token_name(); ?>" value="<?= $CI->security->get_csrf_hash(); ?>">
    <input type="hidden" id="edit_id" name="<?php echo $slug . '_id'; ?>">
	<?php if ($slug == 'medicine') { ?>
		<label for="edit_medicine_code"><b>Medicine Code</b></label>
		<input type="text" id="edit_medicine_code" name="medicine_code" class="form-control">
	<?php } ?>


    <label for="edit_<?php echo $field_name; ?>">
        <b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Name</b></label>
    <input type="text" id="edit_<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="form-control">
    <?php if ($slug == 'city') { ?>
    <label for="edit_state_id"><b><?= _l('state'); ?></b></label>
    <select name="state_id" id="edit_state_id" class="form-control" required>
        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
        <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id']; ?>"><?= $s['state_name']; ?></option>
        <?php endforeach; ?>
    </select>
    <?php } ?>
	
	 <?php if ($slug == 'pincode') { ?>
    <label for="edit_city_id"><b><?= _l('city'); ?></b></label>
	
    <select name="city_id" id="edit_city_id" class="form-control selectpicker" data-live-search="true" required>
        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
        <?php foreach ($cities as $c): ?>
            <option value="<?= $c['city_id']; ?>"><?= $c['city_name']; ?></option>
        <?php endforeach; ?>
    </select>
	
    <?php } ?>
	
	<?php if ($slug == 'treatment_sub_type') { ?><br>
    <label for="treatment_type_id"><b><?= _l('treatment_type'); ?></b></label>
    <select name="treatment_type_id"  id="edit_treatment_type_id" class="form-control" required>
        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
        <?php foreach ($treatment_type as $t): ?>
            <option value="<?= $t['treatment_type_id']; ?>"><?= $t['treatment_type_name']; ?></option>
        <?php endforeach; ?>
    </select>
	<br>
	<label for="<?php echo $field_name; ?>">
        <b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Price</b></label>
	 <input type="text" id="edit_treatment_sub_type_price" name="treatment_sub_type_price" class="form-control">
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <input type="submit" value="Update" class="btn btn-success">
</div>
</form>
</div>
</div>
</div>
<?php endif; ?>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>

<script>
function editRecord(id) {
    $.ajax({
        url: "<?php echo admin_url('master/get_record_by_id/' . $slug); ?>",
        type: "POST",
        data: {
            id: id,
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
        },
        dataType: "json",
        success: function(resp) {
			<?php if ($slug == 'medicine') { ?>
				$('#edit_medicine_code').val(resp['medicine_code']);
			<?php } ?>


            $('#edit_id').val(resp['<?php echo $slug . '_id'; ?>']);
            $('#edit_<?php echo $slug . '_name'; ?>').val(resp['<?php echo $field_name; ?>']);
			 <?php if ($slug == 'city') { ?>
            $('#edit_state_id').val(resp['state_id']);
            <?php } ?>
			<?php if ($slug == 'pincode') { ?>
				$('#edit_city_id').val(resp['city_id']);
				$('#edit_city_id').selectpicker('refresh');
			<?php } ?>
			<?php if ($slug == 'treatment_sub_type') { ?>
            $('#edit_treatment_type_id').val(resp['treatment_type_id']);
            $('#edit_treatment_sub_type_price').val(resp['treatment_sub_type_price']);
            <?php } ?>
            $('#myeditModal').modal('show');
        },
        error: function(xhr) {
            alert('Failed to fetch data.');
            console.error(xhr.responseText);
        }
    });
}

</script>
</body>
</html>