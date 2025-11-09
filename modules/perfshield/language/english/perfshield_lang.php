<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Module Name
$lang['perfshield'] 								= 'PerfShield';

// Sidebar Links
$lang['perfshield_dashboard'] 						= 'Dashboard';
$lang['brute_force'] 	 	  						= 'Brute Force';

// Dashboard page
$lang['failed_login_attempts_logs'] 				= 'Failed Login Attempts Logs';
$lang['clear_log']			   						= 'Clear Log';
$lang['ip'] 				   						= 'IP';
$lang['last_failed_attempt']   						= 'Last Failed Attempt';
$lang['failed_attempts_count'] 						= 'Failed Attempts Count';
$lang['lockouts_count'] 	   						= 'Lockouts Count';

// Brute Force Settings
$lang['brute_force_settings']    					= 'Brute Force Settings';
$lang['max_retries'] 								= 'Max Retries';
$lang['max_failed_attempts_allowed_before_lockout'] = 'Maximun failed attempts allowed before lockout';
$lang['lockout_time'] 		  						= 'Lockout Time';
$lang['in_minutes'] 		  						= 'in minutes';
$lang['max_lockouts'] 								= 'Max Lockouts';
$lang['extend_lockout'] 						    = 'Extend Lockout';
$lang['in_hours'] 						            = 'in hours';
$lang['extend_lockout_time_after_max_lockouts']     = 'Extend Lockout time after Max Lockouts';
$lang['reset_retries'] 								= 'Reset Retries';
$lang['email_notification'] 						= 'Email Notification';
$lang['after'] 										= 'After';
$lang['no_of_lockouts'] 							= 'no. of lockouts';
$lang['lockouts'] 									= 'lockouts';
$lang['0_to_disable_email_notifications'] 			= '0 to disable email notifications';
$lang['save_settings']								= 'Save Settings';
$lang['bf_settings_updated']						= 'Brute force settings updated successfully.';
$lang['blacklist_ip_email'] 	     				= 'Blacklist IP/Email Settings';
$lang['blacklist'] 					 				= 'Blacklist';
$lang['one_ip_or_ip_range_per_line'] 				= 'One IP or IP range (1.2.3.4-5.6.7.8) per line';
$lang['blacklist_ip'] 				  				= 'Blacklist IP';
$lang['blacklist_email'] 			  				= 'Blacklist Email';
$lang['list_updated_success'] 		  				= 'List of IP/User updated successfully.';
$lang['ip_address'] 				  				= 'IP Address';
$lang['actions'] 					  				= 'Actions';
$lang['list_of_blacklist_ip']		  				= 'Blacklist IP\'s list';
$lang['list_of_blacklist_emails']     				= 'Blacklist Emails list';
$lang['removed_user_from_the_list']   				= 'User removed from the list';
$lang['edit_ip_address']           	  				= 'Edit IP Address';
$lang['update'] 					  				= 'Update';
$lang['ip_updated'] 				  				= 'IP Address updated successfully.';
$lang['user_ip_removed'] 		      				= 'IP/User removed.';
$lang['login_access_denied'] 		  				= 'Access denied. Your account has been blocked. Please contact administrator.';
$lang['add_ip_to_blacklist']    	  				= 'Add IP To Blacklist';
$lang['add_email_to_blacklist'] 	  				= 'Add Email To Blacklist';
$lang['email_address'] 		   	   	  				= 'Email Address';
$lang['ip_added_to_blacklist'] 	   	  				= 'IP address has been added to blacklist.';
$lang['email_added_to_blacklist']     				= 'Email address has been added to blacklist.';
$lang['ip_removed_from_blacklist']    				= 'IP address has been removed from blacklist.';
$lang['email_removed_from_blacklist'] 				= 'Email address has been removed from blacklist.';
$lang['country'] 	  				  				= 'Country';
$lang['country_code'] 				  				= 'Country Code';
$lang['isp'] 		  				  				= 'ISP';
$lang['mobile'] 	  				  				= 'Mobile';
$lang['one_email_address_per_line']   				= 'Enter one email address per line';
$lang['ip_address_already_exists']    				= 'This IP Address is already exists';
$lang['email_address_already_exists'] 				= 'This Email Address is already exists';
$lang['login_expiry_for_staff'] 		  			= 'Login Expiry For Staff';
$lang['user_inactivity'] 			  				= 'User Inactivity Timeout';
$lang['user_inactivity_description']  				= '0 to disable user inactivity timeout';
$lang['send_mail_if_ip_is_different'] 				= 'Send email notification to admin if staff logs in from different IP Address ?';
$lang['select_staff'] 				  				= 'Select Staff';
$lang['expiry_date'] 				  				= 'Expiry Date';
$lang['set_expiry_date'] 			  				= 'Set Expiry Date';
$lang['user_expiry'] 				  				= 'User\'s expiry';
$lang['expiry_date_added'] 			  				= 'User expiry added.';
$lang['expiry_date_removed'] 		  				= 'Expiry date removed.';
$lang['update_expiry_date'] 		  				= 'Update Expiry Date';
$lang['you_account_has_been_locked']  				= 'Your account has been locked';
$lang['reset_session'] 				  				= 'Reset Session';
$lang['enter_your_credentials'] 	  				= 'Enter your credentials';
$lang['verify'] 					  				= 'Verify';
$lang['incorrect_password']							= 'Incorrect Password';
$lang['session_reset']								= 'Your session has been reset, and you can now log in.';
$lang['user_not_found']								= 'User not found';
$lang['note']										= 'Note';
$lang['settings_for_only_staff_login'] 				= 'This settings are only applicable on staff login';
$lang['single_session_settings']					= 'Single Session Settings';
$lang['prevent_user_from_login_more_than_once'] 	= 'Prevent a user from being logged in more than once';
$lang['single_session_settings_updated'] 			= 'Setting updated.';
$lang['user_already_has_an_active_session']			= 'User already has an active session';
$lang['prevent_user_tooltip']						= 'It prevents staff user\'s to signin on multiple browsers. If previous session is not destroyed then user cannot signin from new browser. however staff user can reset previous session.';

$lang['is_mobile'] 	  				  				= 'Is Mobile';
$lang['no']											= 'No';
$lang['yes']										= 'Yes';

$lang['notice']										= 'Notice';
$lang['clear_log_notice']							= 'Once you clear the logs all the <b>rules</b> will be <b>reset</b>.';

$lang['logs_cleared']								= 'All logs are cleared.';

$lang['max_retries_tooltip'] = 'Maximum number of login retries allowed before lockout. This determines the maximum number of unsuccessful login attempts a user can make before being locked out of the system.';
$lang['lockout_time_tooltip'] = 'Duration of lockout in minutes. This specifies the amount of time a user will be prevented from attempting to log in after reaching the maximum number of retries.';
$lang['max_lockouts_tooltip'] = 'Maximum number of lockouts allowed. This sets the maximum number of times a user can be locked out.';
$lang['extend_lockout_tooltip'] = 'Duration to extend lockout in hours. This defines the additional time that will be added to the lockout duration if the user exceeds the maximum number of lockouts.';
$lang['reset_retries_tooltip'] = 'Duration to reset retries in hours. This determines the time duration to check for failed login attempts.';
$lang['email_notification_tooltip'] = 'Specifies the number of lockouts after which an email notification is sent to the associated email address.';
$lang['user_inactivity_tooltip'] = 'Duration of inactivity allowed before automatic logout. This sets the time limit for user inactivity, after which they will be automatically logged out for security reasons.';
$lang['send_email_notification_to_admin_tooltip'] = 'Enable email notification to the admin when staff members log in from a different IP address. This helps in identifying potential unauthorized access and provides an extra layer of security.';

$lang['cron_job_setup_required'] = 'This feature requires cron job setup to work properly.';
