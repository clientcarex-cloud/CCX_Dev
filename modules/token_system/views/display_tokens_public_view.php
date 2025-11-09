<?php
$counter_status = strtolower($counter->counter_status ?? '');
$display_config = get_display_config($counter->display_id);
$columns = isset($display_config->display_patient_info) ? explode(',', $display_config->display_patient_info) : [];
$columns = array_map('trim', $columns);

$column_map = [
    'Image' => 'Image',
    'Name' => 'Client\'s Name',
    'Token number' => 'Queue No',
    'Status' => 'Status',
    'Doctor Name' => 'Doctor Name'
];

function render_table($table_data, $columns, $column_map) {
    $serving_token_number = null;
    foreach ($table_data as $row) {
        if (isset($row['status']) && strtolower($row['status']) === 'serving') {
            $serving_token_number = (int)$row['token_number'];
            break;
        }
    }

    echo "<table class='token-table'>";
    echo "<thead><tr>";
    foreach ($columns as $col) {
        echo "<th>" . ($column_map[$col] ?? '') . "</th>";
    }
    echo "</tr></thead><tbody>";

    for ($i = 0; $i < 12; $i++) {
        $row_class = '';
        $badge = '';

        if (isset($table_data[$i])) {
            $row = $table_data[$i];
            $row_status = strtolower($row['status'] ?? '');
            $token_number = (int)($row['token_number'] ?? 0);

            if ($row_status === 'serving') {
                $badge = '<span class="badge bg-white text-success border border-success">Consulting</span>';
                $row_class = 'text-success';
            } elseif ($row_status === 'pending' && isset($serving_token_number) && $token_number === $serving_token_number + 1) {
                $badge = '<span class="badge bg-white text-primary border border-primary">Next</span>';
                $row_class = 'text-primary';
            } else {
                $badge = '<span class="badge bg-white text-warning border border-warning">Waiting</span>';
                $row_class = 'text-warning';
            }

            echo "<tr class='$row_class'>";
            foreach ($columns as $col) {
                switch ($col) {
                    case 'Image':
                        echo "<td><img src='https://images.vexels.com/media/users/3/134789/isolated/lists/aa4c5ca0e2a83abbf167e49c8a50e834-happy-smile-emoji-emoticon-icon.png' class='rounded-circle' width='40' height='40'></td>";
                        break;
                    case 'Name':
                        echo "<td style='font-weight: 900'>{$row['salutation']} {$row['company']}</td>";
                        break;
                    case 'Token number':
                        echo "<td>#{$row['token_number']}</td>";
                        break;
                    case 'Status':
                        echo "<td>$badge</td>";
                        break;
                    case 'Doctor Name':
                        echo "<td>" . ($row['doctor_name'] ?? '-') . "</td>";
                        break;
                    default:
                        echo "<td>-</td>";
                }
            }
            echo "</tr>";
        } else {
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<td>-</td>";
            }
            echo "</tr>";
        }
    }

    echo "</tbody></table>";
}

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
      height: 100%;
      margin: 0;
      overflow-x: hidden;
      background-color: #f8f9fa;
    }
    .header {
      background: #fff;
      padding: 5px 20px;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
    }
    .datetime-display {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 1.5rem;
      color: #000;
      font-weight: bold;
    }
    .token-container {
      padding: 20px;
    }
    .token-wrapper {
      margin-bottom: 20px;
    }
    .token-table {
      width: 100%;
      font-size: 16px;
      border-collapse: collapse;
      background-color: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .token-table th {
      background-color: #52a044;
      color: white;
      padding: 2px;
      text-align: center;
      border: 1px solid #fff;
    }
    .token-table td {
      padding: 2px;
      border: 1px solid #dee2e6;
      text-align: center;
    }
    .token-table tbody tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    .status-overlay {
      position: fixed;
      top: 60px;
      left: 0;
      width: 100%;
      height: calc(100% - 60px);
      display: none;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      font-weight: bold;
      color: #fff;
      z-index: 9999;
    }
    .emergency-overlay {
      background-color: rgba(255, 0, 0, 0.7);
    }
    .lunch-overlay {
      background-color: rgba(0, 0, 0, 0.6);
    }
    body.blurred *:not(.status-overlay):not(script):not(style) {
      filter: blur(5px);
      pointer-events: none;
    }
  </style>
</head>
<body>
<div class="header">
  <?php $logo = get_admin_header_logo_url(); ?>
  <img src="<?= e($logo); ?>" class="img-responsive" alt="<?= e(get_option('companyname')); ?>" style="width: 35px"/>
  <i class="fas fa-expand" onclick="toggleFullScreen()"></i>
  <div id="datetime" class="datetime-display"></div>
</div>

<div class="container-fluid token-container">
  <div class="row">
    <div class="col-md-6 col-12">
      <div class="token-wrapper" id="tokenTable1">
        <?php render_table($table1, $columns, $column_map); ?>
      </div>
    </div>
    <div class="col-md-6 col-12">
      <div class="token-wrapper" id="tokenTable2">
        <?php render_table($table2, $columns, $column_map); ?>
      </div>
    </div>
  </div>
</div>

<div id="emergencyOverlay" class="status-overlay emergency-overlay">
  <i class="fas fa-bell fa-shake me-3"></i> EMERGENCY
</div>
<div id="lunchOverlay" class="status-overlay lunch-overlay">
  <i class="fas fa-pause-circle me-3"></i> LUNCH BREAK
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function requestFullscreen() {
  const el = document.documentElement;
  if (el.requestFullscreen) el.requestFullscreen();
  else if (el.mozRequestFullScreen) el.mozRequestFullScreen();
  else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
  else if (el.msRequestFullscreen) el.msRequestFullscreen();
}

$(document).ready(function () {
  const counterStatus = '<?= $counter_status ?>';

  $(document).one('click', function () {
    requestFullscreen();
    sessionStorage.setItem('fullscreen_enabled', '1');
  });

  if (sessionStorage.getItem('fullscreen_enabled') === '1') {
    setTimeout(() => requestFullscreen(), 500);
  }

  function updateTime() {
    const now = new Date();
    $('#datetime').text(
      now.toLocaleDateString() + ' ' +
      String(now.getHours()).padStart(2, '0') + ':' +
      String(now.getMinutes()).padStart(2, '0') + ':' +
      String(now.getSeconds()).padStart(2, '0')
    );
  }

  updateTime();
  setInterval(updateTime, 1000);

  if (counterStatus === 'emergency') {
    $('body').addClass('blurred');
    $('#emergencyOverlay').css('display', 'flex');
  } else if (counterStatus === 'lunch break') {
    $('body').addClass('blurred');
    $('#lunchOverlay').css('display', 'flex');
  }
  
  let lastAnnouncedToken = null;

function speak(text) {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-IN';
        utterance.rate = 0.9;
        speechSynthesis.speak(utterance);
    }
}

  function refreshTokenTables() {
    $.ajax({
        url: "<?= admin_url('token_system/public_view/get_token_tables_ajax/' . $counter->counter_id) ?>",
        method: "GET",
        dataType: "json",
        success: function (res) {
            $('#tokenTable1').html(res.table1);
            $('#tokenTable2').html(res.table2);

            if (res.serving_token && res.serving_name) {
                if (lastAnnouncedToken !== res.serving_token) {
                    lastAnnouncedToken = res.serving_token;
                    speak(`Token number ${res.serving_token}, ${res.serving_name}, please proceed to the doctor.`);
                }
            }
        }
    });
}

  setInterval(refreshTokenTables, 10000);
});
</script>
<script>
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        // Enter fullscreen
        const elem = document.documentElement; // or use a specific element
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { /* Safari */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE11 */
            elem.msRequestFullscreen();
        }
    } else {
        // Exit fullscreen
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) { /* Safari */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) { /* IE11 */
            document.msExitFullscreen();
        }
    }
}
</script>

</body>
</html>
