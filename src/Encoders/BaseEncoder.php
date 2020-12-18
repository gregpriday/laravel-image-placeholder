<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use GuzzleHttp\Psr7\Stream;
use Imagick;

abstract class BaseEncoder
{
    const IMAGE_SIZE = 512;
    const DEFAULT_POINT_COUNT = 250;
    const COLOR_COUNT = 16;

    const CHARS = '!#$%&()*+-.0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{}~ ';

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

        $this->img->resizeImage(self::IMAGE_SIZE, self::IMAGE_SIZE, Imagick::FILTER_POINT, 1, true);
    }

    /**
     * Encode an integer into a dense string.
     *
     * @param int $number
     * @return string
     */
    protected function encodeInteger(int $number): string
    {
        $return = [];
        while($number > 0) {
            $rem = $number % strlen(self::CHARS);
            array_unshift($return, self::CHARS[$rem]);
            $number -= $rem;
            $number /= strlen(self::CHARS);
        }

        return implode('', $return);
    }

    /**
     * @return string The main generated placeholder.
     */
    abstract public function encode(): string;
}