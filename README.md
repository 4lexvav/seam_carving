# Seam Carving - content-aware image resizing technique

### Usage

```php
$imgPath 			  = WEB_PATH . '2.jpeg';
$dualImgPath 		  = WEB_PATH . '2_dual.jpeg';
$imgResizedPath 	  = WEB_PATH . '2_resized.jpeg';
$dualImgResizedPath   = WEB_PATH . '2_dual_resized.jpeg';
$baseImgWithSeamsFile = WEB_PATH . '2_seams.jpeg';

$picture = new Picture($imgPath);
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture($dualImgPath);

// reduce by 20px in width and 10px in height
$x = '20';
$y = '10';

// collect and remove seams
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
```
