<!DOCTYPE html>
<html>
<head>
    <title>Public Token Counter</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    function askPassword(counter_id) {
        var password = prompt("Enter the password to continue:");
        if (password) {
            var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>';
            var csrf_token_value = '<?= $this->security->get_csrf_hash(); ?>';

            $.post("<?= admin_url('token_system/public_view/verify_password') ?>", {
                counter_id: counter_id,
                password: password,
                [csrf_token_name]: csrf_token_value
            }, function (response) {
                // Replace only the content section
                $('#content').html(response);
            }).fail(function () {
                alert('Error verifying password.');
            });
        }
    }

    $(document).ready(function () {
        <?php if (!isset($success)): ?>
            askPassword(<?= $counter->counter_id ?>);
        <?php endif; ?>
    });
    </script>
</head>
<body>

    <h2>Public Token Counter</h2>

    <div id="content">
        <?php if (isset($success)): ?>
            <p><strong>Counter Name:</strong> <?= $counter->counter_name ?></p>
            <p><strong>Counter ID:</strong> <?= $counter->counter_id ?></p>
        <?php else: ?>
            <p>Verifying access...</p>
        <?php endif; ?>
    </div>

</body>
</html>
