<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Collection;
use Imagick;

abstract class BaseEncoder
{
    const IMAGE_SIZE = 256;
    const DEFAULT_POINT_COUNT = 256;
    const COLOR_COUNT = 32;
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

    public function encode(Collection $points): string
    {
        return $points->groupBy('color')
            ->map(function($p, $c){
                return
                    self::encodeInteger(base_convert($c, 16, 10)) . ',' .
                    // We can chunk values if PHP supports 64 bit integers
                    $p->chunk(PHP_INT_SIZE == 8 ? 2 : 1)->map(function($pc){
                        // Find the combined integer
                        $combined = $pc->map(fn($p) => [$p->x, $p->y])
                            ->flatten()
                            ->map(fn($v, $i) => ($v << $i*8))
                            ->sum();
                        return self::encodeInteger($combined);
                    })->join(',');
            })->join('|');
    }

    public function decode()
    {

    }

    /**
     * Encode an integer into a dense string.
     *
     * @param int $number
     * @return string
     */
    public static function encodeInteger(int $number): string
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
}