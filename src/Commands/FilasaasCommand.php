<?php

namespace Mhmadahmd\Filasaas\Commands;

use Illuminate\Console\Command;

class FilasaasCommand extends Command
{
    public $signature = 'filasaas';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
