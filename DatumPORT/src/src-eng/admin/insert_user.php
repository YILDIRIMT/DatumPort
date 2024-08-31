<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

// Check if the request is coming from localhost
if ($ADMIN_IP == "::1") {
    include("DB_CONNECT.php");

    if (isset($_POST["register_button_html"])) {

        // Retrieve POST values and hash the password
        $register_username = $_POST["register_username_html"];
        $register_role_html = $_POST["register_role_html"];
        $register_password = $_POST["register_password_html"];
        $PASSWORD_SECURE = password_hash($register_password, PASSWORD_DEFAULT);

        $register_username_length = strlen($register_username);
        $register_password_length = strlen($register_password);

        // Function to check if the username exists in the database
        function username_QR($connectC, $username) {
            $stmt = $connectC->prepare("SELECT COUNT(*) FROM usrdta001 WHERE username_dataB = :username");
            $stmt->execute(array(":username" => $username));
            return $stmt->fetchColumn() > 0;
        }

        // Check the username and perform login validations
        if (username_QR($connect, $register_username)) {
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
            <p>This username is already in use</p>
            </div>
            ");
        } elseif ($register_username_length > 20 || $register_username_length < 5) {
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
                <p>Username must be at least 5 characters and no more than 20 characters long</p>
            </div>
            ");
        } elseif ($register_password_length < 8 || $register_password_length > 25) {
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
                <p>User password must be at least 8 characters and no more than 25 characters long</p>
            </div>
            ");
        } else {
            // Default profile picture path and data
            $default_image_path = "../images/profile.png";
            $image_data = file_get_contents($default_image_path);

            // Prepare the SQL query to add the user to the database
            $add_user = $connect->prepare("INSERT INTO usrdta001 (username_dataB, role_dataB, password_dataB, edit_status, image_data)
                                           VALUES (:username_dataB, :role_dataB, :password_dataB, :edit_status, :image_data)");

            $add_user->bindParam(':username_dataB', $register_username);
            $add_user->bindParam(':role_dataB', $register_role_html);
            $add_user->bindParam(':password_dataB', $PASSWORD_SECURE);
            $add_user->bindParam(':edit_status', "0");
            $add_user->bindParam(':image_data', $image_data, PDO::PARAM_LOB);

            $add_user->execute();

            if ($add_user) {
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
                    <p>User creation successful. Added user: '$register_username' &nbsp <img src='../images/loading_nw.gif' style='top:2.5%; position:fixed;'></p>
                </div>
                ");
                header("refresh: 4; url=insert_user.php");
            }
        }
    }
} else {
    // Redirect in case of unauthorized access
    header("location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DP - User Creation Panel</title>
    <link rel="stylesheet" href="../styles/insert_user_style.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img class="register-page-images" src="../images/DatumPort_ntxt.png" alt="DatumPort">
            <h1>User Creation Panel</h1>
            <form action="insert_user.php" method="POST">
                <input type="text" name="register_username_html" placeholder="Username" required>
                <input type="password" name="register_password_html" placeholder="Password" required>
                <br>
                <div class="in-div-role-select">
                  <h3>Role Selection</h3>
                  <select class="select-role" name="register_role_html" placeholder="Select Role" required> &nbsp;
                    <option value="Bilgi Ekleme">Information Entry Role</option>
                    <option value="Bilgi Onaylama">Information Approval Role</option>
                    <option value="Yönetici">Administrator Role</option>
                  </select>
                  <br>
                  <br>
                </div>
                <br>
                <button type="submit" name="register_button_html" onclick="register_button_html_fjs()">Add User</button>
            </form>
        </div>
    </div>
</body>
</html>
