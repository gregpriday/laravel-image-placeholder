<?php

namespace SiteOrigin\VoronoiPlaceholder\Encoders;

use Imagick;
use ImagickDraw;
use sroze\voronoi\Nurbs\Point;
use sroze\voronoi\Nurbs\Voronoi;

class MosaicEncoder extends SimpleEncoder
{
    const ENCODING_VERSION = 1;


    protected function findPoints($count = self::DEFAULT_POINT_COUNT): array
    {
        $img = clone $this->img;
        $img->blurImage(10, 10);
        $img->quantizeImage(32, Imagick::COLORSPACE_RGB, 0, false, false);

        $xCols = (int) ceil($img->getImageWidth()/128);
        $yCols = (int) ceil($img->getImageHeight()/128);

        $points = [];

        for($xCol = 0; $xCol < $xCols; $xCol++) {
            for($yCol = 0; $yCol < $yCols; $yCol++) {
                $region = [
                    $xCol * floor($img->getImageWidth()/$xCols),
                    $yCol * floor($img->getImageHeight()/$yCols),
                    ($xCol+1) * floor($img->getImageWidth()/$xCols),
                    ($yCol+1) * floor($img->getImageHeight()/$yCols),
                ];

                $points = array_merge(
                    $points,
                    $this->optimizeRegion($region, $img, round(self::DEFAULT_POINT_COUNT/($xCols*$yCols)))
                );
            }
        }

        $this->points = array_map(fn($p) => [$p->x, $p->y], $points);
        return $this->points;
    }

    private function optimizeRegion($region, Imagick $refImage, $pointCount): array
    {
        $img = clone $refImage;
        $img->cropImage($region[2] - $region[0], $region[3] - $region[1], $region[0], $region[1]);

        $fitness = 0;
        $points = [];
        $noImprovement = 0;

        // The bound of the voronoi diagram
        $bound = (object) ['xl' => 0, 'xr' => $img->getImageWidth(), 'yt' => 0, 'yb' => $img->getImageHeight()];

        $runs = 0;
        do{
            $extraPoints = rand(0, 4);
            $newPoints = [];
            $count = $pointCount - 2 + $extraPoints;
            for($i = 0; $i < $count; $i++){
                $newPoints[] = new Point(rand(0, $img->getImageWidth()), rand(0, $img->getImageHeight()));
            }

            // Create a diagram for the first set of images.
            $voronoi = new Voronoi();
            $diagram = $voronoi->compute($newPoints, $bound);

            $mosaic = $this->drawMosaic($diagram, $img);

            $diff = $mosaic->compareImages($img, Imagick::METRIC_MEANSQUAREERROR)[1];
            $newFitness = (1-$diff) - ($extraPoints/8)*0.0025;

            if( $newFitness > $fitness ) {
                $noImprovement = 0;
                $points = $newPoints;
                $fitness = $newFitness;
            }
            else $noImprovement++;

            $runs++;

            if($noImprovement >= 16) break;
        } while(true);

        // Adjust the points so they start with the region
        return array_map(function($point) use ($region){
            return new Point($point->x + $region[0], $point->y + $region[1]);
        }, $points);
    }

    /**
     * Draw the mosaic onto a clone of the reference image.
     *
     * @param array $diagram
     * @param \Imagick $refImage
     * @return \Imagick
     */
    private function drawMosaic(array $diagram, Imagick $refImage): Imagick
    {
        // Draw the mosaic
        $m = clone $refImage;
        foreach($diagram['cells'] as $cell) {
            $poly = [];
            if(!empty($cell->_halfedges)) {
                foreach ($cell->_halfedges as $edge) {
                    $p = $edge->getStartPoint();
                    $poly[] = [ 'x' => $p->x, 'y' => $p->y ];
                }
            }

            // Draw the poly on the original image
            $draw = new ImagickDraw();
            $draw->setFillColor( $refImage->getImagePixelColor($cell->_site->x, $cell->_site->y) );
            $draw->polygon($poly);
            $m->drawImage($draw);
        }

        return $m;
    }
}