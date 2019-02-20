<?php
declare(strict_types=1);

interface PictureInterface
{
    public function __construct(string $path, SplFixedArray $imageMatrix = null);

    /**
     * Get image width.
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Get image height.
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * @param int $width
     */
    public function setWidth(int $width): void;

    /**
     * @param int $height
     */
    public function setHeight(int $height): void;

    /**
     * Return image matrix with RGB colors for each px.
     *
     * @return SplFixedArray
     */
    public function getImageMatrix(): SplFixedArray;

    /**
     * Get RGB color of a px.
     *
     * @param int $y
     * @param int $x
     *
     * @return SplFixedArray
     */
    public function getPxColor(int $y, int $x): SplFixedArray;

    /**
     * Remove horizontal seam from picture.
     *
     * @param array $seam
     */
    public function removeHorizontalSeam(array $seam): void;

    /**
     * Remove vertical seam from picture.
     *
     * @param array $seam
     */
    public function removeVerticalSeam(array $seam): void;

    /**
     * Output picture to a file.
     *
     * @param string $path
     */
    public function output(string $path): void;
}
