<?php
include("DB_CONNECT.php");

$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

// İsteğin localhost'tan gelip gelmediğini kontrol et
if ($ADMIN_IP == "::1") {
    // ID alanını sıfırla ve AUTO_INCREMENT'i yeniden başlat
    $sql_update = "SET @num := 0;
                   UPDATE usrlogs001 SET id = @num := (@num+1);
                   ALTER TABLE usrlogs001 AUTO_INCREMENT = 1;";
    $connect->exec($sql_update);

    // Sorgu başarılı
    TRUE;
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
    <title>Datum ~ Port LOGS</title>
    <link rel="stylesheet" href="../styles/logs_style.css">
    <link rel="icon" href="../favicon.png" type="image/x-icon"/>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="../images/DatumPort_Logs_nonbg.png" alt="Logo" class="logo">
        </div>
    </header>
    <main>
        <div class="button-container">
            <a href="http://localhost/phpmyadmin/index.php?route=/sql&pos=0&db=datumport_msql&table=usrlogs001" class="button">Data Base</a>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>LOG Numarası</th>
                        <th>Olay</th>
                        <th>Kullanıcı İsmi</th>
                        <th>IP Adresi</th>
                        <th>Makine Adı</th>
                        <th>Olay Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Toplam kayıt sayısını al
                    $stmt = $connect->prepare("SELECT COUNT(*) FROM usrlogs001");
                    $stmt->execute();
                    $ID = $stmt->fetchColumn();

                    // Kayıtları tersten sırala ve tabloya ekle
                    for ($i = $ID; $i >= 1; $i--) {
                        $stmt_in = $connect->prepare("SELECT * FROM usrlogs001 WHERE id = :id");
                        $stmt_in->execute(array(":id" => $i));
                        $ID_in = $stmt_in->fetch();

                        echo "<tr>";
                        echo "<td>{$i}</td>";
                        echo "<td class='action'>{$ID_in['action']}</td>";
                        echo "<td>{$ID_in['kullanici_ismi']}</td>";
                        echo "<td>{$ID_in['ip_address']}</td>";
                        echo "<td>{$ID_in['machine_name']}</td>";
                        echo "<td>{$ID_in['action_timestamp']}</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
