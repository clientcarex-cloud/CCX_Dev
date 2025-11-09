<!--edit custom theme name modal-->
<div class="modal fade" id="si_ct_edit_custom_theme_name_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
            <?php echo form_open(admin_url('si_custom_theme/save_custome_theme_name/'.$theme['id']),['id' => 'si-ct-edit-theme-name-form']);?>   
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span><?php echo _l('edit').' '.$theme['theme_name']; ?></span>
				</h4>
		  	</div>
		  	<div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php echo render_input('theme_name','si_ct_new_theme_name',$theme['theme_name'],'text',array('required'=>true,'maxlength'=>200));?>
                    </div>
                    <div class="col-md-4 setting-color pull-right">
                        <label>
                            <span class="split">
                                <span id="thumbnail_1" class="color <?php echo ($theme['class'][0] == 'bg-custom' ? $theme['class'][0] : '')?>" style="background-color:<?php echo ($theme['class'][0] !== 'bg-custom' ? $theme['class'][0] : '');?>;"></span>
                                <span id="thumbnail_2" class="color <?php echo ($theme['class'][1] == 'bg-custom' ? $theme['class'][1] : '')?>" style="background-color:<?php echo ($theme['class'][1] !== 'bg-custom' ? $theme['class'][1] : '');?>;"></span>
                            </span>
                            <span id="thumbnail_3" class="color <?php echo ($theme['class'][2] == 'bg-custom' ? $theme['class'][2] : '')?>" style="background-color:<?php echo ($theme['class'][2] !== 'bg-custom' ? $theme['class'][2] : '');?>;"></span>
                        </label>
                    </div>
                </div> 
                <hr/>
                <div class="row">   
                    <div class="col-md-4">
                        <?php echo render_color_picker('color[0]', _l('si_ct_thumbnail_color_1'),($theme['class'][0] == 'bg-custom' ? '#cccccc' : $theme['class'][0]),['data-id'=>'#thumbnail_1']); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_color_picker('color[1]', _l('si_ct_thumbnail_color_2'),($theme['class'][1] == 'bg-custom' ? '#cccccc' : $theme['class'][1]),['data-id'=>'#thumbnail_2']); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_color_picker('color[2]', _l('si_ct_thumbnail_color_3'),($theme['class'][2] == 'bg-custom' ? '#cccccc' : $theme['class'][2]),['data-id'=>'#thumbnail_3']); ?>
                    </div>
                    
                </div>
		  	</div>
		  	<div class="modal-footer">
			 	<button type="submit" class="btn btn-info" id="saveTemplateDetails"><?php echo _l('submit'); ?></button>
			 	<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
		  	</div>
            <?php echo form_close();?>
	   	</div>
	</div>
</div>
<!--edit custom theme name modal-->