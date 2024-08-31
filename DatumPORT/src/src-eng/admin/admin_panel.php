<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

if ($ADMIN_IP !== "::1") {
    header("Location: ../main_page.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datum ~ Port ADMIN</title>
    <link rel="stylesheet" href="../styles/admin_main_panel_style-1.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="../images/DATUM_admin_nonbg.png" alt="Logo" class="logo">
        </div>
    </header>
    <main>
        <div class="button-container">
            <a href="insert_user.php" class="button">Add User</a>
            <a href="reconf_user.php" class="button">Edit User</a>
            <a href="#" class="button">Change System</a>
            <a href="logs.php" class="button">View Logs</a>
            <a href="#section5" class="button">-</a>
            <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=datumport_msql" class="button">Database</a>
        </div>
    </main>
</body>
</html>


