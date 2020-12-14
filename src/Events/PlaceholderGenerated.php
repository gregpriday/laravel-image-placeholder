<?php

namespace SiteOrigin\VoronoiPlaceholder\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceholderGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }
}