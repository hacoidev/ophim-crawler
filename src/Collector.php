<?php

namespace Ophim\Crawler\OphimCrawler;

use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;

class Collector
{
    protected $fields;
    protected $payload;
    protected $forceUpdate;

    public function __construct(array $payload, array $fields, $forceUpdate)
    {
        $this->fields = $fields;
        $this->payload = $payload;
        $this->forceUpdate = $forceUpdate;
    }

    public function get(): array
    {
        $info = $this->payload['movie'] ?? [];
        $episodes = $this->payload['episodes'] ?? [];

        $data = [
            'name' => $info['name'],
            'origin_name' => $info['origin_name'],
            'publish_year' => $info['year'],
            'content' => $info['content'],
            'type' =>  $this->getMovieType($info, $episodes),
            'status' => $info['status'],
            'thumb_url' => $this->getThumbImage($info['slug'], $info['thumb_url']),
            'poster_url' => $this->getPosterImage($info['slug'], $info['poster_url']),
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
        ];

        return $data;
    }

    public function getThumbImage($slug, $url)
    {
        return $this->getImage(
            $slug,
            $url,
            Option::get('should_resize_thumb', false),
            Option::get('resize_thumb_width'),
            Option::get('resize_thumb_height')
        );
    }

    public function getPosterImage($slug, $url)
    {
        return $this->getImage(
            $slug,
            $url,
            Option::get('should_resize_poster', false),
            Option::get('resize_poster_width'),
            Option::get('resize_poster_height')
        );
    }


    protected function getMovieType($info, $episodes)
    {
        return $info['type'] == 'series' ? 'series'
            : ($info['type'] == 'single' ? 'single'
                : (count(reset($episodes)['server_data'] ?? []) > 1 ? 'series' : 'single'));
    }

    protected function getImage($slug, string $url, $shouldResize = false, $width = null, $height = null): string
    {
        if (!Option::get('download_image', false) || empty($url)) {
            return $url;
        }
        try {
            $url = strtok($url, '?');
            $filename = substr($url, strrpos($url, '/') + 1);
            $path = "images/{$slug}/{$filename}";

            if (Storage::disk('public')->exists($path) && $this->forceUpdate == false) {
                return Storage::url($path);
            }

            $img = Image::make($url);

            if ($shouldResize) {
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            Storage::disk('public')->put($path, null);

            $img->save(storage_path("app/public/" . $path));

            return Storage::url($path);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return '';
        }
    }
}
