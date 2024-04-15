<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Providers;

use Carbon\Laravel\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use Zenith\LaravelPlus\Helpers\NamespaceHelper;

class ServiceServiceProvider extends ServiceProvider
{

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $scanDir = app()->path('/Services');
        $files = $this->scanForFiles($scanDir);
        foreach ($files as $file) {
            $ns = NamespaceHelper::path2namespace($file);
            $reflectionClazz = new ReflectionClass($ns);
            app()->singleton($reflectionClazz->getName(), fn() => new $ns());
        }
    }

    public static function scanForFiles(string $basePath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $files = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isFile() && str_contains($file->getFilename(), 'Service.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

}
