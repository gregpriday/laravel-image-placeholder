<?php

namespace SiteOrigin\VoronoiPlaceholder;

use GuzzleHttp\Psr7\Stream;
use Imagick;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class Encoder
{
    CONST IMAGE_SIZE = 52;
    CONST IMAGE_COLORS = 16;

    protected $img;

    protected $processed;

    /**
     * Generator constructor.
     *
     * @param string $src
     * @throws \ImagickException
     */
    public function __construct(string $src)
    {
        // Load the main image
        $this->img = new Imagick();

        if (is_string($src)) {
            $this->img->readImage($src);
        }
        elseif (is_resource($src) && (get_resource_type($src) == 'file' || get_resource_type($src) == 'stream')) {
            $this->img->readImageFile($src);
        }
        else if(is_a($src, Stream::class)) {
            $this->img->readImageBlob($src->getContents());
        }

        $this->processed = false;
    }

    public function encode($filter = Imagick::FILTER_BOX): string
    {
        $img = clone $this->img;
        $img->resizeImage(self::IMAGE_SIZE, self::IMAGE_SIZE, $filter, 1, true);
        $img->gaussianBlurImage(2.5, 1.25);
        $img->quantizeImage(self::IMAGE_COLORS, Imagick::COLORSPACE_RGB, 0, false, false);

        $tmpFile = tempnam(sys_get_temp_dir(), "image") . '.png';
        $img->writeImage($tmpFile);

        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tmpFile);
        $encoded = base64_encode(file_get_contents($tmpFile));
        unlink($tmpFile);

        return $encoded;
    }

}