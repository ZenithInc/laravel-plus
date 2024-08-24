<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Providers;

use Carbon\Laravel\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use Zenith\LaravelPlus\Attributes\Component;
use Zenith\LaravelPlus\Helpers\NamespaceHelper;

class ComponentScanProvider extends ServiceProvider
{

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $classes = $this->scanClasses(app()->path());
        foreach ($classes as $clazz) {
            $reflectionClazz = new ReflectionClass($clazz);
            $interfaces = $reflectionClazz->getInterfaceNames();
            foreach ($interfaces as $interfaceClazz) {
                app()->singleton($interfaceClazz, fn() => $reflectionClazz->newInstance());
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public function scanClasses(string $basePath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $classes = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isFile() && str_contains($file->getFilename(), '.php')) {
                $clazz = NamespaceHelper::path2namespace($file->getPathname());
                if (! class_exists($clazz)) {
                    continue;
                }
                $reflection = new ReflectionClass($clazz);
                if (! $reflection->getAttributes(Component::class)) {
                    continue;
                }
                $classes[] = $clazz;
            }
        }

        return $classes;
    }

}
