<?php


interface SeamCarverInterface
{
    public function __construct(PictureInterface $picture, SplFixedArray $energyMatrix = null);

    /**
     * Current picture.
     *
     * @return PictureInterface
     */
    public function picture(): PictureInterface;

    /**
     * Width of current picture.
     *
     * @return int
     */
    public function width(): int;

    /**
     * Height of current picture.
     *
     * @return int
     */
    public function height(): int;

    /**
     * Energy of pixel at column x and row y.
     *
     * @param int $x
     * @param int $y
     *
     * @return float
     */
    public function energy(int $x, int $y): float;

    /**
     * Sequence of indices for horizontal seam.
     *
     * @return int[]
     */
    public function findHorizontalSeam(): array; //int[]

    /**
     * Sequence of indices for vertical seam.
     *
     * @return int[]
     */
    public function findVerticalSeam(): array; //int[]

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
     * Compute dual-gradient energy of a picture and output it to a file.
     *
     * @param string $path
     */
    public function outputDualGradientPicture(string $path): void;
}
