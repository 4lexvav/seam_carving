# Seam Carving - content-aware image resizing technique

### Usage

```php
$picture = new Picture('./web/image.png');
$seamCarver = new SeamCarver($picture);

$seamCarver->outputDualGradientPicture('./web/image_dual.png');

// reduce by 20px in width and 10px in height
$x = '20';
$y = '10';

// collect and remove seams
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
$picture->output('./web/image_resized.png');
$seamCarver->outputDualGradientPicture('./web/image_dual_resized.png');
```
