<?php
declare(strict_types=1);

interface PictureInterface
{
    public function __construct(string $path, array $imageMatrix = null);

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
     * @return array
     */
    public function getImageMatrix(): array;

    /**
     * Get RGB color of a px.
     *
     * @param int $y
     * @param int $x
     *
     * @return array
     */
    public function getPxColor(int $y, int $x): array;

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

    /**
     * Draw line on the image.
     *
     * @param array $seam
     */
    public function drawSeam(array $seam): void;
}
