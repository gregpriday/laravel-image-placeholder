<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests;

use PHPUnit\Framework\TestCase;
use SiteOrigin\VoronoiPlaceholder\Encoder;
use SiteOrigin\VoronoiPlaceholder\Tests\Traits\DrawsMosaic;

class TestGenerate extends TestCase
{
    use DrawsMosaic;

    public function test_generate_reduction_encoder_image()
    {
        $images = [
            //'https://unsplash.com/photos/RpZxHPikR6A/download?force=true&w=640',
            //'https://unsplash.com/photos/WIlvkeCScuE/download?force=true&w=640',
            'https://unsplash.com/photos/R2YNRCpufl4/download?force=true&w=640',
            //'https://unsplash.com/photos/eqW1MPinEV4/download?force=true&w=640',
            //'https://unsplash.com/photos/7cH0XaQUZro/download?force=true&w=640',
            //'https://unsplash.com/photos/s3lTiHe5T8A/download?force=true&w=640',
            //'https://unsplash.com/photos/tvNDFJalLmY/download?force=true&w=640',
            //'https://unsplash.com/photos/BPrk2cOoCq8/download?force=true&w=640',
            //'https://unsplash.com/photos/dkCDYn5Sy0Y/download?force=true&w=640',
            //'https://unsplash.com/photos/vacdzbeuEHM/download?force=true&w=640',
            //'https://unsplash.com/photos/FALAUa2sHYU/download?force=true&w=640',
        ];


        foreach($images as $image){
            $vd = new Encoder($image);
            $s = $vd->encode();
            $this->assertNotEmpty($s);
        }

        $m = $this->drawMosaic($s);
        $m->blurImage(52,26);
        $m->writeImage(__DIR__ . '/images/out.jpg');
        $this->assertFileExists(__DIR__ . '/images/out.jpg');
    }
}