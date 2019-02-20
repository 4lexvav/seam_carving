# Seam Carving - content-aware image resizing technique

### Seam Carving Algorithm

1. Create Picture instance
	1. Parse image and create image matrix
 
2. Create SeamCarving instance based on Picture instance
	1. Create energy matrix from Picture

3. Output image after processing it by dual-gradient energy function

4. Find and remove seams
	1. Find seam we need to remove
	2. Remove seam pixels from energy matrix
	3. Remove seam pixels from Picture image matrix

5. Create and output new image based on the modified Picture image matrix

### Example

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
