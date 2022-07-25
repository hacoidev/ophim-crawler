<?php

namespace Ophim\Crawler\OphimCrawler;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Http;
use Ophim\Core\Database\Seeders\MenusTableSeeder;
use Ophim\Core\Models\CrawlSchedule;

class ScheduledCrawler
{
    /**
     * @var CrawlSchedule
     */
    protected $schedule;

    public function __construct(CrawlSchedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function execute()
    {
        $links = $this->getMovieLinks();

        foreach ($links as $link) {
            (new Crawler($link, $this->schedule->fields))->handle();
        }

        (new MenusTableSeeder)->run();
    }

    protected function getMovieLinks(): array
    {
        $links =  preg_split('/[\n\r]+/', $this->schedule->link);

        $list = [];
        $pattern = sprintf('%s/phim/{slug}', Setting::get('ophim_api_url', 'https://ophim1.com'));
        foreach ($links  as $link) {
            if ($this->isSingleMovie($link)) {
                $list = array_merge($list, [$link]);
            } else {
                for ($i = $this->schedule->from_page; $i <= $this->schedule->to_page; $i++) {
                    $response = json_decode(Http::timeout(3)->get($link, [
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

    protected function isSingleMovie($link)
    {
        return preg_match('/(.*?)(\/phim\/)(.*?)/', $link);
    }
}
