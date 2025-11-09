<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
    .form-section {
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 6px;
    }
    .form-heading {
        !text-align: center;
        !border-bottom: 1px solid #6a1b9a;
        font-size: 22px;
        !color: #6a1b9a;
        font-weight: 600;
    }
    .section-title {
        display: flex;
        !align-items: center;
        !justify-content: center;
        font-weight: bold;
        font-size: 20px;
        !color: #6a1b9a;
        margin: 5px 0 20px;
    }
    
    .btn-purple {
        background-color: #6a1b9a;
        color: white;
    }
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<div class="clearfix"></div>


<div class="form-section">

<div class="form-heading">Doctor Profile</div>
<hr>
<form method="post" enctype="multipart/form-data" action="<?= admin_url('client/doctor/save_doctor'); ?>">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <div class="row">
        <div class="col-md-4">
                <label class="form-label"><?php echo _l('full_name_and_gender'); ?>*</label>
                <div class="mb-3" style="display: flex; gap: 0;">
                    <div style="flex: 1; max-width: 22%;">
                        <select class="form-control" name="salutation">
						<option value="Master">Master</option>
						<option value="Baby">Baby</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Ms.">Ms.</option>
                        </select>
                    </div>
                    <div style="flex: 1; max-width: 49%;">
                        <input type="text" class="form-control" name="firstname" placeholder="<?php echo _l('enter_patient_name'); ?>" required>
                    </div>
                    <div style="flex: 1; max-width: 29%;">
                        <select class="form-control" name="gender">
                            <option value="Male"><?php echo _l('male'); ?></option>
                            <option value="Female"><?php echo _l('female'); ?></option>
                            <option value="Other"><?php echo _l('other'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        <div class="col-md-4">
            <label><?php echo _l('mobile_number'); ?>*</label>
            <input type="text" class="form-control" name="phonenumber" placeholder="<?php echo _l('enter_number'); ?>" required>
        </div>
        <div class="col-md-4">
            <label><?php echo _l('email'); ?></label>
            <input type="email" class="form-control" name="email" placeholder="<?php echo _l('enter_email_address'); ?>">
        </div>
    </div>
    <br>
    <div class="row mt-3">
        <div class="col-md-4">
            <label><?php echo _l('qualification'); ?></label>
            <input type="text" class="form-control" name="qualification" placeholder="<?php echo _l('enter_qualification'); ?>">
        </div>
        <div class="col-md-4">
            <label><?php echo _l('signature'); ?></label>
            <input type="file" class="form-control" name="signature">
        </div>
        <div class="col-md-4">
            <label><?php echo _l('location'); ?>*</label>
            <input type="text" class="form-control" name="location" placeholder="<?php echo _l('enter_location'); ?>" required>
        </div>
    </div>
	<br>
<div class="section-title"><?php echo _l('consultation_details'); ?></div>
<hr>

<div id="consultation-slots">
    <div class="row slot-row mb-2">
        <div class="col-md-2">
            <label><?php echo _l('day'); ?> *</label>
            <select class="form-control" name="slots[0][day]" required>
                <option value="Mon"><?php echo _l('mon'); ?></option>
                <option value="Tue"><?php echo _l('tue'); ?></option>
                <option value="Wed"><?php echo _l('wed'); ?></option>
                <option value="Thu"><?php echo _l('thu'); ?></option>
                <option value="Fri"><?php echo _l('fri'); ?></option>
                <option value="Sat"><?php echo _l('sat'); ?></option>
            </select>
        </div>
        <div class="col-md-3">
            <label><?php echo _l('shift_start_time'); ?> *</label>
            <input type="time" class="form-control" name="slots[0][shift_start_time]" required>
        </div>
        <div class="col-md-3">
            <label><?php echo _l('shift_end_time'); ?> *</label>
            <input type="time" class="form-control" name="slots[0][shift_end_time]" required>
        </div>
        <div class="col-md-3">
            <label><?php echo _l('avg_session_time'); ?> (<?php echo _l('mins'); ?>) *</label>
            <input type="number" class="form-control" name="slots[0][avg_session_time]" placeholder="<?php echo _l('duration'); ?>" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-sm" id="add-slot">+</button>
        </div>
    </div>
</div>

	<br>
    <div class="section-title"><?php echo _l('job_details'); ?></div>
	<hr>
    <div class="row">
        <div class="col-md-4">
            <label><?php echo _l('license_number'); ?></label>
            <input type="text" class="form-control" name="licence_number" placeholder="<?php echo _l('enter_license_number'); ?>">
        </div>
        <div class="col-md-4">
            <?php
            $selected = "";
            echo render_select('department', $departments, ['departmentid', ['name']], 'department', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
            ?>
        </div>
        <div class="col-md-4">
        <?php
            $selected = "";
            echo render_select('specialization', $specialization, ['specialization_id', ['specialization_name']], 'specialization', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
            ?>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
        <?php
            $selected = "";
            echo render_select('role', $role, ['roleid', ['name']], 'role', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
            ?>
        </div>
        <div class="col-md-4">
        <?php
            $selected = "";
            echo render_select('branch', $branch, ['id', ['name']], 'branch', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
            ?>
        </div>
        <div class="col-md-4">
        <?php
            $selected = "";
            echo render_select('shift', $shift, ['shift_id', ['shift_name']], 'shift', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
            ?>
        </div>
    </div>
	
	<br>
	<div class="text-center mt-4">
		<button type="submit" class="btn btn-purple"><?php echo _l('save'); ?></button>
		<a href="<?= admin_url('doctor') ?>" class="btn btn-danger"><?php echo _l('cancel'); ?></a>
	</div>
</div>
</form>

</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let slotIndex = 1;

document.getElementById('add-slot').addEventListener('click', function () {
    const slotContainer = document.getElementById('consultation-slots');
    const newSlot = document.createElement('div');
    newSlot.className = 'row slot-row mb-2';
    newSlot.innerHTML = `
        <div class="col-md-2" style="margin-top: 5px;">
            <select class="form-control" name="slots[${slotIndex}][day]" required>
                <option value="Mon">Mon</option>
                <option value="Tue">Tue</option>
                <option value="Wed">Wed</option>
                <option value="Thu">Thu</option>
                <option value="Fri">Fri</option>
                <option value="Sat">Sat</option>
            </select>
        </div>
        <div class="col-md-3" style="margin-top: 5px;">
            <input type="time" class="form-control" name="slots[${slotIndex}][shift_start_time]" required>
        </div>
        <div class="col-md-3" style="margin-top: 5px;">
            <input type="time" class="form-control" name="slots[${slotIndex}][shift_end_time]" required>
        </div>
        <div class="col-md-3" style="margin-top: 5px;">
            <input type="number" class="form-control" name="slots[${slotIndex}][avg_session_time]" placeholder="Duration" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-sm remove-slot">-</button>
        </div>
    `;
    slotContainer.appendChild(newSlot);
    slotIndex++;
});

document.getElementById('consultation-slots').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-slot')) {
        e.target.closest('.slot-row').remove();
    }
});
</script>
</body>
</html>
