<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'Ophim\Crawler\OphimCrawler\Controllers',
], function () {
    Route::get('/movie/crawl', 'CrawlController@showCrawlPage');
    Route::get('/movie/crawl/fetch', 'CrawlController@fetch');
    Route::post('/movie/crawl', 'CrawlController@crawl');
});
