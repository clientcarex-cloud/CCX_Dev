<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop40">
    <div class="col-md-4 col-md-offset-4 text-center">
        <h1 class="tw-font-semibold mbot20 login-heading">
            <?php echo _l('enter_your_credentials') ?>
        </h1>
    </div>
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2">
        <?php echo form_open($this->uri->uri_string()); ?>
        <div class="panel_s">
            <div class="panel-body">

                <?php if (!is_language_disabled()) { ?>
                <div class="form-group">
                    <label for="language" class="control-label"><?php echo _l('language'); ?>
                    </label>
                    <select name="language" id="language" class="form-control selectpicker"
                        onchange="change_contact_language(this)"
                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                        data-live-search="true">
                        <?php $selected = (get_contact_language() != '') ? get_contact_language() : get_option('active_language'); ?>
                        <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                        <option value="<?php echo $availableLanguage; ?>"
                            <?php echo ($availableLanguage == $selected) ? 'selected' : '' ?>>
                            <?php echo ucfirst($availableLanguage); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group">
                    <label for="email"><?php echo _l('clients_login_email'); ?></label>
                    <input type="text" autofocus="true" class="form-control" name="email" id="email">
                    <?php echo form_error('email'); ?>
                </div>

                <div class="form-group">
                    <label for="password"><?php echo _l('clients_login_password'); ?></label>
                    <input type="password" class="form-control" name="password" id="password">
                    <?php echo form_error('password'); ?>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo _l('verify'); ?>
                    </button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>