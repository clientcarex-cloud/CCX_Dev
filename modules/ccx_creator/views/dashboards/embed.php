<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($dashboard['name'] ?? 'Dashboard'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2933;
            margin: 0;
            padding: 30px;
        }
        .ccx-embed {
            max-width: 1100px;
            margin: 0 auto;
        }
        .ccx-embed h2 {
            margin-bottom: 5px;
        }
        .ccx-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        .ccx-card {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.08);
        }
        .ccx-card h4 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ccx-stat-value {
            font-size: 32px;
            font-weight: bold;
        }
        .ccx-table {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.08);
        }
        .ccx-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .ccx-table th,
        .ccx-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        .ccx-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="ccx-embed">
        <h2><?php echo html_escape($dashboard['name'] ?? 'Dashboard'); ?></h2>
        <?php if (! empty($dashboard['description'])) : ?>
            <p><?php echo html_escape($dashboard['description']); ?></p>
        <?php endif; ?>

        <?php if (! empty($widgets)) : ?>
            <div class="ccx-grid">
                <?php foreach ($widgets as $widget) : ?>
                    <?php if ($widget['type'] === 'stat') : ?>
                        <div class="ccx-card" style="border-top:4px solid <?php echo html_escape($widget['color']); ?>">
                            <h4><?php echo html_escape($widget['title']); ?></h4>
                            <div class="ccx-stat-value"><?php echo html_escape($widget['value']); ?></div>
                            <div class="text-muted" style="margin-top:6px;font-size:13px;"><?php echo ucfirst(html_escape($widget['metric'])); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php foreach ($widgets as $widget) : ?>
                <?php if ($widget['type'] === 'table') : ?>
                    <div class="ccx-table">
                        <h4><?php echo html_escape($widget['title']); ?></h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($widget['rows'] as $row) : ?>
                                    <tr>
                                        <td>#<?php echo (int) $row['id']; ?></td>
                                        <td><?php echo html_escape(ucfirst($row['status'])); ?></td>
                                        <td><?php echo html_escape($row['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($widget['rows'])) : ?>
                                    <tr>
                                        <td colspan="3" class="text-muted">No records.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-muted">Nothing to show yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
