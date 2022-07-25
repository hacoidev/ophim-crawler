<?php

namespace Ophim\Crawler\OphimCrawler\Console;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:ophim:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Ophim crawler';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Setting::updateOrCreate();

        return 0;
    }
}
