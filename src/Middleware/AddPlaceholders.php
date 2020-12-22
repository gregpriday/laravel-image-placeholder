<?php

namespace SiteOrigin\VoronoiPlaceholder\Middleware;

use Closure;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Pool as GuzzlePool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SiteOrigin\VoronoiPlaceholder\SimpleBaseEncoder;
use SiteOrigin\VoronoiPlaceholder\Services\Placeholder;

/**
 * Laravel Middleware to add placeholder data to each image.
 *
 * Class AddPlaceholders
 *
 * @package SiteOrigin\VoronoiPlaceholder\Middleware
 */
class AddPlaceholders
{
    protected function cacheKey($url)
    {
        return 'placeholder_cache:' . $url . ':' . SimpleBaseEncoder::ENCODING_VERSION;
    }

    public function handle(Request $request, Closure $next){
        // Handle other middleware first.
        /* @var Response */
        $response = $next($request);

        if( $response->status() == 200 ) {
            // Load the document and
            preg_match('/charset=([^;"\\s]+|"[^;"]+")/i', $response->headers->get('content-type'), $matches);
            $doc = new DOMDocument('1.0', $matches[1] ?? 'UTF-8');
            libxml_use_internal_errors(true);
            $doc->loadHTML($response->getContent());
            libxml_clear_errors();

            $xpath = new DOMXPath($doc);
            $imgElements = $xpath->query('//img[contains(@class, "has-placeholder")]');
            if(count($imgElements)) {

                foreach($imgElements as $el) {
                    $el->setAttribute('data-placeholder', placeholder_base64($el->getAttribute('src')));
                }

                $response->setContent($doc->saveHTML());
            }
        }

        return $response;
    }
}