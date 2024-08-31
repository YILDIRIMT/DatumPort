<?php
include("DB_CONNECT.php");

// CAPTCHA resmi için boyutlar ve diğer ayarlar
$width = 200; // Resmin genişliği
$height = 50; // Resmin yüksekliği
$font_size = 20; // Yazı boyutu
$font_file = 'C:\Windows\Fonts\arial.ttf'; // Font dosyasının yolu

// CAPTCHA için rastgele bir dizi oluştur
$captcha_number = generateRandomString(5);

// Yeni bir görüntü oluştur
$image = imagecreatetruecolor($width, $height);

// Renkleri tanımla
$background_color = imagecolorallocate($image, 255, 255, 255); // Beyaz arka plan rengi
$text_color = imagecolorallocate($image, 0, 0, 0); // Siyah metin rengi
$line_color = imagecolorallocate($image, 64, 64, 64); // Gri çizgi rengi

// Arka planı beyaz ile doldur
imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

// Resme rastgele çizgiler ekle
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// CAPTCHA numarasını resme yaz
imagettftext($image, $font_size, 0, 10, $height / 1.5, $text_color, $font_file, $captcha_number);

// Resmi bir değişkene aktar
ob_start(); 
imagepng($image); 
$image_data = ob_get_clean(); 

// Bellekte oluşturulan görüntüyü yok et
imagedestroy($image);

// Rastgele bir dizi üretme fonksiyonu
function generateRandomString($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJkarenKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>
