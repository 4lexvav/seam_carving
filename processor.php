<?php

require_once './vendor/autoload.php';

const WEB_PATH = './web/';

$imgPath 			  = WEB_PATH . '3.jpg';
$dualImgPath 		  = WEB_PATH . '3_dual.jpg';
$imgResizedPath 	  = WEB_PATH . '3_resized.jpg';
$dualImgResizedPath   = WEB_PATH . '3_dual_resized.jpg';
$baseImgWithSeamsFile = WEB_PATH . '3_seams.jpg';

$picture = new Picture($imgPath);
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture($dualImgPath);

$x = '150';
$y = '0';

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
