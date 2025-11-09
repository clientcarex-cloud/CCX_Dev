<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">

<h4 class="no-margin"><?php echo _l('add_display'); ?></h4>
<hr class="hr-panel-heading" />

<?= form_open_multipart(admin_url('token_system/add')); ?>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('display_name'); ?></label>
      <input type="text" name="display_name" class="form-control" required />
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('queue_type'); ?></label>
      <select name="queue_type" class="form-control">
        <option value="Smart"><?php echo _l('smart'); ?></option>
        <option value="Manual"><?php echo _l('manual'); ?></option>
      </select>
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('number_of_list'); ?></label>
      <input type="number" name="number_of_list" class="form-control" />
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('doctor_info'); ?></label><br />
      <label><input type="radio" name="doctor_info" value="1" /> <?php echo _l('yes'); ?></label>
      <label><input type="radio" name="doctor_info" value="0" checked /> <?php echo _l('no'); ?></label>
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('branding'); ?></label><br />
      <label><input type="radio" name="display_logo" value="1" checked /> <?php echo _l('yes'); ?></label>
      <label><input type="radio" name="display_logo" value="0" /> <?php echo _l('no'); ?></label>
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
	
      <label><input type="checkbox" name="display_patient_info[]" value="Token number" /> <?php echo _l('token_number'); ?></label>
      <label><?php echo _l('display_patient_info'); ?></label><br />
      <label><input type="checkbox" name="display_patient_info[]" value="Image" /> <?php echo _l('image'); ?></label>
      <label><input type="checkbox" name="display_patient_info[]" value="Name" /> <?php echo _l('name'); ?></label>
      <label><input type="checkbox" name="display_patient_info[]" value="Doctor Name" /> <?php echo _l('Doctor Name'); ?></label>
      <label><input type="checkbox" name="display_patient_info[]" value="Status" /> <?php echo _l('Status'); ?></label>
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label><?php echo _l('media_type'); ?></label>
      <select name="media_type" class="form-control" id="mediaType">
        <option value="None"><?php echo _l('none'); ?></option>
        <option value="Video"><?php echo _l('video'); ?></option>
        <option value="Images"><?php echo _l('images'); ?></option>
      </select>
    </div>
  </div>

  <div class="row" id="mediaFields" style="display: none;">
    <div class="col-md-4" id="videoLinkDiv" style="display: none;">
      <div class="form-group">
        <label><?php echo _l('youtube_link'); ?></label>
        <input type="text" name="youtube_link" class="form-control" />
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



<div class="row">
  
</div>

<button type="submit" class="btn btn-primary">Save</button>
<?= form_close(); ?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
  // Get the media type select element
  const mediaTypeSelect = document.getElementById('mediaType');
  
  // Get the video and image div elements
  const videoLinkDiv = document.getElementById('videoLinkDiv');
  const imageUploadDiv = document.getElementById('imageUploadDiv');
  const mediaFields = document.getElementById('mediaFields');
  
  // Initial setup to hide both media options
  mediaFields.style.display = 'none';
  videoLinkDiv.style.display = 'none';
  imageUploadDiv.style.display = 'none';

  // Add event listener to handle changes in the media type selection
  mediaTypeSelect.addEventListener('change', function () {
    const selectedMediaType = this.value;

    // Reset visibility of both fields
    videoLinkDiv.style.display = 'none';
    imageUploadDiv.style.display = 'none';
    mediaFields.style.display = 'block'; // Show the media fields container

    // Show the appropriate media field based on selection
    if (selectedMediaType === 'Video') {
      videoLinkDiv.style.display = 'block'; // Show video link field
    } else if (selectedMediaType === 'Images') {
      imageUploadDiv.style.display = 'block'; // Show image upload field
    } else {
      mediaFields.style.display = 'none'; // Hide media fields if "None" is selected
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

