<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$CI   = &get_instance();

if (!isset($slug)) {
    
    $slug = $CI->uri->segment(3); // e.g., 'enquiry_type' from 'master/enquiry_type'
}

if (!isset($title)) {
    
    $title = $CI->uri->segment(3); // e.g., 'enquiry_type' from 'master/enquiry_type'
}
if (!isset($field_name)) {
    
    $field_name = $CI->uri->segment(3); // e.g., 'enquiry_type' from 'master/enquiry_type'
}

?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
</style>
<style>
  .length-select-wrapper {
    display: flex;
    align-items: center;
  }

  .dt-buttons .btn {
    margin-right: 5px;
    margin-bottom: 0;
  }
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
<table id="my-custom-table">

<thead>
<tr>
<th>ID</th>
<th><?php echo $title; ?> Name</th>
<th><?php echo _l('Action'); ?></th>
</tr>
</thead>
<tbody>
<?php if (!empty($records)) {
    foreach ($records as $r) {
        $id_column = $slug . '_id'; // e.g., enquiry_type_id
?>
<tr>
    <td><?php echo $r[$id_column]; ?></td>
    <td><?php echo $r[$field_name]; ?></td>
    <td>
        <?php if (staff_can('edit', $slug)): ?>
            <a href="javascript:void(0);" onclick="editRecord(<?php echo $r[$id_column]; ?>)" class="btn btn-sm btn-primary">Edit</a>
        <?php endif; ?>
        <?php if (staff_can('delete', $slug)): ?>
            <a href="<?php echo admin_url('client/master_delete/' . $slug . '/' . $r[$id_column]); ?>" class="btn btn-sm btn-danger _delete">Delete</a>
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
<form action="<?php echo admin_url('client/' . $slug); ?>" method="POST">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Add <?php echo ucfirst(str_replace('_', ' ', $slug)); ?></h4>
</div>
<div class="modal-body">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    
    <label for="<?php echo $field_name; ?>"><b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Name</b></label>
    <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="form-control">
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
<form action="<?php echo admin_url('client/' . $slug); ?>" method="POST">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Edit <?php echo ucfirst(str_replace('_', ' ', $title)); ?></h4>
</div>
<div class="modal-body">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" id="edit_id" name="<?php echo $slug . '_id'; ?>">
    <label for="edit_<?php echo $field_name . '_name'; ?>"><b><?php echo ucfirst(str_replace('_', ' ', $title)); ?> Name</b></label>
    <input type="text" id="edit_<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="form-control">
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
        url: "<?php echo admin_url('client/get_record_by_id/' . $slug); ?>",
        type: "POST",
        data: {
            id: id,
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
        },
        dataType: "json",
        success: function(resp) {
            $('#edit_id').val(resp['<?php echo $slug . '_id'; ?>']);
            $('#edit_<?php echo $slug . '_name'; ?>').val(resp['<?php echo $field_name; ?>']);
            $('#myeditModal').modal('show');
        },
        error: function(xhr) {
            alert('Failed to fetch data.');
            console.error(xhr.responseText);
        }
    });
}

</script>
<!-- DataTables core -->
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Buttons extension -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
  $(document).ready(function () {
    $('#my-custom-table').DataTable({
      dom:
        "<'row align-items-center mb-2'" +
          "<'col-md-6 d-flex gap-2'<'length-select-wrapper'l>>" +
          "<'col-md-6 text-right'B>" +
        ">" +
        "<'row'<'col-md-12'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-5'i><'col-md-7'p>>",
      buttons: [
        {
          extend: 'copy',
          text: '<i class="fa fa-copy"></i> Copy',
          className: 'btn btn-primary btn-sm'
        },
        {
          extend: 'csv',
          text: '<i class="fa fa-file-csv"></i> CSV',
          className: 'btn btn-info btn-sm'
        },
        {
          extend: 'excel',
          text: '<i class="fa fa-file-excel"></i> Excel',
          className: 'btn btn-success btn-sm'
        },
        {
          extend: 'pdf',
          text: '<i class="fa fa-file-pdf"></i> PDF',
          className: 'btn btn-danger btn-sm'
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Print',
          className: 'btn btn-secondary btn-sm'
        }
      ]
    });
  });
</script>



</body>
</html>
