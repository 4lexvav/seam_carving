<?php

require_once './vendor/autoload.php';

function getRgb($color) {
    return [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
}

$picture = new Picture('./web/3.jpg');
$seamCarver = new SeamCarver($picture);

$imageMatrix = [
    [[78,209,79],[ 63,118,247],[ 92,175, 95],[243, 73,183],[210,109,104],[252,101,119]],
    [[224,191,182],[108, 89, 82],[ 80,196,230],[112,156,180],[176,178,120],[142,151,142]],
    [[117,189,149],[171,231,153],[149,164,168],[107,119, 71],[120,105,138],[163,174,196]],
    [[163,222,132],[187,117,183],[ 92,145, 69],[158,143, 79],[220, 75,222],[189, 73,214]],
    [[211,120,173],[188,218,244],[214,103, 68],[163,166,246],[ 79,125,246],[211,201, 98]]
];

$picture->setWidth(6);
$picture->setHeight(5);
$picture->setImageMatrix($imageMatrix); // set image matrix
$seamCarver->setEnergyMatrix(); // unset energy matrix
$energyMatrix = $seamCarver->computePictureEnergy(); // compute new energy matrix

$seam = $seamCarver->findVerticalSeam();
$seamCarver->removeVerticalSeam($seam);
$newEnergy = $seamCarver->getPictureEnergyMatrix();

$colors = [];
foreach ($newEnergy as $x => $row) {
    foreach ($row as $y => $color) {
        $colors[$x][$y] = getRgb($color);
    }
}

echo PHP_EOL;
print_r($energyMatrix);
echo PHP_EOL;
/*echo PHP_EOL;
print_r($newEnergy);
echo PHP_EOL;*/
echo PHP_EOL;
print_r($seam);
echo PHP_EOL;
echo PHP_EOL;
print_r($colors);
echo PHP_EOL;
die();
