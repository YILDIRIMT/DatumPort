<?php
session_start();
include("DB_CONNECT.php");
include("lib/logs_function.php");
include("lib/user_delete_query.php");

$Erole = $_SESSION["ROLE"];

if ($_SESSION["USERNAME"] && $_SESSION["KEY"] == 1) { // IF SESSION Exists
  $USERn = $_SESSION["USERNAME"];
  $USER_IP_ADDR = $_SERVER["REMOTE_ADDR"];
  $USER_MACHINE_NAME = gethostname();
  
  // Reset the ID values in the urndta001 table and sort them
  $sql_update = "SET @num := 0;
                UPDATE urndta001 SET id = @num := (@num+1);
                ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
  $connect->exec($sql_update);

  // Reset the ID values in the vrfurn001 table and sort them
  $sql_update = "SET @num := 0;
                UPDATE vrfurn001 SET id = @num := (@num+1);
                ALTER TABLE vrfurn001 AUTO_INCREMENT = 1;";
  $connect->exec($sql_update);

  if (isset($_POST["insert_data"])) { // IF POST Exists
    if ($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // User role 
      $urun_adi = $_POST["urun"];
      $urun_adet = $_POST["adet"];
      $urun_kodu = $_POST["urun-kodu"];

      // IF Variables not null
      if(!empty($urun_adi) && !empty($urun_adet) && !empty($urun_kodu)) {
        // Insert data to database
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
        logs($connect,"Insert data / Data Name = $urun_adi Data Piece = $urun_adet Data Code = $urun_kodu",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
        header("location: main_page.php");
      } else {
        echo "<script type='text/javascript'>
        alert('Check the data, and if the error persists, contact your manager.');
        </script>";
      }
    } else {
      echo "
      <script>
      alert('You do not have the necessary permission to add products. Please contact your manager.');
      window.location.href = 'main_page.php';
      </script>
      ";
    }
  }

  if (isset($_POST["log_out"])) { // IF POST Exists
    logs($connect,"Sessıon Close",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME); // Oturum kapatma işlemi için log ekle

    session_destroy();
    header("location: index.php");
    exit;
  }
  echo ".";
} else { // IF POST Not exists
    header("location: index.php");
    exit;
}

#FUNCTIONS
function get_search_data($value,$connect_gsd,$parameter,$USERn_function){
   $SELECT_SEARCH = $connect_gsd->prepare("SELECT * FROM urndta001 WHERE $parameter = :value");
   $SELECT_SEARCH->execute(array(":value" => $value));
   $SEARCH_DATA = $SELECT_SEARCH->fetch();
   
   if($SEARCH_DATA){
        echo "<h5 style='color:green;'>Pending approval data found :</h5>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $SEARCH_DATA["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Data Name:</span> " . $SEARCH_DATA['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Piece:</span> " . $SEARCH_DATA['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Data Code:</span> " . $SEARCH_DATA["urun_kodu"] . "<br>";
        $update = $connect_gsd->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $update->execute(array(":edit_status" => $SEARCH_DATA["id"],":username_dataB" => $USERn_function));

   } else{
       echo "<h3 style='color:red;'>The requested data could not be found. Please check the 'Search Table' and 'Search Type' inputs. If the error persists, contact your manager</h3>";
   }
}

function get_search_data_vfd($value,$connect_gsd,$parameter,$USERn_function){
   $SELECT_SEARCH = $connect_gsd->prepare("SELECT * FROM vrfurn001 WHERE $parameter = :value");
   $SELECT_SEARCH->execute(array(":value" => $value));
   $SEARCH_DATA = $SELECT_SEARCH->fetch();
   
   if($SEARCH_DATA){
        echo "<h5 style='color:green;'>Approved data found :</h5>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $SEARCH_DATA["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Data Name:</span> " . $SEARCH_DATA['urun_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Piece:</span> " . $SEARCH_DATA['adet_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Data Code:</span> " . $SEARCH_DATA["urun_kodu_verify"] . "<br>";
        $update = $connect_gsd->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $update->execute(array(":edit_status" => $SEARCH_DATA["id"],":username_dataB" => $USERn_function));
   } else{
       echo "<h3 style='color:red;'>Data not found. Please check the 'Search Table' and 'Search Type' inputs. If the error persists, contact your manager.</h3>";
   }
}

function edit_data($connectf,$edit_data_fc,$value,$id){
    $STMT_EDIT = $connectf->prepare("UPDATE urndta001 SET $edit_data_fc = :value WHERE id = :id");
    $STMT_EDIT->execute(array(":value" => $value,
                              ":id" => $id));
    global $USERn,$USER_IP_ADDR,$USER_MACHINE_NAME;
    if($STMT_EDIT){
        $LOG_INSERT = $connectf->prepare("INSERT INTO usrlogs001 (action, kullanici_ismi, ip_address, machine_name) VALUES (:action, :kullanici_ismi, :ip_address, :machine_name)");
        $LOG_INSERT->execute(array(":action" => "The value of the data with ID $id has been changed to $value for $edit_data_fc.",
                                   ":kullanici_ismi" => $USERn,
                                   ":ip_address" => $USER_IP_ADDR,
                                   ":machine_name" => $USER_MACHINE_NAME));
        echo "<script>
                alert('Data info has changed.');
              </script>";
    } else{
        echo "<script>
                alert('An error occurred while performing the operation. Please contact your manager.');
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
       <button class="log_out_button" type="submit" name="log_out"><b class="in-log-b">Log Out</b></button>
      </form>
      <?php
      $Eusername = $_SESSION["USERNAME"];
      $get_image = $connect->prepare("SELECT image_data FROM usrdta001 WHERE username_dataB = :user_id");
      $get_image->bindParam(':user_id', $Eusername);
      $get_image->execute();
      $image_data = $get_image->fetchColumn();

      if($Erole == "Yönetici"){
          $EC_role = "Administrator";
      } else if ($Erole == "Bilgi Ekleme"){
          $EC_role = "Information Addition";
      } else{
          $EC_role = "Information Approval";
      }

      // Profil resmini ve kullanıcı bilgilerini ekrana yazdır
      echo '&nbsp;&nbsp;<img style="height:100%; top:1; position:absolute;" src="data:image/jpeg;base64,' . base64_encode($image_data) . '" alt="Profil Resmi"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
      echo "<b style='position:absolute; top:1;'>&nbsp; User : $Eusername</b>";
      echo "<b style='position:absolute; top:1; left:240;'>&nbsp; &nbsp; Role : $EC_role</b> &nbsp; &nbsp; &nbsp; ";
      ?>
  </div>
  <div style="height: auto; padding: 20px;">
    <br>
    <br>
    <div class="container">
    <div class="box">
        <form action="#" method="POST" id="urun-form">
        <label for="urun">Data Name:</label>
        <input type="text" id="urun" name="urun" required>
        <label for="adet">Piece:</label>
        <input type="number" id="adet" name="adet" min="1" required>
        <label for="urun-kodu">Data Code:</label>
        <input type="text" id="urun-kodu" name="urun-kodu" required>
        <button name="insert_data" type="submit">Save</button>
    </form>
    </div>
    <div class="box">
        <form action="#" method="POST" id="urun-form">
        <label for="urun_search">Data Name:</label>
        <input type="text" id="urun_search" name="urun_search_name">
        <label for="urun_kodu_search">Data Code:</label>
        <input type="text" id="urun_kodu_search" name="urun_kodu_search_name">

        <label for="urun_ID_search">Data ID:</label>
        <input type="text" id="urun_ID_search" name="urun_ID_search_name">

        <label for="select_search_st">Search Table:</label>
        <select class="select_search" for="select_search_st" name="search_src_st_name" placeholder="Arama Tipi Seç" required> &nbsp;
            <option value="vf_data">Search for Pending Approval Data</option>
            <option value="vfd_data">Search for Approved Data</option>
        </select>

        <label for="select_search_id">Search Type:</label>
        <select class="select_search" for="select_search_id" name="search_src" placeholder="Arama Tipi Seç" required> &nbsp;
            <option value="urun_name">Data name-focused search</option>
            <option value="urun_code">Data code-focused search</option>
            <option value="urun_id">Data ID-focused search</option>
        </select>
        <button name="search_data" type="submit">Search Data</button>
    </form>
    </div>
    <div class="box">
    <?php

    #DATA EDIT
    if (isset($_POST["edit"])){
        $EDIT_DATA = $connect->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $EDIT_DATA->execute(array(":edit_status" => $_POST["edit"], ":username_dataB" => $USERn));

        echo "<h5 style='color:green;'>Data to be changed :</h5>";

        $EDIT_LY = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
        $EDIT_LY->execute(array(":id" => $_POST['edit']));
        $EDIT_LY_FT = $EDIT_LY->fetch();

        echo "<span style='font-weight: bold;'>ID:</span> " . $EDIT_LY_FT["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Data Name:</span> " . $EDIT_LY_FT['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Piece:</span> " . $EDIT_LY_FT['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Data Code:</span> " . $EDIT_LY_FT["urun_kodu"] . "<br><br>";

        echo '<form action="main_page.php" method="POST">
              <label for="edit_select_st">Type of Data to be Changed:</label>
              <select class="select_search" for="edit_select_st" name="edit_select_st_name" placeholder="Arama Tipi Seç" required>
                <option value="urun_name_edit">Change Data Name</option>
                <option value="urun_code_edit">Change Data Code</option>
                <option value="urun_ad_edit">Changed Data Piece</option>
              </select>
              <input type="text" id="urun_ID_search" name="urun_edit_name_in">
              <button name="edit_data_ver" type="submit">Change Data</button>
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
             echo "<h3 style='color:red;'>An unexpected situation occurred. Please try again, and if the error persists, contact your manager.</h3>";
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
                    echo "<br><form action='main_page.php' method='POST'><button name='refr_search' style='padding: 8px 15px; margin-top:0px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;' value='$EDIT_STATUS_C'>Send Back</button></form>";
                    logs($connect,"Data search was performed Data ID = $EDIT_STATUS_C",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
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
                    echo '<br><form action="main_page.php" method="POST"><button name="verify_search" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5cb85c; color: white;" value="">Verify</button></form>';
                    echo "<form action='main_page.php' method='POST'><button name='edit_search' style='padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5bc0de; color: white;' value='$EDIT_STATUS_C'>Edit</button></form>";
                    echo '<form action="main_page.php" method="POST"><button name="delete_search" style="padding: 8px 15px; margin-top:0px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;" value="">Delete</button></form>';
                    logs($connect,"Data search was performed Data ID = $EDIT_STATUS_C",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
        } else{
            echo "<h3 style='color:green;'>An unexpected error occurred, please contact your manager.</h3>";
        }
    }
    if(isset($_POST["refr_search"])){
            if ($Erole == "Bilgi Onaylama Rolü" || $Erole == "Yönetici") { // User Role
                $RE_VERIFY_DATA = $_POST["refr_search"];
                $RE_VERIFY_SELECT = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id"); // DATABASE SELECT
                $RE_VERIFY_SELECT->execute(array(":id" => $RE_VERIFY_DATA));
                $result = $RE_VERIFY_SELECT->fetch(); // Get Data

                // Distribute the data
                $URUN_I_VRF = $result["urun_verify"];
                $URUN_A_VRF = $result["adet_verify"];
                $URUN_K_VRF = $result["urun_kodu_verify"];

                // Re-Insert the data to the urndta001 table
                $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO urndta001 SET urun = :urun, adet = :adet, urun_kodu = :urun_kodu, urn_sender = :urn_sender");
                $INSERT_VERIFY_DATA->execute(array(
                ":urun" => $URUN_I_VRF,
                ":adet" => $URUN_A_VRF,
                ":urun_kodu" => $URUN_K_VRF,
                ":urn_sender" => "<b style='color:red;'>(Sent Back)</b>". " " . $USERn
                ));
                $DELETE_ID_VA = $_POST["refr_search"];
                $DELETE_URN_VA = $connect->prepare("DELETE FROM vrfurn001 WHERE id = :id");
                $DELETE_URN_VA->execute(array(
                ":id" => $DELETE_ID_VA
                ));
                logs($connect,"Sent back to the product approval process / Data Name = $URUN_I_VRF Data Piece = $URUN_A_VRF Data Code = $URUN_K_VRF ",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
                echo "
                <script>
                alert('Sent back to the product approval process');
                window.location.href = 'main_page.php';
                </script>
                ";
        } else {
                echo "
                <script>
                alert('You do not have the necessary permission to resend to the product approval process.');
                window.location.href = 'main_page.php';
                </script>
                ";
        }
    }
    if(isset($_POST["verify_search"])){
        if ($Erole == "Bilgi Onaylama" || $Erole == "Yönetici") { // User Role
            $SELECT_EDIT_STATUS_VS = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
            $SELECT_EDIT_STATUS_VS->execute(array(":username_dataB" => $USERn));
            $EDIT_STATUS_SLCT = $SELECT_EDIT_STATUS_VS->fetch();

            $VERIFY_DATA = $EDIT_STATUS_SLCT["edit_status"];
            $VERIFY_SELECT = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id"); // DATABASE SELECT
            $VERIFY_SELECT->execute(array(":id" => $VERIFY_DATA));
            $result = $VERIFY_SELECT->fetch(); // Get Data

            // Distribute data
            $URUN_I = $result["urun"];
            $URUN_A = $result["adet"];
            $URUN_K = $result["urun_kodu"];

            // Add the data to the vrfurn001 table for approval
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
            logs($connect,"Data Approved / Data Name = $URUN_I Data Piece = $URUN_A Data Code = $URUN_K",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
            echo "
            <script>
            alert('Data Approved / Data Name = $URUN_I Data Piece = $URUN_A Data Code = $URUN_K');
            window.location.href = 'main_page.php';
            </script>
            ";
        }else{
            echo "
            <script>
            alert('You do not have the necessary permission to perform the product approval process.');
            window.location.href = 'main_page.php';
            </script>
            ";
        }
    } else if(isset($_POST["delete_search"])){
        if($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // User Role
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

            // Reset the ID values in the urndta001 table and sort them 
            $sql_update = "SET @num := 0;
                           UPDATE urndta001 SET id = @num := (@num+1);
                           ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
            $connect->exec($sql_update);
            logs($connect,"Data Deleted / Data Name = $URUN_DELETE_CHECK / Data Code = $URUN_KODU_DELETE_CHECK / Data Piece = $URUN_ADET_DELETE_CHECK / Data ID = $DELETE_ID",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
            echo "
            <script>
            alert('Data Deleting Completed')
            window.location.href = 'main_page.php';
            </script>
            ";
            exit;
        }else {
            echo "
            <script>
            alert('You do not have the necessary permission to perform the data deletion process.');
            window.location.href = 'main_page.php';
            </script>
            ";
        }                       
    } else if(isset($_POST["edit_search"])){
        $EDIT_DATA = $connect->prepare("UPDATE usrdta001 SET edit_status = :edit_status WHERE username_dataB = :username_dataB");
        $EDIT_DATA->execute(array(":edit_status" => $_POST["edit_search"], ":username_dataB" => $USERn));

        echo "<h5 style='color:green;'>Data to be Changed :</h5>";

        $EDIT_LY = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
        $EDIT_LY->execute(array(":id" => $_POST['edit_search']));
        $EDIT_LY_FT = $EDIT_LY->fetch();

        echo "<span style='font-weight: bold;'>ID:</span> " . $EDIT_LY_FT["id"] . "<br>";
        echo "<span style='font-weight: bold;'>Data Name:</span> " . $EDIT_LY_FT['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Piece:</span> " . $EDIT_LY_FT['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Data Code:</span> " . $EDIT_LY_FT["urun_kodu"] . "<br><br>";

        echo '<form action="main_page.php" method="POST">
              <label for="edit_select_st">Type of Data to be Changed</label>
              <select class="select_search" for="edit_select_st" name="edit_select_st_name" placeholder="Arama Tipi Seç" required>
                <option value="urun_name_edit">Change Data Name</option>
                <option value="urun_code_edit">Change Data Code</option>
                <option value="urun_ad_edit">Change Data Piece</option>
              </select>
              <input type="text" id="urun_ID_search" name="urun_edit_name_in">
              <button name="edit_data_ver" type="submit">Change Data</button>
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
  if (isset($_POST["verify"])) { // IF POST Exists
    if ($Erole == "Bilgi Onaylama" || $Erole == "Yönetici") { // User Role
      $VERIFY_DATA = $_POST["verify"];
      $VERIFY_SELECT = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id"); // DATABASE SELECT
      $VERIFY_SELECT->execute(array(":id" => $VERIFY_DATA));
      $result = $VERIFY_SELECT->fetch(); // GET Data

      // Distribute data
      $URUN_I = $result["urun"];
      $URUN_A = $result["adet"];
      $URUN_K = $result["urun_kodu"];

      // Add the data to the vrfurn001 table for confirmation.
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
      logs($connect,"Data Approved / Data Name = $URUN_I Data Piece = $URUN_A Data Code = $URUN_K",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
      echo "
      <script>
      alert('Data Approved / Data Name = $URUN_I Data Piece = $URUN_A Data Code = $URUN_K');
      window.location.href = 'main_page.php';
      </script>
      ";
    } else {
     echo "
     <script>
     alert('You do not have the necessary permission to perform the product approval process.');
     window.location.href = 'main_page.php';
     </script>
     ";
    }
  }

  if (isset($_POST["delete"])) { // IF POST Exists
    if($Erole == "Bilgi Ekleme" || $Erole == "Yönetici") { // User role
     $DELETE_ID = $_POST["delete"];
     $SELECT_DELETE_DATA = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
     $SELECT_DELETE_DATA->execute(array(":id" => $DELETE_ID));
     $DATA_CHECK_DELETE = $SELECT_DELETE_DATA->fetch();
     
     $URUN_DELETE_CHECK = $DATA_CHECK_DELETE["urun"];
     $URUN_KODU_DELETE_CHECK = $DATA_CHECK_DELETE["urun_kodu"];
     $URUN_ADET_DELETE_CHECK = $DATA_CHECK_DELETE["adet"];

     $DELETE_URN = $connect->prepare("DELETE FROM urndta001 WHERE id = :id");
     $DELETE_URN->execute(array(":id" => $DELETE_ID));

     // Reset the ID values in the urndta001 table and sort them.
     $sql_update = "SET @num := 0;
                   UPDATE urndta001 SET id = @num := (@num+1);
                   ALTER TABLE urndta001 AUTO_INCREMENT = 1;";
     $connect->exec($sql_update);
     logs($connect,"Data Deleted / Data Name = $URUN_DELETE_CHECK / Data Code = $URUN_KODU_DELETE_CHECK / Data Piece = $URUN_ADET_DELETE_CHECK / Data ID = $DELETE_ID",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
     echo "
     <script>
     alert('Data Deleting Completed')
     window.location.href = 'main_page.php';
     </script>
     ";
     exit;
   } else {
     echo "
     <script>
     alert('You do not have the necessary permission to perform the data deletion process.'); 
     window.location.href = 'main_page.php';
     </script>
     ";
   }
  }
  ?>

  <?php
  $Erole = $_SESSION["ROLE"];
  if (isset($_POST["rev"])) { // IF POST exists
    if ($Erole == "Bilgi Onaylama Rolü" || $Erole == "Yönetici") { // User role
      $RE_VERIFY_DATA = $_POST["rev"];
      $RE_VERIFY_SELECT = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id"); // DATABASE SELECT
      $RE_VERIFY_SELECT->execute(array(":id" => $RE_VERIFY_DATA));
      $result = $RE_VERIFY_SELECT->fetch(); // GET DATA

      // Distribute Data
      $URUN_I_VRF = $result["urun_verify"];
      $URUN_A_VRF = $result["adet_verify"];
      $URUN_K_VRF = $result["urun_kodu_verify"];

      // Reinsert the data into the urndta001 table.
      $INSERT_VERIFY_DATA = $connect->prepare("INSERT INTO urndta001 SET urun = :urun, adet = :adet, urun_kodu = :urun_kodu, urn_sender = :urn_sender");
      $INSERT_VERIFY_DATA->execute(array(
        ":urun" => $URUN_I_VRF,
        ":adet" => $URUN_A_VRF,
        ":urun_kodu" => $URUN_K_VRF,
        ":urn_sender" => "<b style='color:red;'>(Sent Back)</b>". " " . $USERn
      ));
      $DELETE_ID_VA = $_POST["rev"];
      $DELETE_URN_VA = $connect->prepare("DELETE FROM vrfurn001 WHERE id = :id");
      $DELETE_URN_VA->execute(array(
         ":id" => $DELETE_ID_VA
      ));
      logs($connect,"Resent for product approval process. / Data Name = $URUN_I_VRF Data Piece = $URUN_A_VRF Data Code = $URUN_K_VRF ",$USERn,$USER_IP_ADDR,$USER_MACHINE_NAME);
      echo "
      <script>
      alert('Successfully resent for product approval process.');
      window.location.href = 'main_page.php';
      </script>
      ";
    } else {
      echo "
      <script>
      alert('You do not have the necessary permission to resend the product for the approval process.');
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