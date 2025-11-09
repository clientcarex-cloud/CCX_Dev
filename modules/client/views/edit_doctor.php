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
        font-size: 22px;
        font-weight: 600;
        color: #6a1b9a;
    }
    .section-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
        color: #6a1b9a;
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
    <div class="form-heading">Edit Doctor Profile</div>
    <hr>
    <form method="post" enctype="multipart/form-data" action="<?= admin_url('client/doctor/update_doctor'); ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
        <input type="hidden" name="doctor_id" value="<?= $doctor->staffid ?? '' ?>" />

        <!-- Full Name & Gender -->
        <div class="row">
            <div class="col-md-4">
                <label class="form-label"><?= _l('full_name_and_gender'); ?>*</label>
                <div class="mb-3" style="display: flex; gap: 0;">
                    <div style="flex: 1; max-width: 22%;">
                        <select class="form-control" name="salutation">
						<option value="Master" <?= ($doctor_new_fields->salutation ?? '') == "Master" ? 'selected' : ''; ?>>Master</option>
						<option value="Baby" <?= ($doctor_new_fields->salutation ?? '') == "Baby" ? 'selected' : ''; ?>>Baby</option>
                            <option value="Mr." <?= ($doctor_new_fields->salutation ?? '') == "Mr." ? 'selected' : ''; ?>>Mr.</option>
                            <option value="Mrs." <?= ($doctor_new_fields->salutation ?? '') == "Mrs." ? 'selected' : ''; ?>>Mrs.</option>
                            <option value="Ms." <?= ($doctor_new_fields->salutation ?? '') == "Ms." ? 'selected' : ''; ?>>Ms.</option>
                        </select>
                    </div>
                    <div style="flex: 1; max-width: 49%;">
                        <input type="text" class="form-control" name="firstname" placeholder="<?= _l('enter_doctor_name'); ?>" value="<?= $doctor->firstname ?? '' ?>" required>
                    </div>
                    <div style="flex: 1; max-width: 29%;">
                        <select class="form-control" name="gender">
                            <option value="Male" <?= ($doctor_new_fields->gender ?? '') == "Male" ? 'selected' : ''; ?>><?= _l('male'); ?></option>
                            <option value="Female" <?= ($doctor_new_fields->gender ?? '') == "Female" ? 'selected' : ''; ?>><?= _l('female'); ?></option>
                            <option value="Other" <?= ($doctor_new_fields->gender ?? '') == "Other" ? 'selected' : ''; ?>><?= _l('other'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Mobile Number -->
            <div class="col-md-4">
                <label><?= _l('mobile_number'); ?>*</label>
                <input type="text" class="form-control" name="phonenumber" placeholder="<?= _l('enter_number'); ?>" value="<?= $doctor->phonenumber ?? '' ?>" required>
            </div>

            <!-- Email -->
            <div class="col-md-4">
                <label><?= _l('email'); ?></label>
                <input type="email" class="form-control" name="email" placeholder="<?= _l('enter_email_address'); ?>" value="<?= $doctor->email ?? '' ?>">
            </div>
        </div>

        <br>
        <div class="row mt-3">
            <!-- Qualification -->
            <div class="col-md-4">
                <label><?= _l('qualification'); ?></label>
                <input type="text" class="form-control" name="qualification" placeholder="<?= _l('enter_qualification'); ?>" value="<?= $doctor_new_fields->qualification ?? '' ?>">
            </div>

            <!-- Signature -->
            <div class="col-md-4">
                <label><?= _l('signature'); ?></label>
                <input type="file" class="form-control" name="signature">
            </div>

            <!-- Location -->
            <div class="col-md-4">
                <label><?= _l('location'); ?>*</label>
                <input type="text" class="form-control" name="location" placeholder="<?= _l('enter_location'); ?>" value="<?= $doctor_new_fields->location ?? '' ?>" required>
            </div>
        </div>

        <div class="section-title"><?= _l('job_details'); ?></div>
        <hr>

        <div class="row">
            <!-- License Number -->
            <div class="col-md-4">
                <label><?= _l('license_number'); ?></label>
                <input type="text" class="form-control" name="licence_number" placeholder="<?= _l('enter_license_number'); ?>" value="<?= $doctor_new_fields->licence_number ?? '' ?>">
            </div>

            <!-- Department -->
            <div class="col-md-4">
                <?php
                $selectedDepartment = $doctor_new_fields->department ?? '';
                echo render_select('department', $departments, ['departmentid', 'name'], 'department', $selectedDepartment, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>
            </div>

            <!-- Specialization -->
            <div class="col-md-4">
                <?php
                $selectedSpecialization = $doctor_new_fields->specialization ?? '';
                echo render_select('specialization', $specialization, ['specialization_id', 'specialization_name'], 'specialization', $selectedSpecialization, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Role -->
            <div class="col-md-4">
                <?php
                $selectedRole = $doctor->role ?? '';
                echo render_select('role', $role, ['roleid', 'name'], 'role', $selectedRole, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>
            </div>

            <!-- Branch -->
            <div class="col-md-4">
                <?php
                $selectedBranch = $doctor_new_fields->branch ?? '';
                echo render_select('branch', $branch, ['id', 'name'], 'branch', $selectedBranch, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>
            </div>

            <!-- Shift -->
            <div class="col-md-4">
                <?php
                $selectedShift = $doctor_new_fields->shift_id ?? '';
                echo render_select('shift', $shift, ['shift_id', 'shift_name'], 'shift', $selectedShift, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                ?>
            </div>
        </div>

        <br>
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-purple"><?= _l('save'); ?></button>
            <a href="<?= admin_url('doctor') ?>" class="btn btn-danger"><?= _l('cancel'); ?></a>
        </div>
    </form>
</div>


</div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let slotIndex = <?= count($doctor_time_slots); ?>;

document.getElementById('add-slot').addEventListener('click', function () {
    const slotContainer = document.getElementById('consultation-slots');
    const newSlot = document.createElement('div');
    newSlot.className = 'row slot-row mb-2';
    newSlot.innerHTML = 
        `<div class="col-md-2">
            <select class="form-control" name="slots[${slotIndex}][day]" required>
                <option value="Mon">Mon</option>
                <option value="Tue">Tue</option>
                <option value="Wed">Wed</option>
                <option value="Thu">Thu</option>
                <option value="Fri">Fri</option>
                <option value="Sat">Sat</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="time" class="form-control" name="slots[${slotIndex}][shift_start_time]" required>
        </div>
        <div class="col-md-3">
            <input type="time" class="form-control" name="slots[${slotIndex}][shift_end_time]" required>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control" name="slots[${slotIndex}][avg_session_time]" placeholder="Duration" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-sm remove-slot">-</button>
        </div><br><br><br>`;
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
