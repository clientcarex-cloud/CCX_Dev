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
        <h4 class="no-margin">
        <?php echo $title; ?> <a  class="btn btn-info mbot30 pull-right" data-toggle="modal" data-target="#myModal">
        Add Medicine </a>
        </h4>
        
        <hr class="hr-panel-heading" />
        <div class="clearfix"></div>
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Medicine Name</th>
            <th><?php echo _l('Action'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($medicines)) {
            foreach ($medicines as $medicine) { ?>
                <tr>
                    <td><?php echo $medicine['id']; ?></td>
                    <td><?php echo $medicine['medicine_name']; ?></td>
                    <td>
                    <a href="javascript:void(0);" onclick="editMedicine(<?php echo $medicine['id']; ?>)" class="btn btn-sm btn-primary">Edit</a>

                    <a href="<?php echo admin_url('medicine/delete/' . $medicine['id']); ?>" class="btn btn-sm btn-danger _delete">Delete</a>
                    </td>
                </tr>
        <?php }
        } else { ?>
            <tr>
                <td colspan="3">No medicines found.</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

        
        </div>
        <!-- Modal -->
        <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content">
        <form action="<?php echo admin_url('medicine/add_medicine');?>" method="POST">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Medicine</h4>
        </div>
        
        <div class="modal-body">
        <input type="hidden" class="txt_csrfname" id="txt_csrfname" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        
        <label for="medicine_name"><b>Medicine Name</b></label>
        <input type="text" id="medicine_name" value="" name="medicine_name" class="form-control">
        
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <input type="submit"  value="save" class="btn btn-success ">
        </div>
        </form>	
        </div>
        
        </div>
        </div>
        <!---Edit Modal--->
           <div id="myeditModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
			<div class="modal-content">
            <form action="<?php echo admin_url('medicine/update_medicine'); ?>" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Medicine</h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" id="edit_id" name="id">

                    <label for="edit_medicine_name"><b>Medicine Name</b></label>
                    <input type="text" id="edit_medicine_name" name="medicine_name" class="form-control">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input type="submit" value="Update" class="btn btn-success">
                </div>
            </form>
			</div>
        </div>
        </div>
        
        </div>
        </div>
        </div>
        </div>
        
        <?php init_tail(); ?>
      
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11">   </script>

        <script>
    function editMedicine(id) {
        //alert('Edit function called with ID: ' + id); // Debugging

        $.ajax({
            url: "<?php echo admin_url('medicine/get_medicine_by_id'); ?>",
            type: "POST",
            data: {
                id: id,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: "json",
            success: function(resp) {
                $('#edit_id').val(resp.id);
                $('#edit_medicine_name').val(resp.medicine_name);
                $('#myeditModal').modal('show');
            },
            error: function() {
                alert('Failed to fetch medicine data.');
            }
        });
    }

    
</script>



        <script>
       
        <?php if(isset($_SESSION['add_medicine']) && $_SESSION['add_medicine'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'New Medicine Added!',
            showConfirmButton: false,
            timer: 1500,
            customClass: {
                popup: 'small-swal-popup'
            }
        })
        <?php } ?>

        <?php if(isset($_SESSION['edit_medicine']) && $_SESSION['edit_medicine'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Medicine Updated Successfully',
            showConfirmButton: false,
            timer: 1500,
            customClass: {
                popup: 'small-swal-popup'
            }
        })
        <?php } ?>

        <?php if(isset($_SESSION['delete_medicine']) && $_SESSION['delete_medicine'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Medicine Deleted!',
            showConfirmButton: false,
            timer: 1500
        })
        <?php } 

        $this->session->unset_userdata('add_medicine');
        $this->session->unset_userdata('edit_medicine');
        $this->session->unset_userdata('delete_medicine');
        ?>
        </script>
        
        <script>
     

        $(function(){
        initDataTable('.table-template', window.location.href, [1], [1]);
        });
        
        
        </script>
        </body>
        </html>
