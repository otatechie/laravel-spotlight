<?php

namespace AtoAugustine\Beacon\Commands;

use Illuminate\Console\Command;

class BeaconCommand extends Command
{
    public $signature = 'laravel-beacon';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
