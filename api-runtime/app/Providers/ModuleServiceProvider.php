<?php

namespace App\Providers;

use FileSystemIterator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psy\Readline\Hoa\ConsoleException;
use ReflectionClass;
use ReflectionException;
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
        $modules = config('modules');
        collect(config('modules'))->each(function ($module) {
            if ($module['active'] &&
                is_dir($module['path']) &&
                !(new FileSystemIterator($module['path']))->valid()
            ) {
                return false; // exit from iteration if directory is empty
            }{
                $this->loadModuleRoutes($module['path']);
            }
            return true;
        });
    }

    protected function loadModuleRoutes($module): void
    {
        $this->loadRoutesFrom($module);
    }

    protected function classNameFromFile(SplFileInfo $file, string $namespace): string
    {
        return $namespace.str_replace(
            ['/', '.php'],
            ['\\', ''],
            Str::after($file->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
        );
    }

    public function persistResource(string $command): void
    {
        Application::starting(static function ($artisan) use ($command) {
            $artisan->resolve($command);
        });
    }

    public function modulesCollection($modules, $moduleResourceDir): Collection
    {
        return collect($modules)
            ->each(function ($modules) use ($moduleResourceDir) {
                $commandDirectory = $modules . $moduleResourceDir;
                if (is_dir($commandDirectory) && !(new FileSystemIterator($commandDirectory))->valid()) {
                    return false; // exit from iteration if directory is empty
                }
                return true;
            });
    }

    public function persistResourceCollection(Finder $finder, string $resourceDirectory): void
    {
        $resourceFiles = collect(
            $finder->files()->in($resourceDirectory)->name('*.php')
        )->getIterator();
        $resourceFiles->each(function ($commandFile) {
            $command = $this->classNameFromFile($commandFile, app()->getNamespace());
            $this->persistResource($command);
        });
    }

    /**
     * This works for :
     * Commands, Services
     * Controllers, Models, requests,
     *
     * @param $modules
     * @return void
     */
    protected function loadModuleResorces($modules): void
    {
        try {
            $moduleCollection = $this->modulesCollection($modules, '/Console/Commands/');
            if ($moduleCollection->count() === 0) {
                return;
            }

            $finder = new Finder();
            $this->persistResourceCollection($finder, '/Console/Commands/');

        } catch (ConsoleException $exception) {
            Log::getLogger()->error(
                'WE COULD NOT REGISTER MODULE COMMANDS WITH THE SERVICE CONTAINER',
                $
            $exception->getCode()
            );
        }
    }

    protected function loadAllMigrations(array $modules) : void
    {
        collect($modules)->each(static function ($module) {
            $finder = new Finder();
            $migrationPath = $module . '/database/migrations';
            if (!is_dir($migrationPath) || $finder->files()->count() === 0) {
                return false;
            }
            $migrationFiles = collect(
                $finder->files()->in($migrationPath)->name('*.php')->getIterator()
            );
            $migrationFiles->each(function ($migrationFile) {
                $this->loadMigrationsFrom($migrationFile->getReaPath());
            });
            return true;
        });
    }

    private function exportMigrations(int|string $module)
    {
    }

    private function exportFactories(int|string $module)
    {
    }
}
