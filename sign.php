<?php
// sign.php - output combined traffic sign images stacked vertically

// Determine parameter for signs. Accept 'signs' or 'ids'.
$param = $_GET['signs'] ?? $_GET['ids'] ?? '';
if ($param === '') {
    http_response_code(400);
    echo "No signs specified";
    exit;
}

$signs = array_filter(explode('_', $param));
$images = [];
$totalHeight = 0;
$width = 0;

foreach ($signs as $id) {
    $id = basename($id); // prevent directory traversal
    $file = __DIR__ . '/signs/' . $id . '.png';
    if (!is_file($file)) {
        // skip missing files
        continue;
    }
    $img = @imagecreatefrompng($file);
    if ($img === false) {
        continue;
    }
    $images[] = $img;
    $w = imagesx($img);
    $h = imagesy($img);
    if ($w > $width) {
        $width = $w;
    }
    $totalHeight += $h;
}

if (empty($images)) {
    http_response_code(404);
    echo "No valid signs";
    exit;
}

$dest = imagecreatetruecolor($width, $totalHeight);
imagesavealpha($dest, true);
$transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
imagefill($dest, 0, 0, $transparent);

$y = 0;
foreach ($images as $img) {
    $w = imagesx($img);
    $h = imagesy($img);
    imagecopy($dest, $img, 0, $y, 0, 0, $w, $h);
    $y += $h;
    imagedestroy($img);
}

header('Content-Type: image/png');
imagepng($dest);
imagedestroy($dest);
