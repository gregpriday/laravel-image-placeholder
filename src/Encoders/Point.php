<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use Exception;
use Imagick;
use ImagickPixelException;
use sroze\voronoi\Nurbs\Point as NurbsPoint;

class Point
{
    public int $x;
    public int $y;
    public string $color;

    public function __construct($x, $y)
    {
        $this->x = (int) $x;
        $this->y = (int) $y;
    }

    /**
     * Set and return the point color from the given reference image.
     *
     * @param \Imagick $img
     * @return string
     */
    public function setPointColor(Imagick $img): string
    {
        try {
            $color = $img->getImagePixelColor($this->x, $this->y)->getColor();
            $color = sprintf("%02x%02x%02x", $color['r'], $color['g'], $color['b']);
        } catch (Exception $e) {
            // Just return black if there's a problem
            $color = '000000';
        }
        return $this->color = $color;
    }

    /**
     * Get this as a Voronoi Nurbs Point
     *
     * @return NurbsPoint
     */
    public function asNurbsPoint(): NurbsPoint
    {
        $point = new NurbsPoint($this->x, $this->y);
        $point->color = $this->color ?: null;

        return $point;
    }

}