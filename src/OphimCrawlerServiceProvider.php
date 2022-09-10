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
        config(['plugins' => array_merge(config('plugins', []), [
            'hacoidev/ophim-crawler' =>
            [
                'name' => 'Ophim Crawler',
                'package_name' => 'hacoidev/ophim-crawler',
                'icon' => 'la la-hand-grab-o',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/ophim-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/ophim-crawler/options')],
                ],
            ]
        ])]);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ophim-crawler');
    }
}
