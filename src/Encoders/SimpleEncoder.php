<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use Imagick;
use SiteOrigin\VoronoiPlaceholder\Encoders\BaseEncoder;

class SimpleEncoder extends BaseEncoder
{
    const ENCODING_VERSION = 1;

    protected array $points;
    protected array $colors;

    /**
     * Generator constructor.
     *
     * @param string|resource $src
     * @throws \ImagickException
     */
    public function __construct($src)
    {
        parent::__construct($src);

        $this->points = [];
        $this->colors = [];
    }

    public function encode(): string
    {
        $this->generate();

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
     * Perform the actual calculations
     *
     * @param int $pointCount
     * @throws \ImagickPixelException
     */
    protected function generate($pointCount = self::DEFAULT_POINT_COUNT)
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
}