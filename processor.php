<?php

require_once './vendor/autoload.php';

const WEB_PATH = './web/';

$imgPath 			  = WEB_PATH . '2.jpeg';
$dualImgPath 		  = WEB_PATH . '2_dual.jpeg';
$imgResizedPath 	  = WEB_PATH . '2_resized.jpeg';
$dualImgResizedPath   = WEB_PATH . '2_dual_resized.jpeg';
$baseImgWithSeamsFile = WEB_PATH . '2_seams.jpeg';

/*$imgPath 			  = WEB_PATH . '1.png';
$dualImgPath 		  = WEB_PATH . '1_dual.png';
$imgResizedPath 	  = WEB_PATH . '1_resized.png';
$dualImgResizedPath   = WEB_PATH . '1_dual_resized.png';
$baseImgWithSeamsFile = WEB_PATH . '1_seams.png';*/

$picture = new Picture($imgPath);
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture($dualImgPath);

$x = '1';
$y = '1';

# collect and remove seams
$vSeams = [];
$hSeams = [];
for ($i = 0; $i < $x; $i++) {
	$vSeams[] = $seamCarver->findVerticalSeam();
	$seamCarver->removeVerticalSeam($vSeams[$i]);
}

for ($i = 0; $i < $y; $i++) {
	$hSeams[] = $seamCarver->findHorizontalSeam();
	$seamCarver->removeHorizontalSeam($hSeams[$i]);
}

// output images with removed seams
$picture->output($imgResizedPath);
$seamCarver->outputDualGradientPicture($dualImgResizedPath);
$picture->outputWithSeams($hSeams, $vSeams, $baseImgWithSeamsFile);


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
