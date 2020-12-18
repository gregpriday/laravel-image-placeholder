<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests;

use Imagick;
use PHPUnit\Framework\TestCase;
use SiteOrigin\VoronoiPlaceholder\Encoders\MosaicEncoder;
use SiteOrigin\VoronoiPlaceholder\Encoders\EdgeEncoder;
use SiteOrigin\VoronoiPlaceholder\Encoders\SimpleEncoder;

class TestGenerate extends TestCase
{
    public function test_generate_with_edge_encoder()
    {
        $vd = new EdgeEncoder('https://unsplash.com/photos/lylCw4zcA7I/download?force=true&w=640');
        $s = $vd->encode();
        $this->assertNotEmpty($s);
    }
}