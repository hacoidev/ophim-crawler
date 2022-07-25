<?php

namespace Ophim\Crawler\OphimCrawler;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Ophim\Crawler\OphimCrawler\Console\InstallCommand;

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
        config(['ophim.updaters' => array_merge(config('ophim.updaters', []), [
            [
                'name' => 'Ophim',
                'handler' => Crawler::class,
                'index' => '/admin/movie/crawl',
                'options' => [
                    [
                        'name' => 'domain',
                        'label' => 'API Domain',
                        'type' => 'text',
                        'value' => 'https://ophim1.com'
                    ],
                    [
                        'name' => 'download_image',
                        'label' => 'Tải ảnh khi crawl',
                        'type' => 'checkbox',
                    ],
                ]
            ]
        ])]);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crawler');

        $this->commands([
            InstallCommand::class
        ]);
    }
}
