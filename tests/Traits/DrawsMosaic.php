<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests\Traits;

use Imagick;
use ImagickDraw;
use sroze\voronoi\Nurbs\Point;
use sroze\voronoi\Nurbs\Voronoi;

trait DrawsMosaic
{
    /**
     * Draw the mosaic onto a clone of the reference image. This is for testing/development.
     *
     * @param array $points
     * @param $width
     * @param $height
     * @param bool $withCenters
     * @return \Imagick
     */
    protected function drawMosaic(string $encoded): Imagick
    {
        $img = new Imagick();
        $img->readImageBlob(base64_decode($encoded));

        $r = clone $img;
        $r->resizeImage(1024, 1024, Imagick::FILTER_GAUSSIAN, 1, true);
        $r->setFormat('jpeg');
        return $r;
    }
}