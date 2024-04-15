<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Providers;

use Carbon\Laravel\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;

class LogicServiceProvider extends ServiceProvider
{

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $scanDir = app()->path('/Logic/Impls');
        $files = $this->scanForFiles($scanDir);
        foreach ($files as $file) {
            $ns = $this->convertPathToNamespace($file);
            $reflectionClazz = new ReflectionClass($ns);
            $interfaces = $reflectionClazz->getInterfaceNames();
            foreach ($interfaces as $interfaceClazz) {
                app()->singleton($interfaceClazz, fn() => new $ns());
            }
        }
    }

    public static function scanForFiles(string $basePath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $files = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isFile() && str_contains($file->getFilename(), 'Logic.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Converts a file path to a namespace
     *
     * @param  string  $filePath  The file path to be converted to a namespace
     * @return string The namespace corresponding to the given file path
     */
    public static function convertPathToNamespace(string $filePath): string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $isWindows && $filePath = str_replace('/', '\\', $filePath);
        $explodedPath = explode(DIRECTORY_SEPARATOR, $filePath);
        $explodedPath = collect($explodedPath)->filter(fn ($path) => ! empty($path))->toArray();
        $indexOfApp = array_search('app', $explodedPath);
        $namespaceArray = array_slice($explodedPath, $indexOfApp);
        $namespace = implode(DIRECTORY_SEPARATOR, $namespaceArray);
        $isWindows && str_replace('/', '', $namespace);
        ! $isWindows && $namespace = 'App/'.$namespace;
        ! $isWindows && $namespace = str_replace('/', '\\', $namespace);
        return ucfirst(str_replace('.php', '', $namespace));
    }
}
