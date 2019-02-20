<?php

require_once './vendor/autoload.php';

/*$picture = new \Picture('./web/image.jpeg');
$picture->outputDualGradientPicture('./web/out.jpg');*/

$picture = new \Picture('./web/HJoceanSmall.png');
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture('./web/HJoceanSmallOut.png');

# read from input
$x = '3';
$y = '3';

# collect and remove seams
$hSeams = [];
$vSeams = [];

for ($i = 0; $i < $x; $i++) {
	$hSeams[] = $seamCarver->findVerticalSeam();
	$seamCarver->removeVerticalSeam($hSeams[$i]);
}

/*for ($i = 0; $i < $y; $i++) {
	$vSeams[] = $seamCarver->findHorizontalSeam();
	$seamCarver->removeHorizontalSeam($vSeams[$i]);
}*/

/**
 * Seam Carving Algorithm
 *
 * 1. Create Picture instance
 *  1.1. Parse image and create image matrix
 *
 * 2. Create SeamCarving instance based on Picture instance
 *  2.1. Create energy matrix based on Picture
 *  2.2. Backup original energy matrix to immutable property
 *
 * 3. Output image after processing by dual-gradient energy function
 *
 * 4. Find seam
 *  4.1. Collect all seams we need to remove
 *
 * 5. Remove seam
 *  5.1. Remove seam pixels from energy matrix
 *  5.2. Remove seam pixels from Picture image matrix
 *
 * 6. Create new image based on the Picture image matrix after removed seams pixels
 */
