<?php

namespace SiteOrigin\VoronoiPlaceholder\Tests\Traits;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use SiteOrigin\VoronoiPlaceholder\Encoders\Point;
use sroze\voronoi\Nurbs\Voronoi;

trait DrawsMosaic
{
    /**
     * Draw the mosaic onto a clone of the reference image. This is for testing/development.
     *
     * @param array $points
     * @param $width
     * @param $height
     * @param bool $withCenters
     * @return \Imagick
     */
    protected function drawMosaic(array $points, $width, $height, $withCenters = false): Imagick
    {
        // Create the voronoi diagram to be dawn.
        $bound = (object) ['xl' => 0, 'xr' => $width, 'yt' => 0, 'yb' => $height];
        $sites = array_map(function(Point $point){
            return $point->asNurbsPoint();
        }, $points);
        $voronoi = new Voronoi();
        $diagram = $voronoi->compute($sites, $bound);


        // Draw the mosaic
        $m = new Imagick();
        $m->newImage($width, $height, new ImagickPixel('black'));
        $m->setImageFormat('png');
        foreach($diagram['cells'] as $cell) {
            $poly = [];
            if(!empty($cell->_halfedges)) {
                foreach ($cell->_halfedges as $edge) {
                    $p = $edge->getStartPoint();
                    $poly[] = [ 'x' => $p->x, 'y' => $p->y ];
                }
            }

            // Draw the poly on the original image
            if( !empty($poly) ) {
                $draw = new ImagickDraw();
                $draw->setFillColor('#'.$cell->_site->color);
                $draw->polygon($poly);

                if($withCenters) {
                    // Add in a red center
                    $draw->setFillColor(new ImagickPixel('red'));
                    $draw->circle($cell->_site->x, $cell->_site->y, $cell->_site->x+2, $cell->_site->y+2);
                }

                $m->drawImage($draw);
            }
        }

        return $m;
    }
}