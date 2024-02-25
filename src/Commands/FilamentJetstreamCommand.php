<?php

namespace FilamentJetstream\FilamentJetstream\Commands;

use Illuminate\Console\Command;

class FilamentJetstreamCommand extends Command
{
    public $signature = 'filament-jetstream';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
