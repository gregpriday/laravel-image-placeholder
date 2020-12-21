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

    public function test_generate_edge_encoder_image()
    {
        $vd = new EdgeEncoder('https://unsplash.com/photos/4VDRCoNuvE0/download?force=true&w=640');

        $points = $vd->findPoints();
        $s = $vd->encode($points);
        $this->assertNotEmpty($s);

        // Lets draw this image
        $scale = 2;
        $points = $points->map(function(Point $point) use ($scale) {
            $point->x *= $scale;
            $point->y *= $scale;
            return $point;
        });

        $m = $this->drawMosaic($points->toArray(), $vd->img->getImageWidth()*$scale, $vd->img->getImageHeight()*$scale);
        $m->blurImage($scale*16,$scale*8);
        $m->writeImage(__DIR__ . '/images/out.png');
        $this->assertFileExists(__DIR__ . '/images/out.png');
    }
}