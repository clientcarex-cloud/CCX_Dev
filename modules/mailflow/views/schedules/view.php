<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $newsletterData->email_subject; ?>
                </h4>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table table-bordered">
                            <tr>
                                <th><?php echo _l('mailflow_scheduled_by'); ?></th>
                                <td><?php echo '<a href="' . admin_url('staff/profile/' . $newsletterData->scheduled_by) . '">' . staff_profile_image($newsletterData->scheduled_by, [
                                            'staff-profile-image-small',
                                        ]) . '</a>'; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_scheduled_to'); ?></th>
                                <td><?php echo $newsletterData->scheduled_to . '<br><span style="color: mediumpurple">' . mailflow_human_readable_time_difference($newsletterData->scheduled_to) . '</span>'; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_campaign_status'); ?></th>
                                <td><?php echo mailflow_campaign_statuses($newsletterData->campaign_status)['badge']; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_email_subject'); ?></th>
                                <td><?php echo $newsletterData->email_subject; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_selected_smtp'); ?></th>
                                <td><?php echo !is_object(mailflow_get_email_integrations($newsletterData->email_smtp)) ? mailflow_get_email_integrations($newsletterData->email_smtp) : mailflow_get_email_integrations($newsletterData->email_smtp)->name; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_mails_list'); ?></th>
                                <td><?php echo !empty($newsletterData->email_list) ? implode(',', json_decode($newsletterData->email_list)) : ''; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_sms_list'); ?></th>
                                <td><?php echo !empty($newsletterData->sms_list) ? implode(',', json_decode($newsletterData->sms_list)) : ''; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_created_at'); ?></th>
                                <td><?php echo $newsletterData->created_at; ?></td>
                            </tr>
                        </table>
                        <h4><?php echo _l('mailflow_mail_content'); ?></h4>
                        <?php echo !empty($newsletterData->email_content) ? html_entity_decode($newsletterData->email_content) : ''; ?>
                        <br>
                        <h4><?php echo _l('mailflow_sms_content'); ?></h4>
                        <?php echo !empty($newsletterData->sms_content) ? html_entity_decode($newsletterData->sms_content) : ''; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
