<?php
$logo = imagecreatefrompng('public/images/LR.png');
$logo_w = imagesx($logo);
$logo_h = imagesy($logo);

$canvas_size = 256;
$canvas = imagecreatetruecolor($canvas_size, $canvas_size);

// Brand secondary color: #C01E2E (RGB: 192, 30, 46)
$bg_color = imagecolorallocate($canvas, 192, 30, 46);
imagefill($canvas, 0, 0, $bg_color);

// Add padding
$padding = 40;
$max_w = $canvas_size - ($padding * 2);
$max_h = $canvas_size - ($padding * 2);

$scale = min($max_w / $logo_w, $max_h / $logo_h);
$new_w = (int)($logo_w * $scale);
$new_h = (int)($logo_h * $scale);

$dst_x = (int)(($canvas_size - $new_w) / 2);
$dst_y = (int)(($canvas_size - $new_h) / 2);

// Enable alpha blending for transparent PNG source
imagealphablending($canvas, true);
imagesavealpha($canvas, true);

imagecopyresampled($canvas, $logo, $dst_x, $dst_y, 0, 0, $new_w, $new_h, $logo_w, $logo_h);

imagepng($canvas, 'public/favicon.ico');

imagedestroy($logo);
imagedestroy($canvas);
echo "Favicon generated successfully!\n";
