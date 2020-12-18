<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class EdgeEncoder extends BaseEncoder
{
    const ENCODING_VERSION = 1;
    const RANDOM_SEED = 59213;

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

    protected function findPoints($count = self::DEFAULT_POINT_COUNT): array
    {
        $img = clone $this->img;
        $img->blurImage(10, 10);
        $img->quantizeImage(32, Imagick::COLORSPACE_RGB, 0, false, false);

        $edges = clone $this->img;
        $edges->blurImage(15, 10);
        $edges->setImageType(Imagick::IMGTYPE_GRAYSCALE);
        $edges->quantizeImage(12, Imagick::COLORSPACE_RGB, 0, false, false);
        $edges->edgeImage(6);
        $edges->blurImage(6,2);
        $edges->normalizeImage();
        $edges->functionImage(Imagick::FUNCTION_SINUSOID, [1, -90]);

        // Use a predictable seed so we get the same image every time.
        srand(self::RANDOM_SEED);
        $this->points = [];
        $runs = 0;
        while(count($this->points) < $count) {
            $newPoint = [
                rand(0, $edges->getImageWidth()),
                rand(0, $edges->getImageHeight())
            ];
            $px = $edges->getImagePixelColor($newPoint[0], $newPoint[1]);

            $prob = $edges->getImagePixelColor($newPoint[0], $newPoint[1])->getColorValue(Imagick::COLOR_RED);
            $prob = ($prob+0.005)/1.005;
            if($prob > rand()/getrandmax()) {
                // Accept the color
                $this->points[] = $newPoint;

                // Draw in black so we're less likely to get a nearby point.
                $draw = new ImagickDraw();
                $draw->setFillColor(new ImagickPixel('rgb(0,0,0)'));
                $draw->circle($newPoint[0], $newPoint[1], $newPoint[0] + 8, $newPoint[1] + 8);
                $edges->drawImage($draw);
            }
            $runs++;
            if ($runs >= 1750) break;
        }
        srand((double) microtime() * 1000000);

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