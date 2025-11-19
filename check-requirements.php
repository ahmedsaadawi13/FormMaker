<?php
/**
 * System Requirements Checker
 * Check if all requirements are met for FormMaker System
 */

$requirements = array();
$warnings = array();
$errors = array();

// Check PHP version
$phpVersion = phpversion();
$requirements['PHP Version'] = array(
    'required' => '7.0.33',
    'current' => $phpVersion,
    'status' => version_compare($phpVersion, '7.0.33', '>=') ? 'OK' : 'FAIL'
);

// Check PDO extension
$requirements['PDO Extension'] = array(
    'required' => 'Enabled',
    'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
    'status' => extension_loaded('pdo') ? 'OK' : 'FAIL'
);

// Check PDO MySQL driver
$requirements['PDO MySQL Driver'] = array(
    'required' => 'Enabled',
    'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
    'status' => extension_loaded('pdo_mysql') ? 'OK' : 'FAIL'
);

// Check cURL (optional - we have fallback)
$requirements['cURL Extension'] = array(
    'required' => 'Optional (has fallback)',
    'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
    'status' => extension_loaded('curl') ? 'OK' : 'WARNING'
);

// Check allow_url_fopen (required for OAuth fallback)
$allowUrlFopen = ini_get('allow_url_fopen');
$requirements['allow_url_fopen'] = array(
    'required' => 'On',
    'current' => $allowUrlFopen ? 'On' : 'Off',
    'status' => $allowUrlFopen ? 'OK' : 'FAIL'
);

// Check session support
$requirements['Session Support'] = array(
    'required' => 'Enabled',
    'current' => function_exists('session_start') ? 'Enabled' : 'Disabled',
    'status' => function_exists('session_start') ? 'OK' : 'FAIL'
);

// Check JSON extension
$requirements['JSON Extension'] = array(
    'required' => 'Enabled',
    'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
    'status' => extension_loaded('json') ? 'OK' : 'FAIL'
);

// Check database connection
try {
    require_once 'config.php';
    require_once 'includes/Database.php';
    $db = new Database();
    $requirements['Database Connection'] = array(
        'required' => 'Connected',
        'current' => 'Connected to ' . DB_NAME,
        'status' => 'OK'
    );
} catch (Exception $e) {
    $requirements['Database Connection'] = array(
        'required' => 'Connected',
        'current' => 'Failed: ' . $e->getMessage(),
        'status' => 'FAIL'
    );
}

// Check Google OAuth configuration
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || empty(GOOGLE_CLIENT_ID)) {
    $requirements['Google OAuth Config'] = array(
        'required' => 'Configured',
        'current' => 'Not configured',
        'status' => 'WARNING'
    );
    $warnings[] = 'Google OAuth credentials not configured in config.php';
} else {
    $requirements['Google OAuth Config'] = array(
        'required' => 'Configured',
        'current' => 'Configured',
        'status' => 'OK'
    );
}

// Count statuses
$okCount = 0;
$failCount = 0;
$warningCount = 0;
foreach ($requirements as $req) {
    if ($req['status'] === 'OK') $okCount++;
    if ($req['status'] === 'FAIL') $failCount++;
    if ($req['status'] === 'WARNING') $warningCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Requirements Check - FormMaker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .summary-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card h3 {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .summary-card .number {
            font-size: 2em;
            font-weight: bold;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
        }

        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-ok {
            background: #28a745;
            color: white;
        }

        .badge-fail {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 0 5px;
        }

        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>FormMaker System Requirements Check</h1>
        <p>This page checks if your server meets all requirements to run FormMaker.</p>

        <div class="summary">
            <div class="summary-card status-ok">
                <h3>Passed</h3>
                <div class="number"><?php echo $okCount; ?></div>
            </div>
            <div class="summary-card status-fail">
                <h3>Failed</h3>
                <div class="number"><?php echo $failCount; ?></div>
            </div>
            <div class="summary-card status-warning">
                <h3>Warnings</h3>
                <div class="number"><?php echo $warningCount; ?></div>
            </div>
        </div>

        <?php if ($failCount === 0 && $warningCount === 0): ?>
            <div class="info-box">
                <strong>✓ All requirements met!</strong> Your server is ready to run FormMaker System.
            </div>
        <?php elseif ($failCount > 0): ?>
            <div class="warning-box">
                <strong>⚠ Action Required!</strong> Some critical requirements are not met. Please fix the failed items below.
            </div>
        <?php else: ?>
            <div class="info-box">
                <strong>⚠ Minor Issues</strong> Your system will work, but some features may not be optimal.
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Required</th>
                    <th>Current</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $name => $req): ?>
                    <tr>
                        <td><strong><?php echo $name; ?></strong></td>
                        <td><?php echo $req['required']; ?></td>
                        <td><?php echo $req['current']; ?></td>
                        <td>
                            <?php if ($req['status'] === 'OK'): ?>
                                <span class="badge badge-ok">✓ OK</span>
                            <?php elseif ($req['status'] === 'FAIL'): ?>
                                <span class="badge badge-fail">✗ FAIL</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⚠ WARNING</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (count($warnings) > 0): ?>
            <div class="warning-box">
                <strong>Warnings:</strong>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <?php foreach ($warnings as $warning): ?>
                        <li><?php echo $warning; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($failCount === 0): ?>
                <a href="index.php" class="btn">Go to FormMaker →</a>
            <?php endif; ?>
            <a href="?refresh=1" class="btn" style="background: #6c757d;">Refresh Check</a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0; text-align: center; color: #666;">
            <p>PHP Version: <?php echo phpversion(); ?> | Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        </div>
    </div>
</body>
</html>
