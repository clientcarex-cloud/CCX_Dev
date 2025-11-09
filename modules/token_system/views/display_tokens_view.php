<?php
$columns = ["Token number", "Image", "Name", "Doctor Name", "Status"];

// Ensure trimming and add 'Doctor Name' always
$columns = array_map('trim', $columns);
/* if (!in_array('Doctor Name', $columns)) {
    $columns[] = 'Doctor Name';
} */

// Column labels
$column_map = [
    'Image' => 'Image',
    'Name' => 'Patient Name',
    'Token number' => 'Token No',
    'Status' => 'Status',
    'Doctor Name' => 'Doctor Name'
];

// Merge current and queued patients
$patients = [];
if (!empty($current_patient)) {
    foreach ($current_patient as $p) {
        $p['status'] = 'Serving';
        $patients[] = $p;
    }
}
if (!empty($queued_patients)) {
    foreach ($queued_patients as $p) {
        $p['status'] = 'Pending';
        $patients[] = $p;
    }
}

$table1 = array_slice($patients, 0, 12);
$table2 = array_slice($patients, 12, 12);

function render_table($table_data, $columns, $column_map) {
    echo "<table class='token-table'>";
    echo "<thead><tr>";
    foreach ($columns as $col) {
        echo "<th>" . ($column_map[$col] ?? '') . "</th>";
    }
    echo "</tr></thead><tbody>";

    for ($i = 0; $i < 12; $i++) {
        echo "<tr>";
        if (isset($table_data[$i])) {
            $row = $table_data[$i];
            foreach ($columns as $col) {
                switch ($col) {
                    case 'Image':
                        echo "<td><img src='https://images.vexels.com/media/users/3/134789/isolated/lists/aa4c5ca0e2a83abbf167e49c8a50e834-happy-smile-emoji-emoticon-icon.png' class='rounded-circle' width='40' height='40'></td>";
                        break;
                    case 'Name':
                        echo "<td>{$row['salutation']} {$row['company']}</td>";
                        break;
                    case 'Token number':
                        echo "<td>#{$row['token_number']}</td>";
                        break;
                    case 'Status':
                        $badge = $row['status'] === 'Serving' ? '<span class=\'badge bg-success\'>Serving</span>' : '<span class=\'badge bg-warning text-dark\'>Queued</span>';
                        echo "<td>$badge</td>";
                        break;
                    case 'Doctor Name':
                        echo "<td>" . ($row['doctor_name'] ?? '-') . "</td>";
                        break;
                    default:
                        echo "<td>-</td>";
                }
            }
        } else {
            foreach ($columns as $col) {
                echo "<td>-</td>";
            }
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Public Token Display</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%; margin: 0; overflow-x: hidden; background-color: #f8f9fa;
    }
    .header {
      background: #fff; padding: 10px 20px; border-bottom: 1px solid #dee2e6;
      display: flex; justify-content: space-between; align-items: center; position: relative;
    }
    .datetime-display {
      position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
      font-size: 1.5rem; color: #000; font-weight: bold;
    }
    .token-container {
      padding: 20px;
    }
    .token-wrapper {
      margin-bottom: 20px;
    }
    .token-table {
      width: 100%; font-size: 16px; border-collapse: collapse; background-color: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .token-table th {
      background-color: #52a044; color: white; padding: 6px; text-align: center; border: 1px solid #fff;
    }
    .token-table td {
      padding: 6px; border: 1px solid #dee2e6; text-align: center;
    }
    .token-table tbody tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    .img-responsive {
      max-width: 100%; height: auto;
    }
  </style>
</head>
<body>
<div class="header">
  <?php $logo = get_admin_header_logo_url(); ?>
  <img src="<?= e($logo); ?>" class="img-responsive" alt="<?= e(get_option('companyname')); ?>" style="width: 150px"/>
  <i class="fas fa-expand" onclick="toggleFullScreen()"></i>
  <div id="datetime" class="datetime-display"></div>
</div>
<div class="container-fluid token-container">
  <div class="row">
    <div class="col-md-6 col-12">
      <div class="token-wrapper">
        <?php render_table($table1, $columns, $column_map); ?>
      </div>
    </div>
    <div class="col-md-6 col-12">
      <div class="token-wrapper">
        <?php render_table($table2, $columns, $column_map); ?>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleFullScreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen();
    } else {
      document.exitFullscreen();
    }
  }
  function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const date = now.toLocaleDateString();
    document.getElementById("datetime").textContent = `${date} ${hours}:${minutes}:${seconds}`;
  }
  setInterval(updateTime, 1000);
  updateTime();
</script>
</body>
</html>
