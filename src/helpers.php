<?php

use SiteOrigin\VoronoiPlaceholder\Placeholder;

if (! function_exists('encode_placeholder')) {

    function encode_placeholder($url): string
    {
        return Placeholder::getOrDispatch($url);
    }

}