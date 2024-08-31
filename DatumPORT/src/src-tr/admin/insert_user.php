<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

// İsteğin localhost'tan gelip gelmediğini kontrol et
if ($ADMIN_IP == "::1") {
    include("DB_CONNECT.php");

    if (isset($_POST["register_button_html"])) {

        // POST değerlerini al ve şifreyi hashle
        $register_username = $_POST["register_username_html"];
        $register_role_html = $_POST["register_role_html"];
        $register_password = $_POST["register_password_html"];
        $PASSWORD_SECURE = password_hash($register_password, PASSWORD_DEFAULT);

        $register_username_length = strlen($register_username);
        $register_password_length = strlen($register_password);

        // Kullanıcı adının veritabanında olup olmadığını kontrol eden fonksiyon
        function username_QR($connectC, $username) {
            $stmt = $connectC->prepare("SELECT COUNT(*) FROM usrdta001 WHERE username_dataB = :username");
            $stmt->execute(array(":username" => $username));
            return $stmt->fetchColumn() > 0;
        }

        // Kullanıcı adını kontrol et ve giriş doğrulamalarını yap
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
            <p>Bu kullanıcı adı kullanımda</p>
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
                <p>Kullanıcı ismi 5 karakterden küçük 20 karakterden büyük olmamalı</p>
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
                <p>Kullanıcı şifresi 8 karakterden küçük 25 karakterden büyük olmamalı</p>
            </div>
            ");
        } else {
            // Varsayılan profil resmi yolu ve verileri
            $default_image_path = "../images/profile.png";
            $image_data = file_get_contents($default_image_path);

            // Kullanıcıyı veritabanına eklemek için SQL sorgusunu hazırla
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
                    <p>Kullanıcı oluşturma başarılı. Eklenen kullanıcı: '$register_username' &nbsp <img src='../images/loading_nw.gif' style='top:2.5%; position:fixed;'></p>
                </div>
                ");
                header("refresh: 4; url=insert_user.php");
            }
        }
    }
} else {
    // Yetkisiz erişim durumunda yönlendir
    header("location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DP - Kullanıcı Oluşturma Paneli</title>
    <link rel="stylesheet" href="../styles/insert_user_style.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img class="register-page-images" src="../images/DatumPort_ntxt.png">
            <h1>Kullanıcı Oluşturma Paneli</h1>
            <form action="insert_user.php" method="POST">
                <input type="text" name="register_username_html" placeholder="Kullanıcı Adı" required>
                <input type="password" name="register_password_html" placeholder="Şifre" required>
                <br>
                <div class="in-div-role-select">
                  <h3>Rol Seçimi</h3>
                  <select class="select-role" name="register_role_html" placeholder="Rol Seç" required> &nbsp;
                    <option value="Bilgi Ekleme">Bilgi Ekleme Rolü</option>
                    <option value="Bilgi Onaylama">Bilgi Onaylama Rolü</option>
                    <option value="Yönetici">Yönetici Rolü</option>
                  </select>
                  <br>
                  <br>
                </div>
                <br>
                <button type="submit" name="register_button_html" onclick="register_button_html_fjs()">Kullanıcı Ekle</button>
            </form>
        </div>
    </div>
</body>
</html>