<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $name = str_replace(['/' , '\\'] , '' , $name);
        $name = Str::studly($name);

        $servicePath = app_path("Services/{$name}.php");

        if(!File::exists(app_path('Services')))
        {
            File::makeDirectory(app_path('Services') , 0755 , true);
        }

        if(File::exists($servicePath))
        {
            $this->error("Service {$name} already exists !");
            return ;
        }

        $stub = <<<PHP
        <?php

        namespace App\Services;

        class {$name}
        {
            public function __construct()
            {
                //
            }
        }
        PHP;

        File::put($servicePath , $stub);
        $this->info("Service {$name} created successfully at app/Services/{$name}.php");
    }
}
