<div class="checkbox checkbox-primary">
    <?php $include_modules = get_option('flexibackup_include_modules') ?? 1 ?>
    <input type="checkbox" name="settings[flexibackup_include_modules]" id="include_modules" value="1" <?php echo ($include_modules == 1) ? 'checked' : '' ?> />
    <label for="include_modules"><?php echo _l('flexibackup_modules'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_application = get_option('flexibackup_include_application') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_application]" id="include_application" value="1" <?php echo ($include_application == 1) ? 'checked' : '' ?> />
    <label for="include_application"><?php echo _l('flexibackup_application'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_uploads = get_option('flexibackup_include_uploads') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_uploads]" id="include_uploads" value="1" <?php echo ($include_uploads == 1) ? 'checked' : '' ?> />
    <label for="include_uploads"><?php echo _l('flexibackup_uploads'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_assets = get_option('flexibackup_include_assets') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_assets]" id="include_assets" value="1" <?php echo ($include_assets == 1) ? 'checked' : '' ?> />
    <label for="include_assets"><?php echo _l('flexibackup_assets'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_system = get_option('flexibackup_include_system') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_system]" id="include_system" value="1" <?php echo ($include_system == 1) ? 'checked' : '' ?> />
    <label for="include_system"><?php echo _l('flexibackup_system'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_resources = get_option('flexibackup_include_resources') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_resources]" id="include_resources" value="1" <?php echo ($include_resources == 1) ? 'checked' : '' ?> />
    <label for="include_resources"><?php echo _l('flexibackup_resources'); ?></label>
</div>
<div class="checkbox checkbox-primary">
    <?php $include_media = get_option('flexibackup_include_media') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_media]" id="include_media" value="1" <?php echo ($include_media == 1) ? 'checked' : '' ?> />
    <label for="include_media"><?php echo _l('flexibackup_media'); ?></label>
</div>

<div class="checkbox checkbox-primary">
    <?php $include_media = get_option('flexibackup_include_others') ?? 1; ?>
    <input type="checkbox" name="settings[flexibackup_include_others]" id="include_media" value="1" <?php echo ($include_media == 1) ? 'checked' : '' ?> />
    <label for="include_media"><?php echo _l('flexibackup_include_others'); ?></label>
</div>