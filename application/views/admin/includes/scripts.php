<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once APPPATH . 'views/admin/includes/helpers_bottom.php'; ?>

<?php hooks()->do_action('before_js_scripts_render'); ?>

<?= app_compile_scripts();

/**
 * Global function for custom field of type hyperlink
 */
echo get_custom_fields_hyperlink_js_function(); ?>
<?php
/**
 * Check for any alerts stored in session
 */
app_js_alerts();
?>
<?php
/**
 * Check pusher real time notifications
 */
if (get_option('pusher_realtime_notifications') == 1) { ?>
<script type="text/javascript">
    $(function() {
        // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;
        <?php $pusher_options = hooks()->apply_filters('pusher_options', [['disableStats' => true]]);
    if (! isset($pusher_options['cluster']) && get_option('pusher_cluster') != '') {
        $pusher_options['cluster'] = get_option('pusher_cluster');
    }
    ?>
        var
            pusher_options = <?= json_encode($pusher_options); ?> ;
        var pusher = new Pusher(
            "<?= get_option('pusher_app_key'); ?>",
            pusher_options);
        var channel = pusher.subscribe(
            'notifications-channel-<?= get_staff_user_id(); ?>'
        );
        channel.bind('notification', function(data) {
            fetch_notifications();
        });
    });
	
	/* $(document).ready(function () {
    // For all forms
    $(document).on('submit', 'form', function () {
        var $btn = $(this).find('button[type=submit], .btn[type=submit]');

        // Disable submit button
        $btn.prop('disabled', true).addClass('loading');

        // Optional: Re-enable after timeout (in case of failure)
        setTimeout(function () {
            $btn.prop('disabled', false).removeClass('loading');
        }, 5000);
    });

    // For non-form buttons (AJAX)
    $(document).on('click', 'button:not([type=submit]), .btn:not([type=submit])', function (e) {
        var $btn = $(this);
        if ($btn.prop('disabled')) {
            e.preventDefault();
            return false;
        }
        $btn.prop('disabled', true).addClass('loading');
        setTimeout(function () {
            $btn.prop('disabled', false).removeClass('loading');
        }, 5000);
    });
}); */

</script>
<?php } ?>
<?php app_admin_footer(); ?>