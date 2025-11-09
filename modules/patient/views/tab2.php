<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
        <?php init_head(); ?>
        <style>
            .swal2-popup { font-size: 1.6rem !important; }
        </style>
        <div id="wrapper">
        <div class="content">
        <div class="row">
        <div class="col-md-2">
        <?php $this->load->view('patient_tabs'); ?>
            </div>
        <div class="col-md-10">
        
        <div class="panel_s">
        <div class="panel-body">
        <h4 class="no-margin">
        <?php echo $title; ?> <a  class="btn btn-info mbot30 pull-right" data-toggle="modal" data-target="#myModal">
        Add patient </a>
        </h4>
        
        <hr class="hr-panel-heading" />
        <div class="clearfix"></div>
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>patient Name</th>
            <th><?php echo _l('Action'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($patients)) {
            foreach ($patients as $patient) { ?>
                <tr>
                    <td><?php echo $patient['id']; ?></td>
                    <td><?php echo $patient['patient_name']; ?></td>
                    <td>
                    <a href="javascript:void(0);" onclick="editpatient(<?php echo $patient['id']; ?>)" class="btn btn-sm btn-primary">Edit</a>

                    <a href="<?php echo admin_url('patient/delete/' . $patient['id']); ?>" class="btn btn-sm btn-danger _delete">Delete</a>
                    </td>
                </tr>
        <?php }
        } else { ?>
            <tr>
                <td colspan="3">No patients found.</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

        
        </div>
        <!-- Modal -->
        <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content">
        <form action="<?php echo admin_url('patient/add_patient');?>" method="POST">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add patient</h4>
        </div>
        
        <div class="modal-body">
        <input type="hidden" class="txt_csrfname" id="txt_csrfname" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        
        <label for="patient_name"><b>patient Name</b></label>
        <input type="text" id="patient_name" value="" name="patient_name" class="form-control">
        
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
            <form action="<?php echo admin_url('patient/update_patient'); ?>" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit patient</h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" id="edit_id" name="id">

                    <label for="edit_patient_name"><b>patient Name</b></label>
                    <input type="text" id="edit_patient_name" name="patient_name" class="form-control">
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
    function editpatient(id) {
        //alert('Edit function called with ID: ' + id); // Debugging

        $.ajax({
            url: "<?php echo admin_url('patient/get_patient_by_id'); ?>",
            type: "POST",
            data: {
                id: id,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: "json",
            success: function(resp) {
                $('#edit_id').val(resp.id);
                $('#edit_patient_name').val(resp.patient_name);
                $('#myeditModal').modal('show');
            },
            error: function() {
                alert('Failed to fetch patient data.');
            }
        });
    }

    
</script>



        <script>
       
        <?php if(isset($_SESSION['add_patient']) && $_SESSION['add_patient'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'New patient Added!',
            showConfirmButton: false,
            timer: 1500,
            customClass: {
                popup: 'small-swal-popup'
            }
        })
        <?php } ?>

        <?php if(isset($_SESSION['edit_patient']) && $_SESSION['edit_patient'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'patient Updated Successfully',
            showConfirmButton: false,
            timer: 1500,
            customClass: {
                popup: 'small-swal-popup'
            }
        })
        <?php } ?>

        <?php if(isset($_SESSION['delete_patient']) && $_SESSION['delete_patient'] == 1){ ?>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'patient Deleted!',
            showConfirmButton: false,
            timer: 1500
        })
        <?php } 

        $this->session->unset_userdata('add_patient');
        $this->session->unset_userdata('edit_patient');
        $this->session->unset_userdata('delete_patient');
        ?>
        </script>
        
        <script>
     

        $(function(){
        initDataTable('.table-template', window.location.href, [1], [1]);
        });
        
        
        </script>
        </body>
        </html>
