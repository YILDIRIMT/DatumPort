<?php
include("DB_CONNECT.php"); 
include("lib/logs_function.php"); 
include("lib/captcha_offline.php");

session_start();

// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION["KEY"]) && isset($_SESSION["USERNAME"])) { 
  header("location: main_page.php");
  exit;
}

try { 
    // CAPTCHA verisini veritabanına ekleme
    $stmt = $connect->prepare("INSERT INTO captcha (captcha_number, image_data) VALUES (:captcha_number, :image_data)");
    $stmt->bindParam(':captcha_number', $captcha_number);
    $stmt->bindParam(':image_data', $image_data, PDO::PARAM_LOB);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Veritabanı hatası. Yöneticinize başvurun";
}

$sql = "DELETE FROM captcha WHERE timestamp < NOW() - INTERVAL 5 MINUTE";
try {
    $stmt_captcha_delete = $connect->prepare($sql);

    $stmt_captcha_delete->execute();
} catch (PDOException $e) {
}

// CAPTCHA görüntüsünü al
try {
$get_image = $connect->prepare("SELECT image_data FROM captcha WHERE captcha_number = :captcha_number");
$get_image->bindParam(":captcha_number", $captcha_number);
$get_image->execute();
$image_data = $get_image->fetchColumn();
} catch (PDOException $e) {
}     

// Kullanıcı doğrulama fonksiyonu
function verify_user($connect_f, $username, $password) {
  $stmt = $connect_f->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username"); 
  $stmt->execute(array(":username" => $username)); 
  $content = $stmt->fetch(); 

  if ($content && password_verify($password, $content["password_dataB"])) { 
    return array($content["username_dataB"], $content["role_dataB"]);
  } else {
    return false;
  }
}

if (isset($_POST["post-e"])) {
  
  $input_captcha = $_POST["captcha"];
  // CAPTCHA doğruluğunu kontrol et
  $query_image = $connect->prepare("SELECT captcha_number FROM captcha WHERE captcha_number = :captcha_number ");
  $query_image->bindParam(":captcha_number", $input_captcha);
  $query_image->execute();
  $captcha_status = $query_image->fetchColumn();

  if ($captcha_status == 0) {
      // CAPTCHA yanlışsa sil ve yeniden yönlendir
      $delete_captcha = $connect->prepare("DELETE FROM captcha WHERE captcha_number = :captcha_number");
      $delete_captcha->bindParam(":captcha_number", $captcha_number);
      $delete_captcha->execute();

      header("location: index.php");
      exit;
  } 

  // CAPTCHA doğruysa sil
  $delete_captcha = $connect->prepare("DELETE FROM captcha WHERE captcha_number = :captcha_number");
  $delete_captcha->bindParam(":captcha_number", $input_captcha);
  $delete_captcha->execute();

  // Kullanıcı doğrulama
  $username_q = $_POST["login_username_html"]; 
  $password_q = $_POST["login_password_html"];
  $username_session = verify_user($connect, $username_q, $password_q);
  
  if ($username_session) {
    session_start();

    $_SESSION["USERNAME"] = $username_session[0]; 
    $_SESSION["ROLE"] = $username_session[1]; 
    $_SESSION["KEY"] = 1;

    // Kullanıcı giriş bilgilerini logla
    $USERn = $_SESSION["USERNAME"]; 
    $USER_IP_ADDR = $_SERVER["REMOTE_ADDR"];
    $USER_MACHINE_NAME = gethostbyaddr($_SERVER['REMOTE_ADDR']); 

    logs($connect, "Oturum başlattı", $USERn, $USER_IP_ADDR, $USER_MACHINE_NAME);

    header("location: main_page.php");
  } else { 
    // Hatalı giriş
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
        <p>Yanlış kullanıcı adı veya şifre. Tekrar deneyin veya şifrenizi unuttuysanız yöneticinize başvurun.</p>
    </div>
    ");
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datum ~ Port LOGIN</title>
    <link rel="stylesheet" href="styles/index_main_style_tp-1.css">
    <link rel="icon" href="favicon.png" type="image/x-icon"/>
</head>
<body>
    <div class="container">
        <div class="phone-preview">
            <img src="images/DatumPort_nonbg.png" alt="Datum Port Logo">
        </div>
        <div class="form-container">
            <h1>Sisteme Giriş</h1>
            <form action="index.php" method="POST">
                <input type="text" name="login_username_html" placeholder="Kullanıcı Adı" required>
                <input type="password" name="login_password_html" placeholder="Şifre" required>
                <?php echo '<img style="height:50px; width:160px; position:relative;" src="data:image/jpeg;base64,' . base64_encode($image_data) . '">'; ?>
                <input type="text" name="captcha" placeholder="Buraya Bu Kutunun Üstündeki Kodu Giriniz" required>
                <button name="post-e" type="submit">Giriş</button>
            </form>
            <?php if (isset($login_error)) { ?>
                <div class="error-message">
                    <p>Yanlış kullanıcı adı veya şifre. Tekrar deneyin veya şifrenizi unuttuysanız yöneticinize başvurun.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>