<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="panel_s">
<div class="panel-body">
<h4 class="bold">Availability for <?= $doctor->firstname ?> <?= $doctor->lastname ?></h4>

<hr>

<form method="post" action="<?= admin_url('client/doctor/save_availability') ?>">
    <input type="hidden" name="staff_id" value="<?= $staff_id ?>">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <div class="row">
        <div class="col-md-3">
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
echo render_select('day_of_week', $day_options, ['id', 'name'], 'Day of Week');
?>

        </div>
        <div class="col-md-3">
            <?= render_input('start_time', 'Start Time', '', 'time') ?>
        </div>
        <div class="col-md-3">
            <?= render_input('end_time', 'End Time', '', 'time') ?>
        </div>
        <div class="col-md-2">
            <?= render_input('time_gap_minutes', 'Time Gap (mins)', '15', 'number') ?>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-success mt-4">Add</button>
        </div>
    </div>
</form>

<hr>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Day</th>
            <th>Start</th>
            <th>End</th>
            <th>Gap</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($availability) && is_array($availability)): ?>
			<?php foreach ($availability as $a): ?>
				<tr>
					<td><?= $a['day_of_week'] ?></td>
					<td><?= date('h:i A', strtotime($a['start_time'])) ?></td>
					<td><?= date('h:i A', strtotime($a['end_time'])) ?></td>
					<td><?= $a['time_gap_minutes'] ?> mins</td>
					<td>
						<a href="<?= admin_url('client/doctor/edit_availability/' . $a['id']) ?>" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>
						<a href="<?= admin_url('client/doctor/delete_availability/' . $a['id']) ?>" class="btn btn-danger btn-sm _delete"><i class="fa fa-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr><td colspan="5" class="text-center">No availability set.</td></tr>
		<?php endif; ?>

    </tbody>
</table>

</div></div></div></div></div>
<?php init_tail(); ?>
</body>
</html>
