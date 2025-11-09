<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('edit_display'); ?></h4>
            <hr class="hr-panel-heading" />

            <?= form_open_multipart(admin_url('token_system/edit_display/' . $display->id)); ?>

            <div class="row">
			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('display_name'); ?></label>
				  <input type="text" name="display_name" class="form-control" value="<?= $display->display_name ?>" required />
				</div>
			  </div>

			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('queue_type'); ?></label>
				  <select name="queue_type" class="form-control">
					<option value="Smart" <?= $display->queue_type == 'Smart' ? 'selected' : '' ?>><?php echo _l('smart'); ?></option>
					<option value="Manual" <?= $display->queue_type == 'Manual' ? 'selected' : '' ?>><?php echo _l('manual'); ?></option>
				  </select>
				</div>
			  </div>

			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('number_of_list'); ?></label>
				  <input type="number" name="number_of_list" class="form-control" value="<?= $display->number_of_list ?>" />
				</div>
			  </div>
			</div>

			<div class="row">
			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('doctor_info'); ?></label><br />
				  <label><input type="radio" name="doctor_info" value="1" <?= $display->doctor_info ? 'checked' : '' ?> /> <?php echo _l('yes'); ?></label>
				  <label><input type="radio" name="doctor_info" value="0" <?= !$display->doctor_info ? 'checked' : '' ?> /> <?php echo _l('no'); ?></label>
				</div>
			  </div>

			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('branding'); ?></label><br />
				  <label><input type="radio" name="display_logo" value="1" <?= $display->display_features_logo ? 'checked' : '' ?> /> <?php echo _l('yes'); ?></label>
				  <label><input type="radio" name="display_logo" value="0" <?= !$display->display_features_logo ? 'checked' : '' ?> /> <?php echo _l('no'); ?></label>
				</div>
			  </div>

			  <div class="col-md-4">
				  <div class="form-group">
					<label><?php echo _l('display_patient_info'); ?></label><br />
					
					<label><input type="checkbox" name="display_patient_info[]" value="Token number" <?= in_array('Token number', explode(',', $display->display_patient_info ?? '')) ? 'checked' : '' ?> /> <?php echo _l('token_number'); ?></label>
					
					<label><input type="checkbox" name="display_patient_info[]" value="Image" <?= in_array('Image', explode(',', $display->display_patient_info ?? '')) ? 'checked' : '' ?> /> <?php echo _l('image'); ?></label>
					
					<label><input type="checkbox" name="display_patient_info[]" value="Name" <?= in_array('Name', explode(',', $display->display_patient_info ?? '')) ? 'checked' : '' ?> /> <?php echo _l('name'); ?></label>
					
					<label><input type="checkbox" name="display_patient_info[]" value="Doctor Name" <?= in_array('Doctor Name', explode(',', $display->display_patient_info ?? '')) ? 'checked' : '' ?> /> <?php echo _l('Doctor Name'); ?></label>
					
					<label><input type="checkbox" name="display_patient_info[]" value="Status" <?= in_array('Status', explode(',', $display->display_patient_info ?? '')) ? 'checked' : '' ?> /> <?php echo _l('Status'); ?></label>
				  </div>
				</div>


			  <div class="col-md-4">
				<div class="form-group">
				  <label><?php echo _l('media_type'); ?></label>
				  <select name="media_type" class="form-control" id="mediaType">
					<option value="None" <?= $display->media_type == 'None' ? 'selected' : '' ?>><?php echo _l('none'); ?></option>
					<option value="Video" <?= $display->media_type == 'Video' ? 'selected' : '' ?>><?php echo _l('video'); ?></option>
					<option value="Images" <?= $display->media_type == 'Images' ? 'selected' : '' ?>><?php echo _l('images'); ?></option>
				  </select>
				</div>
			  </div>

			  <div class="row" id="mediaFields" style="display: none;">
				<div class="col-md-4" id="videoLinkDiv" style="display: none;">
				  <div class="form-group">
					<label><?php echo _l('youtube_link'); ?></label>
					<input type="text" name="youtube_link" class="form-control" value="<?= $display->youtube_link ?>" />
				  </div>
				</div>

				<div class="col-md-4" id="imageUploadDiv" style="display: none;">
				  <div class="form-group">
					<label><?php echo _l('upload_images'); ?></label>
					<input type="file" name="slider_images[]" multiple class="form-control" />
				  </div>
				</div>
			  </div>
			</div>


            <button type="submit" class="btn btn-primary">Save</button>
            <?= form_close(); ?>

            <script>
              document.addEventListener('DOMContentLoaded', function () {
                const mediaTypeSelect = document.getElementById('mediaType');
                const videoLinkDiv = document.getElementById('videoLinkDiv');
                const imageUploadDiv = document.getElementById('imageUploadDiv');
                const mediaFields = document.getElementById('mediaFields');
                mediaFields.style.display = 'none';
                videoLinkDiv.style.display = 'none';
                imageUploadDiv.style.display = 'none';

                mediaTypeSelect.addEventListener('change', function () {
                  const selectedMediaType = this.value;

                  videoLinkDiv.style.display = 'none';
                  imageUploadDiv.style.display = 'none';
                  mediaFields.style.display = 'block';

                  if (selectedMediaType === 'Video') {
                    videoLinkDiv.style.display = 'block';
                  } else if (selectedMediaType === 'Images') {
                    imageUploadDiv.style.display = 'block';
                  } else {
                    mediaFields.style.display = 'none';
                  }
                });
              });
            </script>

          </div>
        </div>
      </div>
    </div>
  </div>
<?php init_tail(); ?>
