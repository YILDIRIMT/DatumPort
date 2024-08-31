<?php
include("DB_CONNECT.php");

try {
    // Kullanıcı işlemlerini loglama fonksiyonu
    function logs($connect_func, $ACTION, $USER_ID, $USER_IP_ADDR_FUNC, $USER_MACHINE_NAME_FUNC) { 
        $stmt = $connect_func->prepare("INSERT INTO usrlogs001 SET
            action = :action,
            kullanici_ismi = :kullanici_ismi,
            ip_address = :ip_address,
            machine_name = :machine_name");

        $stmt->execute(array(
            "action" => $ACTION,
            "kullanici_ismi" => $USER_ID,
            "ip_address" => $USER_IP_ADDR_FUNC,
            "machine_name" => $USER_MACHINE_NAME_FUNC
        ));     
    }
} catch (PDOException $e) {
    // Veritabanı hatalarını logla ve kullanıcıya bir hata mesajı göster
    error_log("Veritabanı hatası: " . $e->getMessage());
    echo "<script type='text/javascript'>
            alert('Bir hata oluştu, lütfen sistem yöneticinize başvurun');
          </script>";
}
?>