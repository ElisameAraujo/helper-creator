<?php

namespace Elisame\HelperCreator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeHelperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helper:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new custom helper for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Helpers/{$name}.php");

        if (File::exists($path)) {
            $this->error("The helper {$name} already exists!");
            return;
        }

        File::ensureDirectoryExists(app_path('Helpers'));

        File::put($path, "<?php\n\nfunction {$name}() {\n    //Code of the helper\n}\n");

        $this->info("Helper '{$name}' created sucessful in App/Helpers.");

        $this->call('helpers:register');
    }
}
