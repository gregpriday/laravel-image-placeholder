<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests;

use Imagick;
use PHPUnit\Framework\TestCase;
use SiteOrigin\VoronoiPlaceholder\Encoders\MosaicEncoder;
use SiteOrigin\VoronoiPlaceholder\Encoders\SimpleEncoder;

class TestGenerate extends TestCase
{
    public function test_generate_minimal_voronoi()
    {
        $vd = new SimpleEncoder(__DIR__ . '/images/andrew-pons-lylCw4zcA7I-unsplash.jpg');
        $s = $vd->encode();
        $this->assertNotEmpty($s);
    }

    public function test_generate_mosaic_encoder()
    {
        $vd = new MosaicEncoder(__DIR__ . '/images/andrew-pons-lylCw4zcA7I-unsplash.jpg');
        $s = $vd->encode();
        dd(strlen($s));

        $this->assertNotEmpty($s);
    }

}