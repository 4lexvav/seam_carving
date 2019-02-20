<?php
declare(strict_types=1);

class Picture implements PictureInterface
{
    /**
     * @var array[] - array with px RGB colors
     */
    private $imageMatrix;

    /**
     * @var resource - image
     */
    private $image;

    /**
     * @var int|null
     */
    private $width = null;

    /**
     * @var int|null
     */
    private $height = null;

    /**
     * @var resource - image skipped through dual-gradient energy function
     */
    private $dualGradientImage;

    public function __construct(string $path, array $imageMatrix = null)
    {
        $image = imagecreatefromstring(file_get_contents($path));

        $this->image = $image;
        $this->imageMatrix = $imageMatrix;
        $this->buildMatrix();
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->width = imagesx($this->image);
        }

        return $this->width;
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        if ($this->height === null) {
            $this->height = imagesy($this->image);
        }

        return $this->height;
    }

    /**
     * @inheritdoc
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @inheritdoc
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * Build matrix containing image' RGB colors for each px.
     *
     * @return array
     */
    private function buildMatrix(): array
    {
        if ($this->imageMatrix === null) {
            $img = [];
            for ($i = 0; $i < $this->getHeight(); $i++) {
                $imgX = [];
                for ($j = 0; $j < $this->getWidth(); $j++) {
                    $pxColor = imagecolorat($this->image, $j, $i);
                    $rgb = [];
                    $rgb[0] = ($pxColor >> 16) & 0xFF;
                    $rgb[1] = ($pxColor >> 8) & 0xFF;
                    $rgb[2] = $pxColor & 0xFF;

                    $imgX[$j] = $rgb;
                }
                $img[$i] = $imgX;
            }

            $this->imageMatrix = $img;
        }

        return $this->imageMatrix;
    }

    /**
     * @inheritdoc
     */
    public function getImageMatrix(): array
    {
        if ($this->imageMatrix === null) {
            $this->imageMatrix = $this->buildMatrix();
        }

        return $this->imageMatrix;
    }

    /**
     * @inheritdoc
     */
    public function getPxColor(int $y, int $x): array
    {
        return $this->getImageMatrix()[$y][$x];
    }

    /**
     * @inheritdoc
     */
    public function removeHorizontalSeam(array $seam): void
    {
        // crop image matrix
        foreach($seam as $x => $y) {
            $this->imageMatrix[$y][$x] = null;
        }

        for ($y = 0; $y < $this->getHeight(); $y++) {
            for ($x = 0; $x < $this->getWidth(); $x++) {
                if ($this->imageMatrix[$y][$x] === null) {
                    $i = $y + 1;
                    if ($i >= $this->getHeight()) {
                        break;
                    }

                    do {
                        // shift all vertical pixels 1 pixel up
                        $this->imageMatrix[$i - 1][$x] = $this->imageMatrix[$i][$x];
                        $this->imageMatrix[$i][$x] = null;
                    } while (++$i < $this->getHeight());
                }
            }
        }

        // update width
        unset($this->imageMatrix[$this->getHeight() - 1]);
        $this->setHeight($this->getHeight() - 1);
    }

    public function removeVerticalSeam(array $seam): void
    {
        // crop image matrix
        for ($y = 0; $y < $this->getHeight(); $y++) {
            unset($this->imageMatrix[$y][$seam[$y]]);
            $this->imageMatrix[$y] = array_values($this->imageMatrix[$y]);
        }

        // update width
        $this->setWidth($this->getWidth() - 1);
    }

    /**
     * @inheritdoc
     */
    public function output(string $path): void
    {
        imagejpeg($this->createImage(), $path);
    }

    /**
     * Create image based on current image matrix size and colors.
     *
     * @return resource
     */
    private function createImage()
    {
        $px = imagecreatetruecolor(1, 1);
        $image = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        for ($y = 0; $y < $this->getHeight(); $y++) {
            for ($x = 0; $x < $this->getWidth(); $x++) {
                $color = imagecolorallocate($px, ...$this->imageMatrix[$y][$x]);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        $this->image = $image;

        return $this->image;
    }
}
