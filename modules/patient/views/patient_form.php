<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .swal2-popup { font-size: 1.6rem !important; }
    .form-section {
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 6px;
        margin-bottom: 30px;
    }
    .form-heading {
        text-align: center;
        border-bottom: 1px solid #6a1b9a;
        margin-bottom: 30px;
        font-size: 22px;
        color: #6a1b9a;
        font-weight: 600;
        padding-bottom: 10px;
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
    .section-title::before,
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #6a1b9a;
        margin: 0 15px;
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

<div style="background-color: #6f2dbd; color: white; font-size: 24px; font-weight: bold; padding: 10px 20px; text-align: left; border-radius: 4px 4px 0 0;">
    Enquiry Form
</div>

<form method="post" action="#">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

    <div class="form-section">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Contact Number*</label>
                <input type="text" class="form-control" name="contact_number" value="<?= htmlspecialchars($patient['contact_number'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Enquiry Type*</label>
                <select class="form-control" name="enquiry_type_id">
                    <option value="">Select Enquiry Type</option>
                    <?php foreach ($enquiry_type as $type): ?>
                        <option value="<?= $type['enquiry_type_id'] ?>" <?= set_select('enquiry_type_id', $type['enquiry_type_id'], ($patient['enquiry_type_id'] ?? '') == $type['enquiry_type_id']) ?>>
                            <?= $type['enquiry_type_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Patient Response*</label>
                <select class="form-control" name="patient_response_id">
                    <option value="">Select Response</option>
                    <?php foreach ($patient_response as $response): ?>
                        <option value="<?= $response['patient_response_id'] ?>" <?= set_select('patient_response_id', $response['patient_response_id'], ($patient['patient_response_id'] ?? '') == $response['patient_response_id']) ?>>
                            <?= $response['patient_response_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title">Patient Information</div>
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Patient Name*</label>
                <div class="mb-3" style="display: flex; gap: 0;">
                    <div style="flex: 1; max-width: 22%;">
                        <select class="form-control" name="salutation">
                            <option value="Mr." <?= ($patient['salutation'] ?? '') == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                            <option value="Mrs." <?= ($patient['salutation'] ?? '') == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                            <option value="Ms." <?= ($patient['salutation'] ?? '') == 'Ms.' ? 'selected' : '' ?>>Ms.</option>
                        </select>
                    </div>
                    <div style="flex: 1; max-width: 49%;">
                        <input type="text" class="form-control" name="patient_name" value="<?= htmlspecialchars($patient['patient_name'] ?? '') ?>">
                    </div>
                    <div style="flex: 1; max-width: 29%;">
                        <select class="form-control" name="gender">
                            <option value="Male" <?= ($patient['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($patient['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($patient['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Age*</label>
                <input type="text" class="form-control" name="age" value="<?= htmlspecialchars($patient['age'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Area</label>
                <input type="text" class="form-control" name="area" value="<?= htmlspecialchars($patient['area'] ?? '') ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">City Name*</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($patient['city'] ?? '') ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">Whatsapp Number</label>
                <input type="text" class="form-control" name="whatsapp_number" value="<?= htmlspecialchars($patient['whatsapp_number'] ?? '') ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">Speaking Language</label>
                <select class="form-control" name="speaking_language_id">
                    <option value="">Select Language</option>
                    <?php foreach ($speaking_language as $lang): ?>
                        <option value="<?= $lang['speaking_language_id'] ?>" <?= ($patient['speaking_language_id'] ?? '') == $lang['speaking_language_id'] ? 'selected' : '' ?>>
                            <?= $lang['speaking_language_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">Alternative Number1</label>
                <input type="text" class="form-control" name="alt_number1" value="<?= htmlspecialchars($patient['alt_number1'] ?? '') ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">Alternative Number2</label>
                <input type="text" class="form-control" name="alt_number2" value="<?= htmlspecialchars($patient['alt_number2'] ?? '') ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label class="form-label">Patient Priority</label>
                <select class="form-control" name="patient_priority_id">
                    <option value="">Select Priority</option>
                    <?php foreach ($patient_priority as $priority): ?>
                        <option value="<?= $priority['patient_priority_id'] ?>" <?= ($patient['patient_priority_id'] ?? '') == $priority['patient_priority_id'] ? 'selected' : '' ?>>
                            <?= $priority['patient_priority_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title">Appointment Information</div>
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Branch*</label>
                <select class="form-control" name="branch_id">
                    <option value="">Select Branch</option>
                    <?php foreach ($branch as $br): ?>
                        <option value="<?= $br['branch_id'] ?>" <?= ($patient['branch_id'] ?? '') == $br['branch_id'] ? 'selected' : '' ?>>
                            <?= $br['branch_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Appointment Date*</label>
                <input type="text" class="form-control" name="appointment_date" value="<?= htmlspecialchars($patient['appointment_date'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Assign Doctor</label>
                <select class="form-control" name="assign_doctor_id">
                    <option value="">Select Doctor</option>
                    <?php foreach ($assign_doctor as $doc): ?>
                        <option value="<?= $doc['assign_doctor_id'] ?>" <?= ($patient['assign_doctor_id'] ?? '') == $doc['assign_doctor_id'] ? 'selected' : '' ?>>
                            <?= $doc['assign_doctor_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <label class="form-label">Slots*</label>
                <select class="form-control" name="slots_id">
                    <option value="">Select Slot</option>
                    <?php foreach ($slots as $slot): ?>
                        <option value="<?= $slot['slots_id'] ?>" <?= ($patient['slots_id'] ?? '') == $slot['slots_id'] ? 'selected' : '' ?>>
                            <?= $slot['slots_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Treatment*</label>
                <select class="form-control" name="treatment_id">
                    <option value="">Select Treatment</option>
                    <?php foreach ($treatment as $t): ?>
                        <option value="<?= $t['treatment_id'] ?>" <?= ($patient['treatment_id'] ?? '') == $t['treatment_id'] ? 'selected' : '' ?>>
                            <?= $t['treatment_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Consultation Fee*</label>
                <select class="form-control" name="consultation_fee_id">
                    <option value="">Select Consultation Fee</option>
                    <?php foreach ($consultation_fee as $fee): ?>
                        <option value="<?= $fee['consultation_fee_id'] ?>" <?= ($patient['consultation_fee_id'] ?? '') == $fee['consultation_fee_id'] ? 'selected' : '' ?>>
                            <?= $fee['consultation_fee_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <label class="form-label">Patient Source*</label>
                <select class="form-control" name="patient_source_id">
                    <option value="">Select Patient Source</option>
                    <?php foreach ($patient_source as $source): ?>
                        <option value="<?= $source['patient_source_id'] ?>" <?= ($patient['patient_source_id'] ?? '') == $source['patient_source_id'] ? 'selected' : '' ?>>
                            <?= $source['patient_source_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks"><?= htmlspecialchars($patient['remarks'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">Next Calling Date</label>
                <input type="text" class="form-control" name="next_calling_date" value="<?= htmlspecialchars($patient['next_calling_date'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button type="submit" class="btn btn-purple">Update</button>
        <a href="<?= admin_url('patient') ?>" class="btn btn-danger">Cancel</a>
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
</body>
</html>
