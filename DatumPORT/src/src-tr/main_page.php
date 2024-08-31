<?php
session_start();
include("DB_CONNECT.php");
include("lib/logs_function.php");
include("lib/user_delete_query.php");

$Erole = $_SESSION["ROLE"];

if ($_SESSION["USERNAME"] && $_SESSION["KEY"] == 1) { // SESSION varsa
  $USERn = $_SESSION["USERNAME"];
  $USER_IP_ADDR = $_SERVER["REMOTE_ADDR"];
  $USER_MACHINE_NAME = gethostname();

  // urndta001 tablosundaki ID değerlerini sıfırla ve sıralama yap
  $sql_update = "SET @num := 0;
                UPDATE urndta001 SET id = @num := (@num+1);
                ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
  $connect->exec($sql_update);

  // vrfurn001 tablosundaki ID değerlerini sıfırla ve sıralama yap
  $sql_update = "SET @num := 0;
                UPDATE vrfurn001 SET id = @num := (@num+1);
                ALTER TABLE vrfurn001 AUTO_INCREMENT = 1;";
  $connect->exec($sql_update);

  if (isset($_POST["insert_data"])) { // POST varsa
    if ($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // Kullanıcının rolü
      $urun_adi = $_POST["urun"];
      $urun_adet = $_POST["adet"];
      $urun_kodu = $_POST["urun-kodu"];

      // Veriler boş değilse
      if(!empty($urun_adi) && !empty($urun_adet) && !empty($urun_kodu)) {
        // Veriyi veritabanına ekle
        $stmt = $connect->prepare("INSERT INTO urndta001 SET
        urun = :urun,
        adet = :adet,
        urun_kodu = :urun_kodu,
        urn_sender = :urn_sender");
        $stmt->execute(array(
          "urun" => $urun_adi,
          "adet" => $urun_adet,
          "urun_kodu" => $urun_kodu,
          "urn_sender" => $USERn
        ));
        logs($connect,"Ürün Ekledi / Ürün Adı = $urun_adi Ürün Adet = $urun_adet Ürün Kod = $urun_kodu",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
        header("location: main_page.php");
      } else {
        echo "<script type='text/javascript'>
        alert('Verileri kontrol edin ve hala hata devam ederse yöneticinize başvurun');
        </script>";
      }
    } else {
      echo "
      <script>
      alert('Ürün ekleme işlemi yapmak için gerekli izniniz bulunamadı. Yöneticinize başvurun');
      window.location.href = 'main_page.php';
      </script>
      ";
    }
  }

  if (isset($_POST["log_out"])) { // POST varsa
    logs($connect,"Oturum kapatıldı",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME); // Oturum kapatma işlemi için log ekle

    session_destroy();
    header("location: index.php");
    exit;
  }
  echo ".";
} else { // POST yoksa
    header("location: index.php");
    exit;
}

#FUNCTIONS
function get_search_data($value,$connect_gsd,$parameter,$USERn_function){
   $SELECT_SEARCH = $connect_gsd->prepare("SELECT * FROM urndta001 WHERE $parameter = :value");
   $SELECT_SEARCH->execute(array(":value" => $value));
   $SEARCH_DATA = $SELECT_SEARCH->fetch();
   
   if($SEARCH_DATA){
        echo "<h5 style='color:green;'>Onay Bekleyen Ürün Bulundu :</h5>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $SEARCH_DATA["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $SEARCH_DATA['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $SEARCH_DATA['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $SEARCH_DATA["urun_kodu"] . "<br>";
        $update = $connect_gsd->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $update->execute(array(":edit_status" => $SEARCH_DATA["id"],":username_dataB" => $USERn_function));

   } else{
       echo "<h3 style='color:red;'>Aranan veri bulunamadı 'lütfen Aranan Tablo' ve 'Arama Tipi' girdilerini kontrol edin. Hata devam ederse yöneticinize başvurun.</h3>";
   }
}

function get_search_data_vfd($value,$connect_gsd,$parameter,$USERn_function){
   $SELECT_SEARCH = $connect_gsd->prepare("SELECT * FROM vrfurn001 WHERE $parameter = :value");
   $SELECT_SEARCH->execute(array(":value" => $value));
   $SEARCH_DATA = $SELECT_SEARCH->fetch();
   
   if($SEARCH_DATA){
        echo "<h5 style='color:green;'>Onaylanmış Ürün Bulundu :</h5>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $SEARCH_DATA["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $SEARCH_DATA['urun_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $SEARCH_DATA['adet_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $SEARCH_DATA["urun_kodu_verify"] . "<br>";
        $update = $connect_gsd->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $update->execute(array(":edit_status" => $SEARCH_DATA["id"],":username_dataB" => $USERn_function));
   } else{
       echo "<h3 style='color:red;'>Aranan veri bulunamadı 'lütfen Aranan Tablo' ve 'Arama Tipi' girdilerini kontrol edin. Hata devam ederse yöneticinize başvurun.</h3>";
   }
}

function edit_data($connectf,$edit_data_fc,$value,$id){
    $STMT_EDIT = $connectf->prepare("UPDATE urndta001 SET $edit_data_fc = :value WHERE id = :id");
    $STMT_EDIT->execute(array(":value" => $value,
                              ":id" => $id));
    global $USERn,$USER_IP_ADDR,$USER_MACHINE_NAME;
    if($STMT_EDIT){
        $LOG_INSERT = $connectf->prepare("INSERT INTO usrlogs001 (action, kullanici_ismi, ip_address, machine_name) VALUES (:action, :kullanici_ismi, :ip_address, :machine_name)");
        $LOG_INSERT->execute(array(":action" => "$id ID Değerine sahip olan verinin $edit_data_fc değeri $value olarak değiştirildi",
                                   ":kullanici_ismi" => $USERn,
                                   ":ip_address" => $USER_IP_ADDR,
                                   ":machine_name" => $USER_MACHINE_NAME));
        echo "<script>
                alert('Ürün bilgisi değişti');
              </script>";
    } else{
        echo "<script>
                alert('İşlem gerçekleştirilirken bir hata meydana geldi. Lütfen yöneticinize başvurun');
              </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Datum ~ Port SYSTEM</title>
     <link rel="icon" href="favicon.png" type="image/x-icon"/>
     <link rel="stylesheet" href="styles/main_page_style_1_1.css">
 </head>
 <body bgcolor="#f2f2f2">
  <div class="fixed-bar">
      <form action="main_page.php" method="post">
       <button class="log_out_button" type="submit" name="log_out"><b class="in-log-b">Çıkış Yap</b></button>
      </form>
      <?php
      $Eusername = $_SESSION["USERNAME"];
      $Erole = $_SESSION["ROLE"];
      $get_image = $connect->prepare("SELECT image_data FROM usrdta001 WHERE username_dataB = :user_id");
      $get_image->bindParam(':user_id', $Eusername);
      $get_image->execute();
      $image_data = $get_image->fetchColumn();

      // Profil resmini ve kullanıcı bilgilerini ekrana yazdır
      echo '&nbsp;&nbsp;<img style="height:100%; top:1; position:absolute;" src="data:image/jpeg;base64,' . base64_encode($image_data) . '" alt="Profil Resmi"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
      echo "<b style='position:absolute; top:1;'>&nbsp; Kullanıcı : $Eusername</b>";
      echo "<b style='position:absolute; top:1; left:240;'>&nbsp; &nbsp; Yetki : $Erole</b> &nbsp; &nbsp; &nbsp; ";
      ?>
  </div>
  <div style="height: auto; padding: 20px;">
    <br>
    <br>
    <div class="container">
    <div class="box">
        <form action="#" method="POST" id="urun-form">
        <label for="urun">Ürün Adı:</label>
        <input type="text" id="urun" name="urun" required>
        <label for="adet">Adet:</label>
        <input type="number" id="adet" name="adet" min="1" required>
        <label for="urun-kodu">Ürün Kodu:</label>
        <input type="text" id="urun-kodu" name="urun-kodu" required>
        <button name="insert_data" type="submit">Kaydet</button>
    </form>
    </div>
    <div class="box">
        <form action="#" method="POST" id="urun-form">
        <label for="urun_search">Ürün Adı:</label>
        <input type="text" id="urun_search" name="urun_search_name">
        <label for="urun_kodu_search">Ürün Kodu:</label>
        <input type="text" id="urun_kodu_search" name="urun_kodu_search_name">

        <label for="urun_ID_search">Ürün ID:</label>
        <input type="text" id="urun_ID_search" name="urun_ID_search_name">

        <label for="select_search_st">Aranan Tablo:</label>
        <select class="select_search" for="select_search_st" name="search_src_st_name" placeholder="Arama Tipi Seç" required> &nbsp;
            <option value="vf_data">Onay Bekleyen Verileri Ara</option>
            <option value="vfd_data">Onaylanmış Verileri Ara</option>
        </select>

        <label for="select_search_id">Arama Tipi:</label>
        <select class="select_search" for="select_search_id" name="search_src" placeholder="Arama Tipi Seç" required> &nbsp;
            <option value="urun_name">Ürün İsmi Odaklı Arama</option>
            <option value="urun_code">Ürün Kodu Odaklı Arama</option>
            <option value="urun_id">Ürün ID Odaklı Arama</option>
        </select>
        <button name="search_data" type="submit">Veri Ara</button>
    </form>
    </div>
    <div class="box">
    <?php

    #DATA EDIT
    if (isset($_POST["edit"])){
        $EDIT_DATA = $connect->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $EDIT_DATA->execute(array(":edit_status" => $_POST["edit"], ":username_dataB" => $USERn));

        echo "<h5 style='color:green;'>Değiştirilecek Veri :</h5>";

        $EDIT_LY = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
        $EDIT_LY->execute(array(":id" => $_POST['edit']));
        $EDIT_LY_FT = $EDIT_LY->fetch();

        echo "<span style='font-weight: bold;'>ID:</span> " . $EDIT_LY_FT["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $EDIT_LY_FT['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $EDIT_LY_FT['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $EDIT_LY_FT["urun_kodu"] . "<br><br>";

        echo '<form action="main_page.php" method="POST">
              <label for="edit_select_st">Değiştirilecek Veri Türü:</label>
              <select class="select_search" for="edit_select_st" name="edit_select_st_name" placeholder="Arama Tipi Seç" required>
                <option value="urun_name_edit">Ürün İsmini Değiştir</option>
                <option value="urun_code_edit">Ürün Kodunu Değiştir</option>
                <option value="urun_ad_edit">Ürün Adetini Değiştir</option>
              </select>
              <input type="text" id="urun_ID_search" name="urun_edit_name_in">
              <button name="edit_data_ver" type="submit">Veriyi Değiştir</button>
              </form>';
    }

    if(isset($_POST["edit_data_ver"])){
        $SELECT_EDIT_STATUS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
        $SELECT_EDIT_STATUS->execute(array(":username_dataB" => $USERn));
        $SELECT_EDIT_STATUS_FETCH = $SELECT_EDIT_STATUS->fetch();

        if($_POST["edit_select_st_name"] == "urun_name_edit"){
            edit_data($connect,"urun",$_POST["urun_edit_name_in"],$SELECT_EDIT_STATUS_FETCH["edit_status"]);
        }else if($_POST["edit_select_st_name"] == "urun_code_edit"){
            edit_data($connect,"urun_kodu",$_POST["urun_edit_name_in"],$SELECT_EDIT_STATUS_FETCH["edit_status"]);
        }else if($_POST["edit_select_st_name"] == "urun_ad_edit"){
            edit_data($connect,"adet",$_POST["urun_edit_name_in"],$SELECT_EDIT_STATUS_FETCH["edit_status"]);
        }else{
             echo "<h3 style='color:red;'>Beklenmedik bir durum ile karşılaşıldı. Tekrar deneyin ve hata devam ederse yöneticinize başvurun.</h3>";
        }
    }

    #DATA SEARCH
    if(isset($_POST["search_data"])){
        if($_POST["search_src_st_name"] == "vfd_data"){
                    if($_POST["search_src"] == "urun_name"){
                            $VALUE_INP = $_POST["urun_search_name"];
                            get_search_data_vfd($VALUE_INP,$connect,"urun_verify",$USERn);
                            }
                    if($_POST["search_src"] == "urun_code"){
                            $VALUE_INP = $_POST["urun_kodu_search_name"];
                            get_search_data_vfd($VALUE_INP,$connect,"urun_kodu_verify",$USERn);
                            }
                    if($_POST["search_src"] == "urun_id"){
                            $VALUE_INP = $_POST["urun_ID_search_name"];
                            get_search_data_vfd($VALUE_INP,$connect,"id",$USERn);
                            }
                    $SELECT_EDIT_STATUS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
                    $SELECT_EDIT_STATUS->execute(array(":username_dataB" => $USERn));
                    $SELECT_EDIT_STATUS_FETCH = $SELECT_EDIT_STATUS->fetch();
                    $EDIT_STATUS_C = $SELECT_EDIT_STATUS_FETCH["edit_status"];
                    echo "<br><form action='main_page.php' method='POST'><button name='refr_search' style='padding: 8px 15px; margin-top:0px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;' value='$EDIT_STATUS_C'>Geri Gönder</button></form>";
                    logs($connect,"Veri araması yapıldı veri ID = $EDIT_STATUS_C",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
        } else if ($_POST["search_src_st_name"] == "vf_data"){
                    if($_POST["search_src"] == "urun_name"){
                            $VALUE_INP = $_POST["urun_search_name"];
                            get_search_data($VALUE_INP,$connect,"urun",$USERn);
                            }
                    if($_POST["search_src"] == "urun_code"){
                            $VALUE_INP = $_POST["urun_kodu_search_name"];
                            get_search_data($VALUE_INP,$connect,"urun_kodu",$USERn);
                            }
                    if($_POST["search_src"] == "urun_id"){
                            $VALUE_INP = $_POST["urun_ID_search_name"];
                            get_search_data($VALUE_INP,$connect,"id",$USERn);
                            }
                    $SELECT_EDIT_STATUS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
                    $SELECT_EDIT_STATUS->execute(array(":username_dataB" => $USERn));
                    $SELECT_EDIT_STATUS_FETCH = $SELECT_EDIT_STATUS->fetch();
                    $EDIT_STATUS_C = $SELECT_EDIT_STATUS_FETCH["edit_status"];
                    echo '<br><form action="main_page.php" method="POST"><button name="verify_search" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5cb85c; color: white;" value="">Onayla</button></form>';
                    echo "<form action='main_page.php' method='POST'><button name='edit_search' style='padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5bc0de; color: white;' value='$EDIT_STATUS_C'>Düzenle</button></form>";
                    echo '<form action="main_page.php" method="POST"><button name="delete_search" style="padding: 8px 15px; margin-top:0px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;" value="">Sil</button></form>';
                    logs($connect,"Veri araması yapıldı veri ID = $EDIT_STATUS_C",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
        } else{
            echo "<h3 style='color:green;'>Beklenmedik bir hata meydana geldi lütfen yöneticinize başvurun</h3>";
        }
    }
    if(isset($_POST["refr_search"])){
            if ($Erole == "Bilgi Onaylama Rolü" || $Erole == "Yönetici") { // Kullanıcının rolü
                $RE_VERIFY_DATA = $_POST["refr_search"];
                $RE_VERIFY_SELECT = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id"); // DATABASE SELECT
                $RE_VERIFY_SELECT->execute(array(":id" => $RE_VERIFY_DATA));
                $result = $RE_VERIFY_SELECT->fetch(); // Veriyi getir

                // Verileri dağıt
                $URUN_I_VRF = $result["urun_verify"];
                $URUN_A_VRF = $result["adet_verify"];
                $URUN_K_VRF = $result["urun_kodu_verify"];

                // Veriyi urndta001 tablosuna geri ekle
                $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO urndta001 SET urun = :urun, adet = :adet, urun_kodu = :urun_kodu, urn_sender = :urn_sender");
                $INSERT_VERIFY_DATA->execute(array(
                ":urun" => $URUN_I_VRF,
                ":adet" => $URUN_A_VRF,
                ":urun_kodu" => $URUN_K_VRF,
                ":urn_sender" => "<b style='color:red;'>(Tekrar Gönderdi)</b>". " " . $USERn
                ));
                $DELETE_ID_VA = $_POST["refr_search"];
                $DELETE_URN_VA = $connect->prepare("DELETE FROM vrfurn001 WHERE id = :id");
                $DELETE_URN_VA->execute(array(
                ":id" => $DELETE_ID_VA
                ));
                logs($connect,"Ürün onaylama sürecine tekrar gönderildi / Ürün Adı = $URUN_I_VRF Ürün Adet = $URUN_A_VRF Ürün Kodu = $URUN_K_VRF ",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
                echo "
                <script>
                alert('Ürün onaylama sürecine başarıyla tekrar gönderildi');
                window.location.href = 'main_page.php';
                </script>
                ";
        } else {
                echo "
                <script>
                alert('Ürün onaylama sürecine tekrar gönderme işlemi için gerekli izniniz bulunamadı');
                window.location.href = 'main_page.php';
                </script>
                ";
        }
    }
    if(isset($_POST["verify_search"])){
        if ($Erole == "Bilgi Onaylama" || $Erole == "Yönetici") { // Kullanıcının rolü
            $SELECT_EDIT_STATUS_VS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
            $SELECT_EDIT_STATUS_VS->execute(array(":username_dataB" => $USERn));
            $EDIT_STATUS_SLCT = $SELECT_EDIT_STATUS_VS->fetch();

            $VERIFY_DATA = $EDIT_STATUS_SLCT["edit_status"];
            $VERIFY_SELECT = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id"); // DATABASE SELECT
            $VERIFY_SELECT->execute(array(":id" => $VERIFY_DATA));
            $result = $VERIFY_SELECT->fetch(); // Veriyi getir

            // Verileri dağıt
            $URUN_I = $result["urun"];
            $URUN_A = $result["adet"];
            $URUN_K = $result["urun_kodu"];

            // Onaylama için veriyi vrfurn001 tablosuna ekle
            $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO vrfurn001 SET urun_verify = :urun, adet_verify = :adet, urun_kodu_verify = :urun_kodu, urn_sender_verify = :urn_sender_verify");
            $INSERT_VERIFY_DATA->execute(array(
            ":urun" => $URUN_I,
            ":adet" => $URUN_A,
            ":urun_kodu" => $URUN_K,
            ":urn_sender_verify" => $USERn
            ));
            $DELETE_ID_VA = $EDIT_STATUS_SLCT["edit_status"];
            $DELETE_URN_VA = $connect->prepare("DELETE FROM urndta001 WHERE id = :id");
            $DELETE_URN_VA->execute(array(
            ":id" => $DELETE_ID_VA
            ));
            logs($connect,"Ürün Onaylandı / Ürün Adı = $URUN_I Ürün Adet = $URUN_A Ürün Kodu = $URUN_K",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
            echo "
            <script>
            alert('Ürün Onaylandı / Ürün Adı = $URUN_I Ürün Adet = $URUN_A Ürün Kodu = $URUN_K');
            window.location.href = 'main_page.php';
            </script>
            ";
        }else{
            echo "
            <script>
            alert('Ürün silme işlemini yapmak için gerekli izniniz bulunamadı');
            window.location.href = 'main_page.php';
            </script>
            ";
        }
    } else if(isset($_POST["delete_search"])){
        if($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // Kullanıcının rolü
            $SELECT_EDIT_STATUS_VS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
            $SELECT_EDIT_STATUS_VS->execute(array(":username_dataB" => $USERn));
            $EDIT_STATUS_SLCT = $SELECT_EDIT_STATUS_VS->fetch();

            $DELETE_ID = $EDIT_STATUS_SLCT["edit_status"];
            $SELECT_DELETE_DATA = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
            $SELECT_DELETE_DATA->execute(array(":id" => $DELETE_ID));
            $DATA_CHECK_DELETE = $SELECT_DELETE_DATA->fetch();
     
            $URUN_DELETE_CHECK = $DATA_CHECK_DELETE["urun"];
            $URUN_KODU_DELETE_CHECK = $DATA_CHECK_DELETE["urun_kodu"];
            $URUN_ADET_DELETE_CHECK = $DATA_CHECK_DELETE["adet"];

            $DELETE_URN = $connect->prepare("DELETE FROM urndta001 WHERE id = :id");
            $DELETE_URN->execute(array(":id" => $DELETE_ID));

            // urndta001 tablosundaki ID değerlerini sıfırla ve sıralama yap
            $sql_update = "SET @num := 0;
                           UPDATE urndta001 SET id = @num := (@num+1);
                           ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
            $connect->exec($sql_update);
            logs($connect,"Ürün Silindi / Urun Adı = $URUN_DELETE_CHECK / Urun Kodu = $URUN_KODU_DELETE_CHECK / Urun Adet = $URUN_ADET_DELETE_CHECK / Urun ID = $DELETE_ID",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
            echo "
            <script>
            alert('Ürün silme başarılı')
            window.location.href = 'main_page.php';
            </script>
            ";
            exit;
        }else {
            echo "
            <script>
            alert('Ürün silme işlemini yapmak için gerekli izniniz bulunamadı');
            window.location.href = 'main_page.php';
            </script>
            ";
        }                       
    } else if(isset($_POST["edit_search"])){
        $EDIT_DATA = $connect->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $EDIT_DATA->execute(array(":edit_status" => $_POST["edit_search"], ":username_dataB" => $USERn));

        echo "<h5 style='color:green;'>Değiştirilecek Veri :</h5>";

        $EDIT_LY = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
        $EDIT_LY->execute(array(":id" => $_POST['edit_search']));
        $EDIT_LY_FT = $EDIT_LY->fetch();

        echo "<span style='font-weight: bold;'>ID:</span> " . $EDIT_LY_FT["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $EDIT_LY_FT['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $EDIT_LY_FT['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $EDIT_LY_FT["urun_kodu"] . "<br><br>";

        echo '<form action="main_page.php" method="POST">
              <label for="edit_select_st">Değiştirilecek Veri Türü:</label>
              <select class="select_search" for="edit_select_st" name="edit_select_st_name" placeholder="Arama Tipi Seç" required>
                <option value="urun_name_edit">Ürün İsmini Değiştir</option>
                <option value="urun_code_edit">Ürün Kodunu Değiştir</option>
                <option value="urun_ad_edit">Ürün Adetini Değiştir</option>
              </select>
              <input type="text" id="urun_ID_search" name="urun_edit_name_in">
              <button name="edit_data_ver" type="submit">Veriyi Değiştir</button>
              </form>';
    }
    ?>     
    </div>
  </div>
  <br>
  <div class="container">
    <div id="p1" class="box">
    </div>
    <div id="p2" class="box">
    </div>
</div>
  <?php
  if (isset($_POST["verify"])) { // POST varsa
    if ($Erole == "Bilgi Onaylama" || $Erole == "Yönetici") { // Kullanıcının rolü
      $VERIFY_DATA = $_POST["verify"];
      $VERIFY_SELECT = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id"); // DATABASE SELECT
      $VERIFY_SELECT->execute(array(":id" => $VERIFY_DATA));
      $result = $VERIFY_SELECT->fetch(); // Veriyi getir

      // Verileri dağıt
      $URUN_I = $result["urun"];
      $URUN_A = $result["adet"];
      $URUN_K = $result["urun_kodu"];

      // Onaylama için veriyi vrfurn001 tablosuna ekle
      $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO vrfurn001 SET urun_verify = :urun, adet_verify = :adet, urun_kodu_verify = :urun_kodu, urn_sender_verify = :urn_sender_verify");
      $INSERT_VERIFY_DATA->execute(array(
        ":urun" => $URUN_I,
        ":adet" => $URUN_A,
        ":urun_kodu" => $URUN_K,
        ":urn_sender_verify" => $USERn
      ));
      $DELETE_ID_VA = $_POST["verify"];
      $DELETE_URN_VA = $connect->prepare("DELETE FROM urndta001 WHERE id = :id");
      $DELETE_URN_VA->execute(array(
         ":id" => $DELETE_ID_VA
      ));
      logs($connect,"Ürün Onaylandı / Ürün Adı = $URUN_I Ürün Adet = $URUN_A Ürün Kodu = $URUN_K",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
      echo "
      <script>
      alert('Ürün Onaylandı / Ürün Adı = $URUN_I Ürün Adet = $URUN_A Ürün Kodu = $URUN_K');
      window.location.href = 'main_page.php';
      </script>
      ";
    } else {
     echo "
     <script>
     alert('Ürün onaylama işlemini yapmanız için gerekli izniniz bulunamadı');
     window.location.href = 'main_page.php';
     </script>
     ";
    }
  }

  if (isset($_POST["delete"])) { // POST varsa
    if($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // Kullanıcının rolü
     $DELETE_ID = $_POST["delete"];
     $SELECT_DELETE_DATA = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
     $SELECT_DELETE_DATA->execute(array(":id" => $DELETE_ID));
     $DATA_CHECK_DELETE = $SELECT_DELETE_DATA->fetch();
     
     $URUN_DELETE_CHECK = $DATA_CHECK_DELETE["urun"];
     $URUN_KODU_DELETE_CHECK = $DATA_CHECK_DELETE["urun_kodu"];
     $URUN_ADET_DELETE_CHECK = $DATA_CHECK_DELETE["adet"];

     $DELETE_URN = $connect->prepare("DELETE FROM urndta001 WHERE id = :id");
     $DELETE_URN->execute(array(":id" => $DELETE_ID));

     // urndta001 tablosundaki ID değerlerini sıfırla ve sıralama yap
     $sql_update = "SET @num := 0;
                   UPDATE urndta001 SET id = @num := (@num+1);
                   ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
     $connect->exec($sql_update);
     logs($connect,"Ürün Silindi / Urun Adı = $URUN_DELETE_CHECK / Urun Kodu = $URUN_KODU_DELETE_CHECK / Urun Adet = $URUN_ADET_DELETE_CHECK / Urun ID = $DELETE_ID",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
     echo "
     <script>
     alert('Ürün silme başarılı')
     window.location.href = 'main_page.php';
     </script>
     ";
     exit;
   } else {
     echo "
     <script>
     alert('Ürün silme işlemini yapmak için gerekli izniniz bulunamadı');
     window.location.href = 'main_page.php';
     </script>
     ";
   }
  }
  ?>

  <?php
  $Erole = $_SESSION["ROLE"];
  if (isset($_POST["rev"])) { // POST varsa
    if ($Erole == "Bilgi Onaylama Rolü" || $Erole == "Yönetici") { // Kullanıcının rolü
      $RE_VERIFY_DATA = $_POST["rev"];
      $RE_VERIFY_SELECT = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id"); // DATABASE SELECT
      $RE_VERIFY_SELECT->execute(array(":id" => $RE_VERIFY_DATA));
      $result = $RE_VERIFY_SELECT->fetch(); // Veriyi getir

      // Verileri dağıt
      $URUN_I_VRF = $result["urun_verify"];
      $URUN_A_VRF = $result["adet_verify"];
      $URUN_K_VRF = $result["urun_kodu_verify"];

      // Veriyi urndta001 tablosuna geri ekle
      $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO urndta001 SET urun = :urun, adet = :adet, urun_kodu = :urun_kodu, urn_sender = :urn_sender");
      $INSERT_VERIFY_DATA->execute(array(
        ":urun" => $URUN_I_VRF,
        ":adet" => $URUN_A_VRF,
        ":urun_kodu" => $URUN_K_VRF,
        ":urn_sender" => "<b style='color:red;'>(Tekrar Gönderdi)</b>". " " . $USERn
      ));
      $DELETE_ID_VA = $_POST["rev"];
      $DELETE_URN_VA = $connect->prepare("DELETE FROM vrfurn001 WHERE id = :id");
      $DELETE_URN_VA->execute(array(
         ":id" => $DELETE_ID_VA
      ));
      logs($connect,"Ürün onaylama sürecine tekrar gönderildi / Ürün Adı = $URUN_I_VRF Ürün Adet = $URUN_A_VRF Ürün Kodu = $URUN_K_VRF ",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
      echo "
      <script>
      alert('Ürün onaylama sürecine başarıyla tekrar gönderildi');
      window.location.href = 'main_page.php';
      </script>
      ";
    } else {
      echo "
      <script>
      alert('Ürün onaylama sürecine tekrar gönderme işlemi için gerekli izniniz bulunamadı');
      window.location.href = 'main_page.php';
      </script>
      ";
    }
  }
  ?>
  </div>
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="scripts/get-p1_main.js" type="text/javascript"></script>
  <script src="scripts/get-p2_main.js" type="text/javascript"></script>
 </body>
</html>