<?php

$image = imagecreatefromstring(file_get_contents('./image.jpeg'));
$x = imagesx($image);
$y = imagesy($image);

$img = [$x];
for ($i = 0; $i < $x; $i++) {
	$imgY = [$y];
	for ($j = 0; $j < $y; $j++) {
		$pxColor = imagecolorat($image, $i, $j);
		$rgb = [];
		$rgb[] = ($pxColor >> 16) & 0xFF;
		$rgb[] = ($pxColor >> 8) & 0xFF;
		$rgb[] = $pxColor & 0xFF;

		$imgY[$j] = $rgb;
	}
	$img[$i] = $imgY;
}

echo PHP_EOL;
print_r(memory_get_peak_usage(true) / 1024 / 1024);
echo PHP_EOL;
die();