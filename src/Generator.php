<?php

namespace SiteOrigin\VoronoiPlaceholder;

use Imagick;

class Generator
{
    const ENCODING_VERSION = 1;
    const IMAGE_SIZE = 512;
    const DEFAULT_POINT_COUNT = 256;
    const COLOR_COUNT = 16;
    const CHARS = '!#$%&()*+-.0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{}~ ';

    private array $points;
    private array $colors;

    public function __construct(string $imageSrc)
    {
        // Load the main image
        $this->img = new Imagick($imageSrc);
        $this->img->resizeImage(self::IMAGE_SIZE, self::IMAGE_SIZE, Imagick::FILTER_POINT, 1, true);

        $this->points = [];
        $this->colors = [];
    }

    /**
     * Perform the actual calculations
     *
     * @param int $pointCount
     * @throws \ImagickPixelException
     */
    public function generate($pointCount = self::DEFAULT_POINT_COUNT)
    {
        $this->findPoints($pointCount);
        $this->findPointColors();
    }

    /**
     * Find point positions that correspond with highly salient areas of the image.
     *
     * @param int $count
     * @return array
     * @throws \ImagickPixelException
     */
    protected function findPoints($count = self::DEFAULT_POINT_COUNT): array
    {
        $edges = clone $this->img;
        //$edges->quantizeImage(self::COLOR_COUNT, Imagick::COLORSPACE_RGB, 0, false, false);
        $edges->setImageType(Imagick::IMGTYPE_GRAYSCALE);
        $edges->edgeImage(4);
        $edges->blurImage(30, 10);
        $edges->normalizeImage();

        $this->points = [];
        while(count($this->points) < $count) {
            $newPoint = [
                rand(0, $edges->getImageWidth()),
                rand(0, $edges->getImageHeight()),
            ];
            $px = $edges->getImagePixelColor($newPoint[0], $newPoint[1]);

            $prob = (0.002 + $px->getColorValue(Imagick::COLOR_RED))/1.002;
            if($prob > rand()/getrandmax()) {
                // Accept the color
                $this->points[] = $newPoint;

                // Draw in black so we're less likely to get a nearby point.
                $draw = new \ImagickDraw();
                $draw->setFillColor(new \ImagickPixel('rgb(0,0,0)'));
                $draw->circle($newPoint[0], $newPoint[1], $newPoint[0] + 16, $newPoint[1] + 16);
                $edges->drawImage($draw);
            }
        }

        return $this->points;
    }

    /**
     * For the internal points, find the colors that represent them.
     *
     * @throws \ImagickPixelException
     */
    protected function findPointColors(): array
    {
        $img = clone $this->img;
        $img->blurImage(25, 10);
        $img->quantizeImage(self::COLOR_COUNT, Imagick::COLORSPACE_RGB, 0, false, false);

        foreach($this->points as $point) {
            $color = $img->getImagePixelColor($point[0], $point[1])->getColor();
            $color = sprintf("%02x%02x%02x", $color['r'], $color['g'], $color['b']);

            if(empty($this->colors[$color])) {
                $this->colors[$color] = [];
            }

            $this->colors[$color][] = $point;
        }

        return $this->colors;
    }

    public function getPointsString(): string
    {
        if (empty($this->colors)) $this->generate();

        $return = '';
        foreach($this->colors as $color => $points) {
            $return .= $this->encodeInteger(base_convert($color, 16, 10)) . ',';
            $return .= join(
                ',',
                array_map(fn($point) => $this->encodeInteger(($point[0] << 9) + $point[1]), $points)
            ) . '|';
        }
        return rtrim($return, '|');
    }

    /**
     * Encode an integer into a dense string.
     *
     * @param int $number
     * @return string
     */
    protected function encodeInteger(int $number): string
    {
        $return = [];
        while($number > 0) {
            $rem = $number % strlen(self::CHARS);
            array_unshift($return, self::CHARS[$rem]);
            $number -= $rem;
            $number /= strlen(self::CHARS);
        }

        return implode('', $return);
    }

}