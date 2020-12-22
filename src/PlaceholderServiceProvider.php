<?php

namespace SiteOrigin\VoronoiPlaceholder;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\VoronoiPlaceholder\Middleware\AddPlaceholders;

class PlaceholderServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        app('router')->aliasMiddleware('placeholder-images', AddPlaceholders::class);
    }
}