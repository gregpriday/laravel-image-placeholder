<?php

namespace SiteOrigin\VoronoiPlaceholder;

use Imagick;

class Generator
{
    CONST DEFAULT_POINT_COUNT = 300;
    CONST COLOR_COUNT = 32;
    const CHARS = '!#$%&()*+-.0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{}~';

    private string $imageSrc;
    private array $points;
    private array $colors;

    public function __construct(string $imageSrc)
    {
        // Load the main image
        $this->img = new Imagick($imageSrc);
        $this->img->resizeImage(1024,1024, Imagick::FILTER_GAUSSIAN, 1, true);

        $this->points = [];
        $this->colors = [];
    }

    /**
     * Perform the actual calculations
     *
     * @param int $pointCount
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
    protected function findPoints($count = self::DEFAULT_POINT_COUNT)
    {
        $edges = clone $this->img;
        $edges->quantizeImage(self::COLOR_COUNT, Imagick::COLORSPACE_RGB, 0, false, false);
        $edges->edgeImage(3);
        $edges->setImageType(Imagick::IMGTYPE_GRAYSCALE);
        $edges->blurImage(25, 10);
        $edges->normalizeImage();

        $this->points = [];
        while(count($this->points) < $count) {
            $newPoint = [
                rand(0, $edges->getImageWidth()),
                rand(0, $edges->getImageHeight()),
            ];
            $px = $edges->getImagePixelColor($newPoint[0], $newPoint[1]);

            $prob = (0.0025 + $px->getColorValue(Imagick::COLOR_RED))/1.0025;
            if($prob > rand()/getrandmax()) {
                // Accept the color
                $this->points[] = $newPoint;

                // Draw in black so we're less likely to get a nearby point.
                $draw = new \ImagickDraw();
                $draw->setFillColor(new \ImagickPixel('rgb(0,0,0)'));
                $draw->circle($newPoint[0], $newPoint[1], $newPoint[0] + 24, $newPoint[1] + 24);
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
    protected function findPointColors()
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
    }

    public function getPointsString()
    {
        if (empty($this->colors)) $this->generate();

        $return = '';
        foreach($this->colors as $color => $points) {
            $return .= $this->encodeInteger(base_convert($color, 16, 10)) . ',';
            $return .= join(
                ',',
                array_map(fn($point) => $this->encodeInteger(($point[0] << 10) + $point[1]), $points)
            ) . '|';
        }
        return rtrim($return, '|');
    }

    public function encodeInteger(int $number)
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