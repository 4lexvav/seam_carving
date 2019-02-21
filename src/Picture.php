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
     * @var resource - image
     */
    private $originalImage;

    /**
     * @var int
     */
    private $origWidth;

    /**
     * @var int
     */
    private $origHeight;

    public function __construct(string $path, array $imageMatrix = null)
    {
        $image = imagecreatefromstring(file_get_contents($path));

        $this->image = $image;
        $this->originalImage = $image;
        $this->imageMatrix = $imageMatrix;
        $this->buildMatrix();
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->width = imagesx($this->image);
            $this->origWidth = imagesx($this->image);
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
            $this->origHeight = imagesy($this->image);
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
            $height = $this->getHeight();
            $width = $this->getWidth();
            for ($i = 0; $i < $height; $i++) {
                $imgX = [];
                for ($j = 0; $j < $width; $j++) {
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
    public function setImageMatrix(array $matrix = []): void
    {
        $this->imageMatrix = $matrix;
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
        $height = $this->getHeight();
        $width = $this->getWidth();

        // crop image matrix
        foreach($seam as $x => $y) {
            $this->imageMatrix[$y][$x] = null;
        }

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($this->imageMatrix[$y][$x] === null) {
                    $i = $y + 1;
                    if ($i >= $height) {
                        break;
                    }

                    do {
                        // shift all vertical pixels 1 pixel up
                        $this->imageMatrix[$i - 1][$x] = $this->imageMatrix[$i][$x];
                        $this->imageMatrix[$i][$x] = null;
                    } while (++$i < $height);
                }
            }
        }

        // update width
        unset($this->imageMatrix[$height - 1]);
        $this->setHeight($height - 1);
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
     * @inheritdoc
     */
    public function outputWithSeams(array $hSeams, array $vSeams, string $path): void
    {
        $color = imagecolorallocate(imagecreatetruecolor(1, 1), 255, 0, 0);
        $image = $this->originalImage;

        // set vertical seams
        foreach ($vSeams as $seam) {
            foreach ($seam as $y => $x) {
                imagesetpixel($image, $x, $y, $color);
            }
        }

        // set horizontal seams
        foreach ($hSeams as $seam) {
            foreach ($seam as $x => $y) {
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagejpeg($image, $path);
    }

    /**
     * Create image based on current image matrix size and colors.
     *
     * @return resource
     */
    private function createImage()
    {
        $height = $this->getHeight();
        $width = $this->getWidth();

        $px = imagecreatetruecolor(1, 1);
        $image = imagecreatetruecolor($width, $height);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorallocate($px, ...$this->imageMatrix[$y][$x]);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        $this->image = $image;

        return $this->image;
    }
}
