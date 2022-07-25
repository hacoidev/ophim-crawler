<?php

namespace Ophim\Crawler\OphimCrawler;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;

class OphimCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {
        config(['ophim.crawlers' => array_merge(config('ophim.crawlers', []), [
            ScheduledCrawler::class => 'Ophim Crawler'
        ])]);

        config(['ophim.updaters' => array_merge(config('ophim.updaters', []), [
            Crawler::class => 'Ophim'
        ])]);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crawler');
    }
}
