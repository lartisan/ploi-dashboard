<?php

namespace Lartisan\PloiDashboard\Commands;

use Illuminate\Console\Command;

class PloiDashboardCommand extends Command
{
    public $signature = 'ploi-dashboard';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
