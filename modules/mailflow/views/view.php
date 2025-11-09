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
                                <th><?php echo _l('mailflow_sent_by'); ?></th>
                                <td><?php echo get_staff_full_name($newsletterData->sent_by); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_total_emails_to_send'); ?></th>
                                <td><?php echo $newsletterData->total_emails_to_send; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_total_sms_to_send'); ?></th>
                                <td><?php echo $newsletterData->total_sms_to_send; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_total_whatsapp_to_send'); ?></th>
                                <td><?php echo $newsletterData->total_whatsapp_to_send; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_emails_sent'); ?></th>
                                <td><?php echo $newsletterData->emails_sent; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_sms_sent'); ?></th>
                                <td><?php echo $newsletterData->sms_sent; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_whatsapp_sent'); ?></th>
                                <td><?php echo $newsletterData->whatsapp_sent; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_emails_failed'); ?></th>
                                <td><?php echo $newsletterData->emails_failed; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_sms_failed'); ?></th>
                                <td><?php echo $newsletterData->sms_failed; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo _l('mailflow_whatsapp_failed'); ?></th>
                                <td><?php echo $newsletterData->whatsapp_failed; ?></td>
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
								<th><?php echo _l('mailflow_whatsapp_list'); ?></th>
								<td>
									<?php
									$whatsappListRaw = $newsletterData->whatsapp_list ?? '';
									$whatsappList = is_string($whatsappListRaw) && !empty($whatsappListRaw) 
										? json_decode($whatsappListRaw, true) 
										: [];

									echo is_array($whatsappList) ? implode(',', $whatsappList) : '';
									?>

								</td>
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
                        <?php echo !empty($newsletterData->sms_content) ? html_entity_decode($newsletterData->sms_content) : ''; ?><br>
                        <h4><?php echo _l('mailflow_whatsapp_content'); ?></h4>
						<?php
						$whatsappContent = $newsletterData->whatsapp_template_content ?? '';

						if (is_array($whatsappContent)) {
							echo implode(', ', $whatsappContent); // Or any separator you prefer
						} else {
							echo html_entity_decode($whatsappContent);
						}
						?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
