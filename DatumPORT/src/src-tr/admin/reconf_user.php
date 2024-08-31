<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

// Yönetici IP'si kontrolü
if ($ADMIN_IP == "::1") {
    include("DB_CONNECT.php");

    // Kullanıcı silme işlemi
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

    // Kullanıcı bilgilerini güncelleme işlemi
    if (isset($_POST["register_button_html"])) {
        $register_username = $_POST["register_username_html"];
        $register_role_html = $_POST["register_role_html"];
        $register_password = $_POST["register_password_html"];
        $PASSWORD_SECURE = password_hash($register_password, PASSWORD_DEFAULT);

        $register_username_length = strlen($register_username);
        $register_password_length = strlen($register_password);

        // Kullanıcı adı kontrol fonksiyonu
        function username_QR($connectC, $username) {
            $stmt = $connectC->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username");
            $stmt->execute(array(":username" => $username));
            return $stmt->fetchColumn() > 0;
        }

        // Şifre uzunluk kontrolü ve kullanıcı güncelleme işlemi
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
                <p>Şifre 8 karakterden kısa ve 25 karakterden uzun olmamalıdır</p>
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
                <p>Kullanıcı bulunamadı</p>
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
                    <p>Bilgiler başarıyla güncellendi. Kullanıcı adı: '$register_username' &nbsp <img src='../images/loading_nw.gif' style='top:2.5%; position:fixed;'></p>
                </div>
                ");
                header("refresh: 3; url=reconf_user.php");
            }
        }
    }
} else {
    // Yetkisiz erişim
    header("location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DP - Kullanıcı Yönetimi Paneli</title>
    <link rel="stylesheet" href="../styles/reconf_user_style.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img class="register-page-images" src="../images/DatumPort_ntxt.png">
            <h1>Kullanıcı Yönetim Paneli</h1>
            <form action="reconf_user.php" method="POST">
                <input type="text" name="register_username_html" placeholder="Bilgileri Değiştirilecek Hesabın Kullanıcı Adı" required>
                <input type="password" name="register_password_html" placeholder="Yeni Şifre" required>
                <br>
                <div class="in-div-role-select">
                    <h3>Yeni Rol</h3>
                    <select class="select-role" name="register_role_html" placeholder="Rol Seç" required> &nbsp;
                        <option value="Bilgi Ekleme">Bilgi Ekleme Rolü</option>
                        <option value="Bilgi Onaylama">Bilgi Onaylama Rolü</option>
                        <option value="Yönetici">Yönetici Rolü</option>
                    </select>
                    <br>
                    <br>
                </div>
                <br>
                <button type="submit" name="register_button_html" onclick="register_button_html_fjs()">Bilgileri Değiştir</button>
            </form>
            <form action="reconf_user.php" method="POST">
                <input class="delete_user_textbox" type="text" name="delete_user" placeholder="Silinecek Hesabın Kullanıcı Adı" required>
                <button type="submit" name="delete_user_button" class="delete_user_button">Kullanıcıyı Sil</button>
            </form>
        </div>
    </div>
</body>
</html>
