<?php
declare(strict_types=1);

class SeamCarver implements SeamCarverInterface
{
    /**
     * @var PictureInterface
     */
    private $picture;

    /**
     * @var SplFixedArray - array with px energy
     */
    private $energyMatrix;

    /**
     * @var resource
     */
    private $dualGradientImage;

    public function __construct(PictureInterface $picture, SplFixedArray $energyMatrix = null)
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
        // loop 1 line more to treat first line as all zeros

    }

    /**
     * @inheritdoc
     */
    public function findVerticalSeam(): array
    {
        $minIdx = 0;
        $minEnergy = 0;
        $seam = new SplFixedArray($this->picture()->getHeight());
        $pathMatrix = array_fill(0, $this->height(), array_fill(0, $this->width(), 0));
        $energyMatrix = $this->getPictureEnergyMatrix();

        $line = array_fill(0, $this->width(), 0);
        for ($i = 0; $i < $this->height(); $i++) {
            for ($j = 0; $j < $this->width(); $j++) {
                $p = ($j === 0) ? $j : $j - 1; // x - 1 px
                $c = $j; // x px
                $n = ($j + 1 === $this->width()) ? $j : $j + 1; // x + 1 px

                // add minimum energy to the next px
                $minEnergy = min($line[$p], $line[$c], $line[$n]);
                $minIdx = array_search($minEnergy, array_slice($line, $p, $p === $c ? 2 : 3, true), true);
                $energyMatrix[$i][$j] += $minEnergy;

                // store x-coordinate position (min energy idx) from which we came to this px to collect seam carve path
                $pathMatrix[$i][$j] = $minIdx;
            }
            $line = $energyMatrix[$i]->toArray();
        }

        // identify current seam location by finding minimum value stored in the latest horizontal line of an image
        $minEnergy = min($energyMatrix[$this->height() - 1]->toArray()); // energy value
        $minIdx = array_search($minEnergy, $energyMatrix[$this->height() - 1]->toArray(), true);

        $seam[$this->height() - 1] = $minIdx;
        // collect lowest seam by using pointers to previous pixels with lowest energy values
        for ($i = $this->height() - 2; $i >= 0; $i--) {
            $seam[$i] = $pathMatrix[$i + 1][$minIdx];
            $minIdx = $seam[$i];
        }

        return $seam->toArray();
    }

    /**
     * @inheritdoc
     */
    public function removeHorizontalSeam(array $seam): void
    {
        // it seams we only need to once remove the vertical seam from both matrices (picture and enerty)
        // and finally remove all the seams from the actual image and save it.
        // TODO: remove horizontal seam from picture and energy image + from both matrices
    }

    /**
     * @inheritdoc
     */
    public function removeVerticalSeam(array $seam): void
    {
        // crop image
        $this->picture()->removeVerticalSeam($seam);

        // crop energy matrix
        for ($y = 0; $y < $this->picture()->getHeight(); $y++) {
            unset($this->energyMatrix[$y][$seam[$y]]);
        }
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
     * @return SplFixedArray
     */
    private function getPictureEnergyMatrix(): SplFixedArray
    {
        if ($this->energyMatrix === null) {
            $this->energyMatrix = $this->computePictureEnergy();
        }

        return $this->energyMatrix;
    }

    /**
     * Skip image matrix through dual-gradient energy function to compute energy for each px.
     *
     * @param SplFixedArray $imageMatrix
     *
     * @return SplFixedArray
     */
    private function computePictureEnergy(): SplFixedArray
    {
        if ($this->energyMatrix === null) {
            $y = $this->picture()->getHeight();
            $energyMatrix = new SplFixedArray($y);
            for ($i = 0; $i < $y; $i++) {
                $x = $this->picture()->getWidth();
                $energyY = new SplFixedArray($x);
                for ($j = 0; $j < $x; $j++) {
                    $xLeft = $this->picture()->getPxColor($i, $j === 0 ? $x - 1 : $j - 1);
                    $xRight = $this->picture()->getPxColor($i, $j === $x - 1 ? 0 : $j + 1);
                    $yLeft = $this->picture()->getPxColor($i === 0 ? $y - 1 : $i - 1, $j);
                    $yRight = $this->picture()->getPxColor($i === $y - 1 ? 0 : $i + 1, $j);

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
     * @param SplFixedArray $xLeft  - RGB color of a pixel
     * @param SplFixedArray $xRight - RGB color of a pixel
     * @param SplFixedArray $yLeft  - RGB color of a pixel
     * @param SplFixedArray $yRight - RGB color of a pixel
     *
     * @return int
     */
    private function computePxEnergy(
        SplFixedArray $xLeft,
        SplFixedArray $xRight,
        SplFixedArray $yLeft,
        SplFixedArray $yRight
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
        $dualGradientImage = imagecreatetruecolor($this->picture()->getWidth(), $this->picture()->getHeight());
        foreach ($this->energyMatrix as $i => $y) {
            foreach ($y as $j => $color) {
                imagesetpixel($dualGradientImage, $j, $i, $color);
            }
        }

        $this->dualGradientImage = $dualGradientImage;

        return $this->dualGradientImage;
    }
}
