<?php

namespace Ophim\Crawler\OphimCrawler\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Ophim\Crawler\OphimCrawler\Crawler;
use Ophim\Crawler\OphimCrawler\Option;

class CrawlerScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ophim:plugins:ophim-crawler:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler movie schedule command';

    protected $logger;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->logger = Log::channel('ophim-crawler');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!$this->checkCrawlerScheduleEnable()) return 0;
        $link = sprintf('%s/danh-sach/phim-moi-cap-nhat', Option::get('domain'));
        $data = collect();
        $page_from = Option::get('crawler_schedule_page_from', 1);
        $page_to = Option::get('crawler_schedule_page_to', 2);
        $this->logger->notice(sprintf("Crawler Page (FROM: %d | TO: %d)",  $page_from, $page_to));
        for ($i = $page_from; $i <= $page_to; $i++) {
            if(!$this->checkCrawlerScheduleEnable()) {
                $this->logger->notice(sprintf("Stop Crawler Page"));
                return 0;
            }
            $response = json_decode(Http::timeout(30)->get($link, [
                'page' => $i
            ]), true);
            if ($response['status'] && count($response['items'])) {
                $data->push(...$response['items']);
            }
        }
        $movies = $data->shuffle();
        $count_movies = count($movies);
        $this->logger->notice(sprintf("Start Crawler Movies (TOTAL: %d)",  $count_movies));
        $count_error = 0;
        foreach ($movies as $key => $movie) {
            try {
                if(!$this->checkCrawlerScheduleEnable()) {
                    $this->logger->notice(sprintf("Stop Crawler Movies (TOTAL: %d | CRAWED: %d | ERROR %d)", $count_movies, $key, $count_error));
                    return 0;
                }
                $link = sprintf('%s/phim/%s', Option::get('domain'), $movie['slug']);
                $crawler = (new Crawler(
                    $link,
                    Option::get('crawler_schedule_fields', Option::getAllOptions()['crawler_schedule_fields']['default']),
                    Option::get('crawler_schedule_excludedCategories', []),
                    Option::get('crawler_schedule_excludedRegions', []),
                    Option::get('crawler_schedule_excludedType', []),
                    false))
                    ->handle();
            } catch (\Exception $e) {
                $this->logger->error(sprintf("%s ERROR: %s", $movie['slug'], $e->getMessage()));
                $count_error++;
            }
        }
        $this->logger->notice(sprintf("Finish Crawler Movies (TOTAL: %d | DONE: %d | ERROR: %d)", $count_movies, $count_movies - $count_error, $count_error));
        return 0;
    }

    public function checkCrawlerScheduleEnable()
    {
        return Option::get('crawler_schedule_enable', false);
    }
}
