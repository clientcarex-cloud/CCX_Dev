<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">CCX Creator</h4>
                        <p class="text-muted mtop10">
                            Use the sidebar items below to access the new creation areas.
                        </p>
                        <?php if (! empty($sections)) : ?>
                            <ul class="list-unstyled mtop25">
                                <?php foreach ($sections as $section) : ?>
                                    <li class="mbot10">
                                        <i class="<?php echo html_escape($section['icon']); ?> mright5" aria-hidden="true"></i>
                                        <a href="<?php echo html_escape($section['href']); ?>">
                                            <?php echo html_escape($section['label']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
