<?php

namespace SiteOrigin\VoronoiPlaceholder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use SiteOrigin\VoronoiPlaceholder\Encoders\SimpleEncoder;
use SiteOrigin\VoronoiPlaceholder\Events\PlaceholderGenerated;
use SiteOrigin\VoronoiPlaceholder\Placeholder;

class GeneratePlaceholder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $url;

    public function __construct(string $url)
    {

        $this->url = $url;
    }

    /**
     * @throws \ImagickException
     */
    public function handle()
    {
        // Encode the image into a placeholder
        $encoder = new SimpleEncoder($this->url);

        Cache::put(Placeholder::cacheKey($this->url), $encoder->encode());

        // Dispatch an event
        PlaceholderGenerated::dispatch($this->url);
    }
}