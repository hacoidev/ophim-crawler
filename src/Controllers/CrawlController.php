<?php

namespace Ophim\Crawler\OphimCrawler\Controllers;


use Ophim\Core\Requests\MovieRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Ophim\Crawler\OphimCrawler\Crawler;

/**
 * Class CrawlController
 * @package Ophim\Crawler\OphimCrawler\Controllers
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CrawlController extends CrudController
{
    public function fetch(Request $request)
    {
        $data = collect();

        $request['link'] = preg_split('/[\n\r]+/', $request['link']);

        foreach ($request['link'] as $link) {
            if (preg_match('/(.*?)(\/phim\/)(.*?)/', $link)) {
                $response = json_decode(file_get_contents($link), true);
                $data->push(collect($response['movie'])->only('name', 'slug')->toArray());
            } else {
                for ($i = $request['from']; $i <= $request['to']; $i++) {
                    $response = json_decode(Http::timeout(3)->get($link, [
                        'page' => $i
                    ]), true);
                    if ($response['status']) {
                        $data->push(...$response['items']);
                    }
                }
            }
        }

        return $data;
    }

    public function showCrawlPage(Request $request)
    {
        $categories = Cache::remember('ophim_categories', config('ophim_cache_ttl', 5 * 60), function () {
            $data = json_decode(file_get_contents(sprintf('%s/the-loai', Setting::get('ophim_api_url', 'https://ophim1.com'))), true) ?? [];
            return collect($data)->pluck('name', 'name')->toArray();
        });

        $regions = Cache::remember('ophim_regions', config('ophim_cache_ttl', 5 * 60), function () {
            $data = json_decode(file_get_contents(sprintf('%s/quoc-gia', Setting::get('ophim_api_url', 'https://ophim1.com'))), true) ?? [];
            return collect($data)->pluck('name', 'name')->toArray();
        });

        return view('crawler::ophim-crawler.crawl', compact('regions', 'categories'));
    }

    public function crawl(Request $request)
    {
        $pattern = sprintf('%s/phim/{slug}', Setting::get('ophim_api_url', 'https://ophim1.com'));

        try {
            $link = str_replace('{slug}', $request['slug'], $pattern);
            (new Crawler($link, request('fields', []), request('excludedCategories', []), request('excludedRegions', [])))->handle();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
