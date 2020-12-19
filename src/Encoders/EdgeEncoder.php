<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use ImagickPixelException;

class EdgeEncoder extends BaseEncoder
{
    const ENCODING_VERSION = 1;
    const RANDOM_SEED = 59213;

    public function findPoints($count = self::DEFAULT_POINT_COUNT): array
    {
        // Create an image that identifies the edges
        $edges = clone $this->img;
        $edges->blurImage(10, 5);
        $edges->setImageType(Imagick::IMGTYPE_GRAYSCALE);
        $edges->quantizeImage(12, Imagick::COLORSPACE_RGB, 0, false, false);
        $edges->edgeImage(3);
        $edges->blurImage(3,1);
        $edges->normalizeImage();

        // Prioritize areas around the actual edges, instead of the edges themselves
        $edges->functionImage(Imagick::FUNCTION_SINUSOID, [1, -90]);

        // Use a predictable seed so we get the same image every time.
        srand(self::RANDOM_SEED);
        $points = [];
        $runs = 0;
        while(count($points) < $count) {
            $newPoint = new Point(
                rand(0, $edges->getImageWidth()),
                rand(0, $edges->getImageHeight())
            );

            try {
                $prob = $edges->getImagePixelColor($newPoint->x, $newPoint->y)->getColorValue(Imagick::COLOR_RED);
            } catch (ImagickPixelException $e) {
                $prob = 0.1;
            }

            if($prob > rand()/getrandmax()) {
                // Accept the color
                $points[] = $newPoint;

                // Draw in black to create a space around this point, so we don't clump points too closely
                $draw = new ImagickDraw();
                $draw->setFillColor(new ImagickPixel('black'));
                $draw->circle($newPoint->x, $newPoint->y, $newPoint->x + 3, $newPoint->y + 3);
                $edges->drawImage($draw);
            }
            $runs++;
            if ($runs >= 2000) break;
        }
        srand((double) microtime() * 1000000);

        // Lets add colors to the points
        $img = clone $this->img;
        $img->blurImage(10, 5);
        $img->quantizeImage(self::COLOR_COUNT, Imagick::COLORSPACE_RGB, 0, false, false);

        // Return the points with reference image set
        return array_map(function(Point $point) use ($img){
            $point->setPointColor($img);
            return $point;
        }, $points);
    }
}