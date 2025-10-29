<?php

namespace FarmCHAT\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FarmCHAT:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the FarmCHAT assets';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if($this->option('force')){
            $this->call('vendor:publish', [
                '--tag' => 'FarmCHAT-config',
                '--force' => true,
            ]);

            $this->call('vendor:publish', [
                '--tag' => 'FarmCHAT-migrations',
                '--force' => true,
            ]);

            $this->call('vendor:publish', [
                '--tag' => 'FarmCHAT-models',
                '--force' => true,
            ]);
        }

        $this->call('vendor:publish', [
            '--tag' => 'FarmCHAT-views',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'FarmCHAT-assets',
            '--force' => true,
        ]);
    }
}
