<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psy\Readline\Hoa\ConsoleException;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Illuminate\Console\Command;
use Illuminate\Console\Application;
use Symfony\Component\Finder\SplFileInfo;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    protected function loadControllers() {

    }

    /**
     * @throws \ReflectionException
     */
    protected function loadCommands($paths)
    {
        try {
            $paths = array_unique(Arr::wrap($paths));
            $paths = array_filter($paths, function ($path) {
                return is_dir($path);
            });

            if (empty($paths)) {
                return;
            }

            $namespace = $this->app->getNamespace();
            foreach (Finder::create()->in($paths)->files() as $file) {
                $command = $this->commandClassFromFile($file, $namespace);
                if (is_subclass_of($command, Command::class) &&
                    ! (new ReflectionClass($command))->isAbstract()) {
                    Application::starting(static function ($artisan) use ($command) {
                        $artisan->resolve($command);
                    });
                }
            }
        } catch (ConsoleException $exception) {

        }
    }
    protected function commandClassFromFile(SplFileInfo $file, string $namespace): string
    {
        return $namespace.str_replace(
            ['/', '.php'],
            ['\\', ''],
            Str::after($file->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
        );
    }
}
