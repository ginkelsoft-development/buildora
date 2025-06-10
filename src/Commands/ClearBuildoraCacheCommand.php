<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBuildoraCacheCommand extends Command
{
    protected $signature = 'buildora:cache:clear';
    protected $description = 'Clear the Buildora-specific cache store';

    public function handle(): int
    {
        $store = config('buildora.cache.store', 'buildora_file');

        Cache::store($store)->flush();

        $this->info("Buildora cache [{$store}] cleared.");
        return Command::SUCCESS;
    }
}
