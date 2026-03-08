<?php
/*
 * setup.php — ONE-TIME SETUP SCRIPT
 * ----------------------------------
 * Run this file ONCE via browser, then DELETE IT immediately.
 * URL: http://192.168.100.96:8080/setup.php
 *
 * What it does:
 *   1. Updates server name to "MU Da Nang" in DTweb_settings
 *   2. Sets admin IP to % (wildcard) so you can access ?p=general from any IP
 *   3. Ensures the Aion theme is active
 */
if (basename(__FILE__) !== basename($_SERVER['PHP_SELF'])) { exit(); }

require $_SERVER['DOCUMENT_ROOT'] . "/configs/config.php";

$results = [];
$errors  = [];

// 1. Update server name
$name_b64  = base64_encode("MU Da Nang");
$title_b64 = base64_encode("MU Da Nang");

$r1 = mssql_query("UPDATE [DTweb_settings] SET
    [title]       = '$title_b64',
    [server_name] = '$name_b64'
");
if ($r1) {
    $results[] = "Server name updated to: MU Da Nang";
} else {
    $errors[] = "Failed to update server name";
}

// 2. Fix admin IP to wildcard so any browser can access ?p=general
$r2 = mssql_query("UPDATE [DTweb_GM_Accounts] SET [ip] = '%' WHERE [name] = 'test'");
if ($r2) {
    $results[] = "Admin IP set to wildcard (%) — any IP can now access the admin panel";
} else {
    $errors[] = "Failed to update admin IP";
}

// 3. Ensure Aion theme is active
$r3 = mssql_query("UPDATE [DTweb_settings] SET [theme] = 'Aion'");
if ($r3) {
    $results[] = "Theme confirmed: Aion";
} else {
    $errors[] = "Failed to set theme";
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MU Da Nang — Setup</title>
    <style>
        body { font-family: monospace; background: #0d0804; color: #c6884c; padding: 40px; }
        h2   { color: #ffcc88; }
        .ok  { color: #00c8a8; }
        .err { color: #ff4444; }
        .warn { color: #ffcc00; margin-top: 30px; padding: 14px; border: 1px solid #ffcc00; border-radius: 4px; }
    </style>
</head>
<body>
<h2>MU Da Nang — Setup Script</h2>
<p>Your IP: <b><?= htmlspecialchars($_SERVER['REMOTE_ADDR']) ?></b></p>
<hr style="border-color:#4a2010;">

<?php foreach ($results as $msg): ?>
    <p class="ok">✓ <?= htmlspecialchars($msg) ?></p>
<?php endforeach; ?>

<?php foreach ($errors as $err): ?>
    <p class="err">✗ <?= htmlspecialchars($err) ?></p>
<?php endforeach; ?>

<div class="warn">
    ⚠️  <strong>DELETE THIS FILE NOW!</strong><br><br>
    This script has no authentication — anyone who accesses it can reset your admin settings.<br>
    Delete <code>Web/setup.php</code> from your server immediately after this page loads.
</div>

<p style="margin-top:30px;">
    Next steps:<br>
    1. Delete <code>setup.php</code><br>
    2. Log in with account: <b>test</b> / password: <b>test</b><br>
    3. Go to <a style="color:#00c8a8" href="?p=general">?p=general</a> to change settings in the admin panel<br>
    4. Change your admin password immediately
</p>
</body>
</html>
