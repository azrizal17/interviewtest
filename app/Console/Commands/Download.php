<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DownloadController;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'output a report top agent monthly award points';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $down;
    public function __construct(DownloadController $down)
    {
        parent::__construct();
        $this->down = $down;   
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->down->getIndex();
    }
}
