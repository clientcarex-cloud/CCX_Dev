<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-6 col-md-offset-3">
<div class="panel_s">
<div class="panel-body">
<h4 class="bold">Edit Availability - <?= $doctor->firstname ?> <?= $doctor->lastname ?></h4>

<form method="post" action="<?= admin_url('client/doctor/save_availability') ?>">
    <input type="hidden" name="id" value="<?= $record['id'] ?>">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <input type="hidden" name="staff_id" value="<?= $record['staff_id'] ?>">
   <?php
$day_options = [
    ['id' => 'Monday', 'name' => 'Monday'],
    ['id' => 'Tuesday', 'name' => 'Tuesday'],
    ['id' => 'Wednesday', 'name' => 'Wednesday'],
    ['id' => 'Thursday', 'name' => 'Thursday'],
    ['id' => 'Friday', 'name' => 'Friday'],
    ['id' => 'Saturday', 'name' => 'Saturday'],
    ['id' => 'Sunday', 'name' => 'Sunday'],
];
echo render_select('day_of_week', $day_options, ['id', 'name'], 'Day of Week', $record['day_of_week'] ?? '');
?>

    <?= render_input('start_time', 'Start Time', $record['start_time'], 'time') ?>
    <?= render_input('end_time', 'End Time', $record['end_time'], 'time') ?>
    <?= render_input('time_gap_minutes', 'Time Gap (mins)', $record['time_gap_minutes'], 'number') ?>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

</div></div></div></div></div>
<?php init_tail(); ?>
</body>
</html>
