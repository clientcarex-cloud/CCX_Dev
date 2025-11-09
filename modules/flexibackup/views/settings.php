<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <?php flexibackup_init_menu(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 animated fadeIn">
                <?php echo form_open($this->uri->uri_string(), ['id' => 'flexibackup_settings']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="form-group select-placeholder">
                            <label for="files_backup_schedule"
                                   class="control-label"><?php echo _l('flexibackup_files_backup_schedule'); ?></label>
                            <select name="settings[flexibackup_files_backup_schedule]" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php $default_files_backup_schedule = get_option('flexibackup_files_backup_schedule') ?? 1; ?>
                                <?php foreach (get_backup_schedules_types() as $type) { ?>
                                    <option value="<?php echo $type['key']; ?>"
                                        <?php if ($default_files_backup_schedule == $type['key']) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo _l($type['label']); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group select-placeholder">
                            <label for="database_backup_schedule"
                                   class="control-label"><?php echo _l('flexibackup_database_backup_schedule'); ?></label>
                            <select name="settings[flexibackup_database_backup_schedule]" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php $default_database_backup_schedule = get_option('flexibackup_database_backup_schedule') ?? 1; ?>
                                <?php foreach (get_backup_schedules_types() as $type) { ?>
                                    <option value="<?php echo $type['key']; ?>"
                                        <?php if ($default_database_backup_schedule == $type['key']) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo _l($type['label']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php
                        $time = get_option('flexibackup_auto_backup_time') ? get_option('flexibackup_auto_backup_time') : '22:00';
                        ?>
                        <div class="form-group">
                            <label for="choose_your_remote_storage"
                                   class="control-label"><?php echo _l('flexibackup_choose_the_time_of_your_scheduled_backup'); ?>
                            </label>
                            <div class="p-2 tw-ml-4">
                                <input type="time" name="settings[flexibackup_auto_backup_time]" class="form-control"
                                       value="<?php echo $time; ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <?php $flexibackup_folder_name = get_option('flexibackup_backup_name_prefix') == '' ? 'backup' : get_option('flexibackup_backup_name_prefix'); ?>
                            <?php echo render_input('settings[flexibackup_backup_name_prefix]', 'flexibackup_backup_name_prefix', $flexibackup_folder_name, 'text'); ?>
                        </div>
                        
                        <div class="form-group">
                            <?php $flexibackup_retain_for_days = empty(get_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING)) ? FLEXIBACKUP_RETAIN_FOR_DAYS_DEFAULT_VALUE : get_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING); ?>
                            <?php echo render_input('settings[' . FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING . ']', 'flexibackup_retain_for_days_label', $flexibackup_retain_for_days, 'text'); ?>
                        </div>

                        <div class="form-group">
                            <label for="choose_your_remote_storage"
                                   class="control-label"><?php echo _l('flexibackup_choose_your_remote_storage'); ?></label>
                            <!-- checkbox with grid images -->
                            <div class="tw-grid tw-flex-col tw-p-2 remote-storage-options">
                                <?php $default_remote_storage = get_option('flexibackup_remote_storage') ?? 'dropbox'; ?>
                                <?php foreach (get_remote_storage_options() as $option): ?>
                                    <div class="tw-flex tw-items-center remote-storage-option pointer tw-pt-2 tw-pb-2">
                                        <input type="radio" id="<?php echo $option['key'] ?>"
                                               name="settings[flexibackup_remote_storage]"
                                               value="<?php echo $option['key'] ?>"
                                               class="tw-mr-2" <?php echo ($default_remote_storage == $option['key']) ? 'checked' : '' ?> />
                                        <label for="<?php echo $option['key'] ?>" onclick="return flexi_show_key_pair('<?php echo $option['key'] ?>')">
                                            <img src="<?php echo module_dir_url('flexibackup', $option['icon']); ?>"
                                                 alt="Dropbox" />
                                            <span><?php echo _l($option['label']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group form-access-key-pairs">
                            <div class="google-drive-container <?php echo ($default_remote_storage == 'google-drive') ? '' : 'hidden' ?>" id="google-drive">
                                <?php echo render_input('flexibackup_google_drive_client_id', 'flexibackup_google_drive_client_id', get_option('flexibackup_google_drive_client_id'), 'text'); ?>
                                <?php echo render_input('flexibackup_google_drive_secret', 'flexibackup_google_drive_secret', get_option('flexibackup_google_drive_secret'), 'text'); ?>
                                <?php echo render_input('flexibackup_google_drive_refresh_token', 'flexibackup_google_drive_refresh_token', get_option('flexibackup_google_drive_refresh_token'), 'text'); ?>
                            </div>
                            <div class="email-container <?php echo ($default_remote_storage == 'email') ? '' : 'hidden' ?>" id="email">
                                <p><?php echo _l("flexibackup_email_note") ?></p>
                                <?php echo render_input('flexibackup_email_address', 'flexibackup_email_address', get_option('flexibackup_email_address'), 'text'); ?>
                            </div>
                            <div class="ftp-container <?php echo ($default_remote_storage == 'ftp') ? '' : 'hidden' ?>" id="ftp">
                                <?php echo render_input('flexibackup_ftp_server', 'flexibackup_ftp_server', get_option('flexibackup_ftp_server'), 'text'); ?>
                                <?php echo render_input('flexibackup_ftp_user', 'flexibackup_ftp_user', get_option('flexibackup_ftp_user'), 'text'); ?>
                                <?php echo render_input('flexibackup_ftp_password', 'flexibackup_ftp_password', get_option('flexibackup_ftp_password'), 'text'); ?>
                                <?php echo render_input('flexibackup_ftp_path', 'flexibackup_ftp_path', get_option('flexibackup_ftp_path'), 'text'); ?>
                            </div>
                            <div class="sftp-container <?php echo ($default_remote_storage == 'sftp') ? '' : 'hidden' ?>" id="sftp">
                                <?php echo render_input('flexibackup_sftp_server', 'flexibackup_sftp_server', get_option('flexibackup_sftp_server'), 'text'); ?>
                                <?php echo render_input('flexibackup_sftp_user', 'flexibackup_sftp_user', get_option('flexibackup_sftp_user'), 'text'); ?>
                                <?php echo render_input('flexibackup_sftp_password', 'flexibackup_sftp_password', get_option('flexibackup_sftp_password'), 'text'); ?>
                                <?php echo render_input('flexibackup_sftp_path', 'flexibackup_sftp_path', get_option('flexibackup_sftp_path'), 'text'); ?>
                            </div>
                            <div class="s3-container <?php echo ($default_remote_storage == 's3') ? '' : 'hidden' ?>" id="s3">
                                <img src="<?php echo module_dir_url('flexibackup', 'assets/images/aws_logo.png'); ?>"
                                     alt="Amazon S3" />
                                <p class="description">
                                    <?php echo _l('flexibackup_s3_description'); ?>
                                </p>
                                <?php echo render_input('flexibackup_s3_access_key', 'flexibackup_s3_access_key', get_option('flexibackup_s3_access_key'), 'text'); ?>
                                <?php echo render_input('flexibackup_s3_secret_key', 'flexibackup_s3_secret_key', get_option('flexibackup_s3_secret_key'), 'text'); ?>
                                <?php echo render_input('flexibackup_s3_location', 'flexibackup_s3_location', get_option('flexibackup_s3_location'), 'text'); ?>
                                <?php echo render_input('flexibackup_s3_region', 'flexibackup_s3_region', get_option('flexibackup_s3_region'), 'text'); ?>
                            </div>
                            <div class="webdav-container <?php echo ($default_remote_storage == 'webdav') ? '' : 'hidden' ?>" id="s3">
                                <img src="<?php echo module_dir_url('flexibackup', 'assets/images/webdav.png'); ?>"
                                     alt="Webdav" />
                                <?php echo render_input('flexibackup_webdav_base_uri', 'flexibackup_webdav_base_uri', get_option('flexibackup_webdav_base_uri'), 'text'); ?>
                                <?php echo render_input('flexibackup_webdav_username', 'flexibackup_webdav_username', get_option('flexibackup_webdav_username'), 'text'); ?>
                                <?php echo render_input('flexibackup_webdav_password', 'flexibackup_webdav_password', get_option('flexibackup_webdav_password'), 'text'); ?>
                            </div>
                            <div class="onedrive-container <?php echo ($default_remote_storage == 'onedrive') ? '' : 'hidden' ?>" id="onedrive">
                                <div id="flexibackup_onedrive_settings_uri" class="hidden" data-setting-uri="<?php echo $this->uri->uri_string() ?>"></div>
                                <?php echo render_input('flexibackup_onedrive_client_id', 'flexibackup_onedrive_client_id', get_option('flexibackup_onedrive_client_id'), 'text'); ?>
                                <?php echo render_input('flexibackup_onedrive_client_secret', 'flexibackup_onedrive_client_secret', get_option('flexibackup_onedrive_client_secret'), 'text'); ?>
                                
                                <button id="onedrive-authorize-btn" class="btn btn-primary <?php echo empty(get_option(FLEXIBACKUP_ONEDRIVE_CODE_SETTING)) ? '' : 'hidden' ?>">
                                    <?php echo _l('flexibackup_authorize') ?>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="choose_your_remote_storage"
                                   class="control-label"><?php echo _l('flexibackup_include_in_files_backup'); ?>
                            </label>
                            <div class="p-2 tw-ml-4">
                                <?php echo flexi_backup_file_options(); ?>
                            </div>
                        </div>
                        <!-- yes and no -->
                        <div class="form-group">
                            <?php echo render_yes_no_option('flexibackup_auto_backup_to_remote_enabled', 'flexibackup_auto_backup_to_remote_enabled'); ?>
                        </div>
                        <div class="form-group">
                            <?php echo render_yes_no_option('flexibackup_auto_delete_remote_backups', 'flexibackup_auto_delete_remote_backups'); ?>
                        </div>

                    </div>
                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <?php echo _l('flexibackup-save-changes'); ?>
                        </button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
    <?php init_backup_now_form(); ?>
    <?php init_tail(); ?>
</body>

</html>