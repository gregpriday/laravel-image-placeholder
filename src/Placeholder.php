<?php

namespace SiteOrigin\VoronoiPlaceholder;

use Illuminate\Support\Facades\Cache;
use SiteOrigin\VoronoiPlaceholder\Encoders\SimpleEncoder;
use SiteOrigin\VoronoiPlaceholder\Jobs\GeneratePlaceholder;

class Placeholder
{
    public static function cacheKey($url): string
    {
        return 'image_placeholders:' . $url . ':' . SimpleEncoder::ENCODING_VERSION;
    }

    /**
     * Get the placeholder value for a given URL, or dispatch the job
     *
     * @param $url
     * @return string
     */
    public static function getOrDispatch($url): string
    {
        return Cache::rememberForever(self::cacheKey($url), function() use ($url){
            // Dispatch the job to generate the placeholder
            GeneratePlaceholder::dispatch($url);
            return '';
        });
    }

}