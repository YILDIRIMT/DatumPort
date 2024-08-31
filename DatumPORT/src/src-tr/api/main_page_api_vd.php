<?php
include("../DB_CONNECT.php"); 
session_start(); 

// Kullanıcı giriş kontrolü
if ($_SESSION["USERNAME"] && $_SESSION["KEY"] == 1) {

    // Ürünlerin sayısını al
    $SELECT_COUNT_VF = $connect->prepare("SELECT COUNT(*) FROM vrfurn001");
    $SELECT_COUNT_VF->execute();
    $COUNT_VF = $SELECT_COUNT_VF->fetchColumn();

    echo "<h2>Onaylanmış Ürünler</h2>";

    // Ürünleri listele
    for ($i = $COUNT_VF; $i >= 1; $i--) {
        $WCLN_DATA = $connect->prepare("SELECT * FROM vrfurn001 WHERE id = :id");
        $WCLN_DATA->execute(array(":id" => $i));
        $result = $WCLN_DATA->fetch();

        echo '<hr><br><div class="data-item">';
        echo "<b>" . "Olayı Gerçekleştiren : " . $result['urn_sender_verify'] . "</b><br><br>";
        echo "<span style='font-weight: bold;'>ID:</span> " . $i . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $result['urun_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $result['adet_verify'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $result["urun_kodu_verify"] . "<br>";

        echo '<form action="main_page.php" method="POST">
                <button name="rev" style="padding: 8px 15px; margin-top:15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;" value="'.$i.'">Geri Gönder</button>
              </form>';
    }
} else {
    // Yetkisiz erişim
    header("location: ../index.php");
    exit;
}
?>
