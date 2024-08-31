<?php
include("../DB_CONNECT.php");
session_start();

// Kullanıcı giriş kontrolü
if ($_SESSION["USERNAME"] && $_SESSION["KEY"] == 1) {

    // Onay bekleyen ürünlerin sayısını al
    $WCLN = $connect->prepare("SELECT COUNT(*) FROM urndta001");
    $WCLN->execute();
    $COLNM = $WCLN->fetchColumn();

    echo "<h2>Onay Bekleyen Ürünler</h2>";

    // Ürünleri listele
    for ($i = $COLNM; $i >= 1; $i--) {
        $WCLN_DATA = $connect->prepare("SELECT * FROM urndta001 WHERE id = :id");
        $WCLN_DATA->execute(array(":id" => $i));
        $result = $WCLN_DATA->fetch();

        echo '<div class="data-item">';
        echo "<hr><b>" . "Olayı Gerçekleştiren: " . $result['urn_sender'] . "</b><br>";
        echo "<br><span style='font-weight: bold;'>ID:</span> " . $i . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Adı:</span> " . $result['urun'] . "<br>";
        echo "<span style='font-weight: bold;'>Adet:</span> " . $result['adet'] . "<br>";
        echo "<span style='font-weight: bold;'>Ürün Kodu:</span> " . $result["urun_kodu"] . "<br>";
        
        echo '<br><div>';
        echo '<form action="main_page.php" method="POST"><button name="verify" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5cb85c; color: white;" value="'.$i.'">Onayla</button></form>';
        echo '<form action="main_page.php" method="POST"><button name="edit" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #5bc0de; color: white;" value="'.$i.'">Düzenle</button></form>';
        echo '<form action="main_page.php" method="POST"><button name="delete" style="padding: 8px 15px; margin-top:0px; margin-right: 10px; cursor: pointer; border: none; outline: none; border-radius: 5px; font-size: 14px; background-color: #d9534f; color: white;" value="'.$i.'">Sil</button></form>';
        echo '</div>';
        echo '</div><br><br>';
    }
} else {
    // Yetkisiz erişim
    header("location: ../index.php");
    exit;
}
?>