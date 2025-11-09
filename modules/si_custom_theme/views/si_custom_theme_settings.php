<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4><?php echo _l('si_custom_theme').' '._l('si_ct_settings_validate'); ?></h4>
<hr/>
<?php if(!get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activated') || get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activation_code')==''){?>
<div class="row" id="si_ct_validate_wrapper" data-wait-text="<?php echo '<i class=\'fa fa-spinner fa-pulse\'></i> '._l('wait_text'); ?>" data-original-text="<?php echo _l('si_ct_settings_validate'); ?>">
	<div class="col-md-9">
		<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('si_ct_settings_purchase_code_help'); ?>"></i>
		<?php echo render_input('settings['.SI_CUSTOM_THEME_MODULE_NAME.'_activation_code]','si_ct_settings_activation_code',get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activation_code'),'text',array('data-toggle'=>'tooltip','data-title'=>_l('si_ct_settings_purchase_code_help'),'maxlength'=>60)); 
			echo form_hidden('settings['.SI_CUSTOM_THEME_MODULE_NAME.'_activated]',get_option(SI_CUSTOM_THEME_MODULE_NAME.'_activated'));
		?>
		<span><?php echo _l('si_ct_settings_valid_purchase_help'); ?></span>
		<span><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"><?php echo _l('setup_help'); ?></a></span>
	</div>
	<div class="col-md-3 mtop25">
		<button id="si_ct_validate" class="btn btn-success"><?php echo _l('si_ct_settings_validate');?></button>
	</div>
	<div class="col-md-12" id="si_ct_validate_messages" class="mtop25 text-left"></div>
</div>
<?php }else{?>
	<span><?php echo _l('si_ct_settings_activated_info');?> <a href="<?php echo admin_url('si_custom_theme')?>"><?php echo _l('settings') ?></a></span>
<?php } ?>
		
	