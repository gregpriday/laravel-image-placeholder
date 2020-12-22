<?php

use Illuminate\Support\Facades\Cache;
use SiteOrigin\VoronoiPlaceholder\Encoder;
use SiteOrigin\VoronoiPlaceholder\Placeholder;

if (! function_exists('encode_placeholder')) {

    function placeholder_base64($url): string
    {
        return Cache::rememberForever('placeholder_image::' . $url, function() use ($url){
            $encoder = new Encoder($url);
            return $encoder->encode();
        });
    }

}