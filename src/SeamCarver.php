<?php
declare(strict_types=1);

class SeamCarver implements SeamCarverInterface
{
    /**
     * @var PictureInterface
     */
    private $picture;

    /**
     * @var array - array with px energy
     */
    private $energyMatrix;

    /**
     * @var resource
     */
    private $dualGradientImage;

    public function __construct(PictureInterface $picture, array $energyMatrix = null)
    {
        $this->picture = $picture;
        $this->energyMatrix = $energyMatrix;
        $this->computePictureEnergy();
    }

    /**
     * @inheritdoc
     */
    public function picture(): PictureInterface
    {
        return $this->picture;
    }

    /**
     * @inheritdoc
     */
    public function width(): int
    {
        return $this->picture->getWidth();
    }

    /**
     * @inheritdoc
     */
    public function height(): int
    {
        return $this->picture->getHeight();
    }

    /**
     * @inheritdoc
     */
    public function energy(int $x, int $y): float
    {
        return $this->getPictureEnergyMatrix()[$y][$x];
    }

    /**
     * @inheritdoc
     */
    public function findHorizontalSeam(): array
    {
        $seam = [];
        $minIdx = 0;
        $minEnergy = 0;
        $height = $this->height();
        $width = $this->width();
        $pathMatrix = array_fill(0, $height, array_fill(0, $width, 0));

        $energyMatrix = $this->getPictureEnergyMatrix();
        $line = array_fill(0, $height, 0);
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                $p = ($j === 0) ? $j : $j - 1; // x - 1 px
                $c = $j; // x px
                $n = ($j + 1 === $height) ? $j : $j + 1; // x + 1 px

                $pV = $line[$p];
                $cV = $line[$c];
                $nV = $line[$n];

                // add minimum energy to the next px
                $minEnergy = min($pV, $cV, $nV);
                $minIdx = array_search($minEnergy, [$p => $pV, $c => $cV, $n => $nV], true);

                $energyMatrix[$j][$i] += $minEnergy;

                // store x-coordinate position (min energy idx) from which we came to this px to collect seam carve path
                $pathMatrix[$j][$i] = $minIdx;
            }
            $line = array_column($energyMatrix, $i); // extract all vertical pixel values
        }

        // identify current seam location by finding minimum value stored in the latest horizontal line of an image
        $lastColumnValues = array_column($energyMatrix, $width - 1);
        $minEnergy = min($lastColumnValues); // energy value
        $minIdx = array_search($minEnergy, $lastColumnValues, true);

        $seam[$width - 1] = $minIdx;

        // collect lowest seam by using pointers to previous pixels with lowest energy values
        for ($i = $width - 2; $i >= 0; $i--) {
            $columns = array_column($pathMatrix, $i);
            $seam[$i] = $columns[$minIdx];
            $minIdx = $seam[$i];
        }

        return $seam;
    }

    /**
     * @inheritdoc
     */
    public function findVerticalSeam(): array
    {
        $seam = [];
        $minIdx = 0;
        $minEnergy = 0;
        $height = $this->height();
        $width = $this->width();
        $pathMatrix = array_fill(0, $this->height(), array_fill(0, $width, 0));

        $energyMatrix = $this->getPictureEnergyMatrix();
        $line = array_fill(0, $width, 0);
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $p = ($j === 0) ? $j : $j - 1; // x - 1 px
                $c = $j; // x px
                $n = ($j + 1 === $width) ? $j : $j + 1; // x + 1 px

                $pV = $line[$p];
                $cV = $line[$c];
                $nV = $line[$n];

                // add minimum energy to the next px
                $minEnergy = min($line[$p], $line[$c], $line[$n]);
                $minIdx = array_search($minEnergy, [$p => $pV, $c => $cV, $n => $nV], true);
                $energyMatrix[$i][$j] += $minEnergy;

                // store x-coordinate position (min energy idx) from which we came to this px to collect seam carve path
                $pathMatrix[$i][$j] = $minIdx;
            }
            $line = $energyMatrix[$i];
        }

        // identify current seam location by finding minimum value stored in the latest horizontal line of an image
        $minEnergy = min($energyMatrix[$height - 1]); // energy value
        $minIdx = array_search($minEnergy, $energyMatrix[$height - 1], true);

        $seam[$height - 1] = $minIdx;
        // collect lowest seam by using pointers to previous pixels with lowest energy values
        for ($i = $height - 2; $i >= 0; $i--) {
            $seam[$i] = $pathMatrix[$i + 1][$minIdx];
            $minIdx = $seam[$i];
        }

        return $seam;
    }

    /**
     * @inheritdoc
     */
    public function removeHorizontalSeam(array $seam): void
    {
        $height = $this->height();
        $width = $this->width();

        // crop energy matrix
        foreach($seam as $x => $y) {
            $this->energyMatrix[$y][$x] = null;
        }

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($this->energyMatrix[$y][$x] === null) {
                    $i = $y + 1;
                    if ($i >= $height) {
                        break;
                    }

                    do {
                        // shift all vertical pixels 1 pixel up
                        $this->energyMatrix[$i - 1][$x] = $this->energyMatrix[$i][$x];
                        $this->energyMatrix[$i][$x] = null;
                    } while (++$i < $height);
                }
            }
        }

        // crop image
        unset($this->energyMatrix[$height - 1]);
        $this->picture()->removeHorizontalSeam($seam);
    }

    /**
     * @inheritdoc
     */
    public function removeVerticalSeam(array $seam): void
    {
        // crop energy matrix
        for ($y = 0; $y < $this->picture()->getHeight(); $y++) {
            unset($this->energyMatrix[$y][$seam[$y]]);

            $this->energyMatrix[$y] = array_values($this->energyMatrix[$y]);
        }

        // crop image
        $this->picture()->removeVerticalSeam($seam);
    }

    /**
     * @inheritdoc
     */
    public function outputDualGradientPicture(string $path): void
    {
        imagejpeg($this->createDualEnergyImage(), $path);
    }

    /**
     * Return image matrix with computed energy for each px.
     *
     * @return array
     */
    public function getPictureEnergyMatrix(): array
    {
        if ($this->energyMatrix === null) {
            $this->energyMatrix = $this->computePictureEnergy();
        }

        return $this->energyMatrix;
    }

    /**
     * @param array|null $matrix
     */
    public function setEnergyMatrix(array $matrix = null): void
    {
        $this->energyMatrix = $matrix;
    }

    /**
     * Skip image matrix through dual-gradient energy function to compute energy for each px.
     *
     * @return array
     */
    public function computePictureEnergy(): array
    {
        $imageMatrix = $this->picture()->getImageMatrix();
        if ($this->energyMatrix === null) {
            $x = $this->picture()->getWidth();
            $y = $this->picture()->getHeight();
            $energyMatrix = [];
            for ($i = 0; $i < $y; $i++) {
                $energyY = [];
                for ($j = 0; $j < $x; $j++) {
                    $xLeft = $imageMatrix[$i][$j === 0 ? $x - 1 : $j - 1];
                    $xRight = $imageMatrix[$i][$j === $x - 1 ? 0 : $j + 1];
                    $yLeft = $imageMatrix[$i === 0 ? $y - 1 : $i - 1][$j];
                    $yRight = $imageMatrix[$i === $y - 1 ? 0 : $i + 1][$j];

                    $energyY[$j] = $this->computePxEnergy($xLeft, $xRight, $yLeft, $yRight);
                }
                $energyMatrix[$i] = $energyY;
            }

            $this->energyMatrix = $energyMatrix;
        }

        return $this->energyMatrix;
    }

    /**
     * Compute central px dual gradient energy.
     *
     * @param int[] $xLeft  - RGB color of a pixel
     * @param int[] $xRight - RGB color of a pixel
     * @param int[] $yLeft  - RGB color of a pixel
     * @param int[] $yRight - RGB color of a pixel
     *
     * @return int
     */
    private function computePxEnergy(
        array $xLeft,
        array $xRight,
        array $yLeft,
        array $yRight
    ): int {
        $energy = 0;
        for ($i = 0; $i < 3; $i++) {
            $diffX = abs($xLeft[$i] - $xRight[$i]);
            $diffY = abs($yLeft[$i] - $yRight[$i]);
            $energy += ($diffX ** 2 + $diffY ** 2);
        }

        return $energy;
    }

    /**
     * Create image based on colors passed through dual-gradient energy function.
     *
     * @return resource
     */
    private function createDualEnergyImage()
    {
        //$px = imagecreatetruecolor(1, 1);
        $dualGradientImage = imagecreatetruecolor($this->picture()->getWidth(), $this->picture()->getHeight());
        foreach ($this->energyMatrix as $i => $y) {
            foreach ($y as $j => $color) {
                //$color = imagecolorallocate($px, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF);
                imagesetpixel($dualGradientImage, $j, $i, $color);
            }
        }

        $this->dualGradientImage = $dualGradientImage;

        return $this->dualGradientImage;
    }
}
