<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests;

use Imagick;
use PHPUnit\Framework\TestCase;
use SiteOrigin\VoronoiPlaceholder\Encoders\BaseEncoder;
use SiteOrigin\VoronoiPlaceholder\Encoders\EdgeEncoder;
use SiteOrigin\VoronoiPlaceholder\Encoders\Point;
use SiteOrigin\VoronoiPlaceholder\Tests\Traits\DrawsMosaic;

class TestGenerate extends TestCase
{
    use DrawsMosaic;

    public function test_generate_preview_image()
    {
        $vd = new EdgeEncoder('https://unsplash.com/photos/GVnUVP8cs1o/download?force=true&w=640');

        $points = $vd->findPoints();

        // Lets draw this image
        $points = array_map(function(Point $point){
            $point->x *= 2;
            $point->y *= 2;
            return $point;
        }, $points);

        $m = $this->drawMosaic($points, $vd->img->getImageWidth()*2, $vd->img->getImageHeight()*2);
        $m->blurImage(32,16);
        $m->writeImage(__DIR__ . '/images/out.png');
        $this->assertFileExists(__DIR__ . '/images/out.png');
    }

    public function test_generate_encode()
    {
        $vd = new EdgeEncoder('https://unsplash.com/photos/GVnUVP8cs1o/download?force=true&w=640');

        $s = $vd->encode();
        $this->assertNotEmpty($s);
    }
}