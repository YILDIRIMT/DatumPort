<?php
include("DB_CONNECT.php");

$width = 200;
$height = 50;
$font_size = 20;
$font_file = 'C:\Windows\Fonts\arial.ttf';

$captcha_number = generateRandomString(5);

$image = imagecreatetruecolor($width, $height);

$background_color = imagecolorallocate($image, 255, 255, 255); // Beyaz arka plan rengi
$text_color = imagecolorallocate($image, 0, 0, 0); // Siyah metin rengi
$line_color = imagecolorallocate($image, 64, 64, 64); // Gri çizgi rengi

imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

imagettftext($image, $font_size, 0, 10, $height / 1.5, $text_color, $font_file, $captcha_number);

ob_start(); 
imagepng($image); 
$image_data = ob_get_clean(); 

imagedestroy($image);

function generateRandomString($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJkarenKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>
