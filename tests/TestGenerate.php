<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests;

use Imagick;
use PHPUnit\Framework\TestCase;
use SiteOrigin\VoronoiPlaceholder\Generator;

class TestGenerate extends TestCase
{
    public function test_generate_minimal_voronoi()
    {
        $vd = new Generator(__DIR__ . '/images/andrew-pons-lylCw4zcA7I-unsplash.jpg');
        $s = $vd->getPointsString();
        $this->assertNotEmpty($s);
    }

}