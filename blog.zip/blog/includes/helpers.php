<?php /* Helper functions */ 
function resizeAndCompressImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 800, $maxSizeKB = 200) {
    list($width, $height, $type) = getimagesize($sourcePath);

    // Create source image based on type
    switch ($type) {
        case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $srcImg = imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_WEBP: $srcImg = imagecreatefromwebp($sourcePath); break;
        default: return false;
    }

    // Resize keeping aspect ratio
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    $dstImg = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Compress to fit under 200 KB
    $quality = 90;
    do {
        ob_start();
        imagejpeg($dstImg, null, $quality);
        $imageData = ob_get_clean();
        $sizeKB = strlen($imageData) / 1024;
        $quality -= 5;
    } while ($sizeKB > $maxSizeKB && $quality > 10);

    file_put_contents($targetPath, $imageData);
    imagedestroy($srcImg);
    imagedestroy($dstImg);

    return true;
}
?>