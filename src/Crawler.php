<?php

namespace Ophim\Crawler\OphimCrawler;

use Ophim\Core\Models\Movie;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Ophim\Core\Models\Actor;
use Ophim\Core\Models\Category;
use Ophim\Core\Models\Director;
use Ophim\Core\Models\Episode;
use Ophim\Core\Models\Region;
use Ophim\Core\Models\Tag;
use Ophim\Core\Models\Studio;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Http;
use Ophim\Core\Crawler\BaseCrawler;

class Crawler extends BaseCrawler
{
    public function handle()
    {
        $payload = json_decode(file_get_contents($this->link), true);

        $this->checkIsInExcludedList($payload);

        $info = $this->transformData($payload);

        $movie = Movie::where('name', $info['name'])->where('origin_name', $info['origin_name'])->first();

        if ($movie) {
            if (!$this->canHandleUpdating($movie)) {
                return;
            }

            $movie->update(collect($info)->only($this->fields)->toArray());
        } else {
            $movie = Movie::create($info->merge(['update_handler' => static::class])->all());
        }

        $this->syncActors($movie, $payload);
        $this->syncDirectors($movie, $payload);
        $this->syncCategories($movie, $payload);
        $this->syncRegions($movie, $payload);
        $this->syncTags($movie, $payload);
        $this->syncStudios($movie, $payload);
        $this->updateEpisodes($movie, $payload);
    }

    public static function getMovieLinks(array $links, $from, $to): array
    {
        $list = [];
        $pattern = sprintf('%s/phim/{slug}', get_addon_option('ophim', 'domain', 'https://ophim1.com'));
        foreach ($links  as $link) {
            if (static::isSingleMovie($link)) {
                $list = array_merge($list, [$link]);
            } else {
                for ($i = $from; $i <= $to; $i++) {
                    $response = json_decode(Http::timeout(10)->get($link, [
                        'page' => $i
                    ]), true);

                    if (!$response['status']) {
                        continue;
                    }

                    foreach ($response['items'] as $item) {
                        $list = array_merge($list, [str_replace('{slug}', $item['slug'], $pattern)]);
                    }
                }
            }
        }

        return $list;
    }

    protected static function isSingleMovie($link)
    {
        return preg_match('/(.*?)(\/phim\/)(.*?)/', $link);
    }

    protected function canHandleUpdating(Movie $movie)
    {
        return $movie->update_handler == static::class;
    }

    protected function checkIsInExcludedList($payload)
    {
        $newCategories = collect($payload['movie']['category'])->pluck('name')->toArray();
        if (array_intersect($newCategories, $this->excludedCategories)) {
            throw new \Exception("In excluded categories");
        }

        $newRegions = collect($payload['movie']['country'])->pluck('name')->toArray();
        if (array_intersect($newRegions, $this->excludedRegions)) {
            throw new \Exception("In excluded regions");
        }
    }

    protected function transformData(array $payload): Collection
    {
        $info = $payload['movie'];
        $episodes = $payload['episodes'];

        $data = collect([
            'name' => $info['name'],
            'origin_name' => $info['origin_name'],
            'publish_year' => $info['year'],
            'content' => $info['content'],
            'type' =>  $this->getMovieType($info, $episodes),
            'status' => $info['status'],
            'thumb_url' => $this->getImage($info['slug'], $info['thumb_url']),
            'poster_url' => $this->getImage($info['slug'], $info['poster_url']),
            'is_copyright' => $info['is_copyright'] != 'off',
            'trailer_url' => $info['trailer_url'] ?? "",
            'quality' => $info['quality'],
            'language' => $info['lang'],
            'episode_time' => $info['time'],
            'episode_current' => $info['episode_current'],
            'episode_total' => $info['episode_total'],
            'notify' => $info['notify'],
            'showtimes' => $info['showtimes'],
            'is_shown_in_theater' => $info['chieurap'],
        ]);

        return $data;
    }

    protected function getMovieType($info, $episodes)
    {
        return $info['type'] == 'series' ? 'series'
            : ($info['type'] == 'single' ? 'single'
                : (count(reset($episodes)['server_data'] ?? []) > 1 ? 'series' : 'single'));
    }

    protected function getImage($slug, string $url): string
    {
        if (!get_addon_option('ophim', 'download_image', false) || empty($url)) {
            return $url;
        }

        try {
            $contents = file_get_contents($url);
            $filename = substr($url, strrpos($url, '/') + 1);
            $path = "images/{$slug}/{$filename}";
            Storage::disk('public')->put($path, $contents);
            return Storage::url($path);
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function syncActors($movie, array $payload)
    {
        if (!in_array('actors', $this->fields)) return;

        $actors = [];
        foreach ($payload['movie']['actor'] as $actor) {
            if (!trim($actor)) continue;
            $actors[] = Actor::firstOrCreate(['name' => trim($actor)])->id;
        }
        $movie->actors()->sync($actors);
    }

    protected function syncDirectors($movie, array $payload)
    {
        if (!in_array('directors', $this->fields)) return;

        $directors = [];
        foreach ($payload['movie']['director'] as $director) {
            if (!trim($director)) continue;
            $directors[] = Director::firstOrCreate(['name' => trim($director)])->id;
        }
        $movie->directors()->sync($directors);
    }

    protected function syncCategories($movie, array $payload)
    {
        if (!in_array('categories', $this->fields)) return;

        $categories = [];
        foreach ($payload['movie']['category'] as $category) {
            if (!trim($category['name'])) continue;
            $categories[] = Category::firstOrCreate(['name' => trim($category['name'])])->id;
        }
        $movie->categories()->sync($categories);
    }

    protected function syncRegions($movie, array $payload)
    {
        if (!in_array('regions', $this->fields)) return;

        $regions = [];
        foreach ($payload['movie']['country'] as $region) {
            if (!trim($region['name'])) continue;
            $regions[] = Region::firstOrCreate(['name' => trim($region['name'])])->id;
        }
        $movie->regions()->sync($regions);
    }

    protected function syncTags($movie, array $payload)
    {
        if (!in_array('tags', $this->fields)) return;

        $tags = [];
        $tags[] = Tag::firstOrCreate(['name' => trim($movie->name)])->id;
        $tags[] = Tag::firstOrCreate(['name' => trim($movie->origin_name)])->id;

        $movie->tags()->sync($tags);
    }

    protected function syncStudios($movie, array $payload)
    {
        if (!in_array('studios', $this->fields)) return;
    }

    protected function updateEpisodes($movie, $payload)
    {
        if (!in_array('episodes', $this->fields)) return;

        foreach ($payload['episodes'] as $server) {
            foreach ($server['server_data'] as $episode) {
                if ($episode['link_m3u8']) {
                    Episode::firstOrCreate([
                        'name' => $episode['name'],
                        'movie_id' => $movie->id,
                        'server' => $server['server_name'],
                        'type' => 'm3u8'
                    ], [
                        'link' => $episode['link_m3u8'],
                        'slug' => 'tap-' . $episode['name']
                    ]);
                }
                if ($episode['link_embed']) {
                    Episode::firstOrCreate([
                        'name' => $episode['name'],
                        'movie_id' => $movie->id,
                        'server' => $server['server_name'],
                        'type' => 'embed',
                    ], [
                        'link' => $episode['link_embed'],
                        'slug' => 'tap-' . $episode['name']
                    ]);
                }
            }
        }
    }
}
