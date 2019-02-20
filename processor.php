<?php

require_once './vendor/autoload.php';

const WEB_PATH = './web/';

$imgPath 			= WEB_PATH . 'HJoceanSmall.png';
$dualImgPath 		= WEB_PATH . 'HJoceanSmall_dual.png';
$imgResizedPath 	= WEB_PATH . 'HJoceanSmall_resized.png';
$dualImgResizedPath = WEB_PATH . 'HJoceanSmall_dual_resized.png';

$picture = new \Picture($imgPath);
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture($dualImgPath);

$x = '20';
$y = '10';

# collect and remove seams
$hSeams = [];
$vSeams = [];
for ($i = 0; $i < $x; $i++) {
	$hSeams[] = $seamCarver->findVerticalSeam();
	$seamCarver->removeVerticalSeam($hSeams[$i]);
}

for ($i = 0; $i < $y; $i++) {
	$vSeams[] = $seamCarver->findHorizontalSeam();
	$seamCarver->removeHorizontalSeam($vSeams[$i]);
}

// output images with removed seams
$picture->output($imgResizedPath);
$seamCarver->outputDualGradientPicture($dualImgResizedPath);


/**
 * Seam Carving Algorithm
 *
 * 1. Create Picture instance
 *  1.1. Parse image and create image matrix
 *
 * 2. Create SeamCarving instance based on Picture instance
 *  2.1. Create energy matrix from Picture
 *
 * 3. Output image after processing it by dual-gradient energy function
 *
 * 4. Find and remove seams
 *  4.1. Find seam we need to remove
 *  4.2. Remove seam pixels from energy matrix
 *  4.3. Remove seam pixels from Picture image matrix
 *
 * 6. Create and output new image based on the modified Picture image matrix
 */
