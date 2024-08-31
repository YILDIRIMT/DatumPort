<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

// Check if the request is coming from localhost
if ($ADMIN_IP == "::1") {
    include("DB_CONNECT.php");

    // User deletion process
    if (isset($_POST["delete_user_button"])) {
        $delete_textbox = $_POST["delete_user"];
        $delete_sql = $connect->prepare("DELETE FROM usrdta001 WHERE username_dataB = :username_dataB");
        $delete_sql->execute(array(":username_dataB" => $delete_textbox));

        if ($delete_sql) {
            echo("
            <div style='
            width: 100%;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            '>
                <p>Kullanıcı başarıyla silindi &nbsp <img src='../images/loading_nw.gif' style='top:2.5%; position:fixed;'></p>
            </div>
            ");
            header("refresh: 2; url=reconf_user.php");
        }
    }

    // Update user information process**
    if (isset($_POST["register_button_html"])) {
        $register_username = $_POST["register_username_html"];
        $register_role_html = $_POST["register_role_html"];
        $register_password = $_POST["register_password_html"];
        $PASSWORD_SECURE = password_hash($register_password, PASSWORD_DEFAULT);

        $register_username_length = strlen($register_username);
        $register_password_length = strlen($register_password);

        // Username validation function
        function username_QR($connectC, $username) {
            $stmt = $connectC->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username");
            $stmt->execute(array(":username" => $username));
            return $stmt->fetchColumn() > 0;
        }

        // Password length check and user update process
        if ($register_password_length < 8 || $register_password_length > 25) {
            echo("
            <div style='
            width: 100%;
            background-color: red;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            '>
                <p>Password must be between 8 and 25 characters long</p>
            </div>
            ");
        } elseif (username_QR($connect, $register_username) == 0) {
            echo("
            <div style='
            width: 100%;
            background-color: red;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            '>
                <p>User Not Found</p>
            </div>
            ");
        } elseif (username_QR($connect, $register_username)) {
            $stmt = $connect->prepare("UPDATE usrdta001 SET password_dataB = :password, role_dataB = :role WHERE username_dataB = :username");
            $stmt->execute(array(
                ":username" => $register_username,
                ":password" => $PASSWORD_SECURE,
                ":role" => $register_role_html
            ));
            if ($stmt) {
                echo("
                <div style='
                width: 100%;
                background-color: #4CAF50;
                color: white;
                text-align: center;
                padding: 10px 0;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                '>
                    <p>Information has been successfully updated. Username: '$register_username' &nbsp <img src='../images/loading_nw.gif' style='top:2.5%; position:fixed;'></p>
                </div>
                ");
                header("refresh: 3; url=reconf_user.php");
            }
        }
    }
} else {
    // Unauthorized access
    header("location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYS - User Management Panel</title>
    <link rel="stylesheet" href="../styles/reconf_user_style.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img class="register-page-images" src="../images/DatumPort_ntxt.png">
            <h1>User Management Panel</h1>
            <form action="reconf_user.php" method="POST">
                <input type="text" name="register_username_html" placeholder="Username of the Account to Modify" required>
                <input type="password" name="register_password_html" placeholder="New Password" required>
                <br>
                <div class="in-div-role-select">
                    <h3>New Role</h3>
                    <select class="select-role" name="register_role_html" placeholder="Select Role" required> &nbsp;
                        <option value="Bilgi Ekleme">Information Addition Role</option>
                        <option value="Bilgi Onaylama">Information Approval Role</option>
                        <option value="Yönetici">Administrator Role</option>
                    </select>
                    <br>
                    <br>
                </div>
                <br>
                <button type="submit" name="register_button_html" onclick="register_button_html_fjs()">Change Information</button>
            </form>
            <form action="reconf_user.php" method="POST">
                <input class="delete_user_textbox" type="text" name="delete_user" placeholder="Username of the Account to Delete" required>
                <button type="submit" name="delete_user_button" class="delete_user_button">Delete User</button>
            </form>
        </div>
    </div>
</body>
</html>

