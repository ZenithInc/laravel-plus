<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use Zenith\LaravelPlus\Attributes\Component;
use Zenith\LaravelPlus\Attributes\Logic;
use Zenith\LaravelPlus\Attributes\Repository;
use Zenith\LaravelPlus\Attributes\Service;
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
                try {
                    $reflection = new ReflectionClass($clazz);
                } catch (ReflectionException $_) {
                    continue;
                }
                $isEmpty = collect($reflection->getAttributes())->filter(fn ($attribute) => in_array($attribute->getName(), [
                    Component::class, Logic::class, Service::class, Repository::class
                ]))->isEmpty();
                if ($isEmpty) {
                    continue;
                }
                $classes[] = $clazz;
            }
        }

        return $classes;
    }

}