<?php
error_reporting(0);
// Start session for captcha
session_start();

// Clean any previous output
ob_clean();

// Captcha Settings
$width = 150;
$height = 50;
$chars = 5;

// Generate random captcha code (avoid similar looking characters)
$possible = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$str = '';
for ($i = 0; $i < $chars; $i++) { 
    $str .= $possible[random_int(0, strlen($possible) - 1)];
}
$_SESSION['verify'] = $str;

// Create image
$image = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($image, 45, 55, 72);      // Dark blue-gray
$text = imagecolorallocate($image, 255, 255, 255);  // White
$line = imagecolorallocate($image, 100, 100, 100);  // Gray

// Fill background
imagefill($image, 0, 0, $bg);

// Add noise lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, 
        random_int(0, $width), 
        random_int(0, $height), 
        random_int(0, $width), 
        random_int(0, $height), 
        $line);
}

// Add noise dots
for ($i = 0; $i < 150; $i++) {
    imagesetpixel($image, 
        random_int(0, $width), 
        random_int(0, $height), 
        $line);
}

// Use built-in font (more reliable than TTF)
$font_size = 5;
$x = 15;
$y = ($height - imagefontheight($font_size)) / 2;

// Add each character with slight random offset
for ($i = 0; $i < strlen($str); $i++) {
    $char_x = $x + ($i * 25) + random_int(-3, 3);
    $char_y = $y + random_int(-3, 3);
    imagestring($image, $font_size, $char_x, $char_y, $str[$i], $text);
}

// Output image
header("Content-type: image/png");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
imagepng($image);
imagedestroy($image);
exit();
?>
