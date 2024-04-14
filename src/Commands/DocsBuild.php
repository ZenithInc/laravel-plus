<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Zenith\LaravelPlus\Attributes\Routes\Alias;
use Zenith\LaravelPlus\Attributes\Routes\DeleteMapping;
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Routes\PostMapping;
use Zenith\LaravelPlus\Attributes\Routes\Prefix;
use Zenith\LaravelPlus\Attributes\Routes\PutMapping;
use Zenith\LaravelPlus\Attributes\Routes\Response;
use Zenith\LaravelPlus\Attributes\Validators\Param;
use Zenith\LaravelPlus\Helpers\ControllerHelper;
use Zenith\LaravelPlus\Helpers\MarkdownHelper;
use Zenith\LaravelPlus\Helpers\VitePressConfigHelper;

class DocsBuild extends Command
{
    /**
     * An array of class constants representing the Restful method attributes within the system.
     *
     * Each class defines the mapping of an HTTP method to endpoints within
     * the application, primarily used in the scanning and generating of API
     * documentation.
     */
    protected const array ROUTE_ATTRIBUTES = [
        GetMapping::class => 'GET',
        PostMapping::class => 'POST',
        PutMapping::class => 'PUT',
        DeleteMapping::class => 'DELETE',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan attributes in controllers, generate api docs.';

    /**
     * Execute the console command.
     *
     * @throws ReflectionException
     */
    public function handle(): void
    {
        $apis = $this->scanApiInfo();
        $this->generateDocs($apis);
    }

    /**
     * Generates API documentation from $apis array and save it in Markdown format in designated directory.
     * Each markdown file represents an API action.
     *
     * @param  array  $apis  The array of APIs to document. Each API is an associative array with 'namespace' and 'actions' keys.
     *                       'actions' itself is an array of action names.
     */
    private function generateDocs(array $apis): void
    {
        $docsDir = $this->getDocsDir();
        $this->deleteMdFilesExceptIndex($docsDir);
        $builder = new VitePressConfigHelper();
        $builder->nav('Home', '/')
            ->nav('Api', '/Menu/add')
            ->sidebar('Api');
        foreach ($apis as $api) {
            $dir = $this->createDirectoryFromClassNamespace($api['namespace'], $docsDir);
            foreach ($api['actions'] as $action) {
                $link = Str::replace('\\', '/', Str::after($dir, 'docs').'/'.$action['name']);
                $builder->sidebarAppendItem('Api', $action['name'], $link);
                $filename = $dir.DIRECTORY_SEPARATOR.$action['name'].'.md';
                File::put($filename, $this->generateApiContent($api, $action));
            }
        }
        File::put($docsDir.'/.vitepress/config.js', $builder->build());
    }

    /**
     * This private function generates API content using the provided $api and $action arrays.
     *
     * @param  array  $api:  This array contains relevant API data.
     * @param  array  $action:  This array contains action data such as 'params','name','method','path',and so on.
     * @return string: The function returns a string, which contains the generated API content in Markdown format.
     */
    private function generateApiContent(array $api, array $action): string
    {
        $params = collect($action['params'])->map(fn ($param) => array_values($param))->toArray();

        return (new MarkdownHelper())
            ->meta(['outline' => 'deep'])
            ->h1($action['name'].' API')
            ->table(['path', 'method', 'created_at'], [[$api['prefix'].$action['path'], $action['method'], Carbon::now()]])
            ->h2('接口传参')
            ->table(['name', 'rule', 'message'], $params)
            ->build();
    }

    /**
     * Creates a directory from a class namespace.
     *
     * This function takes a namespace and a base directory as parameters.
     * It determines a relative path based on the provided namespace,
     * replacing namespace separators with directory separators.
     * If the resulting directory does not exist, it creates it.
     *
     * @param  string  $namespace  Namespace from which to create directory.
     * @param  string  $baseDirectory  Base directory where new directory will be created.
     */
    public function createDirectoryFromClassNamespace(string $namespace, string $baseDirectory): string
    {
        $baseNamespace = 'App\Http\Controllers\\';
        $relativeNamespace = Str::after($namespace, $baseNamespace);
        if (empty($relativeNamespace)) {
            return $baseDirectory;
        }
        $relativeNamespace = Str::before($relativeNamespace, 'Controller');
        $relativeDirectory = Str::replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);
        $directory = $baseDirectory.'/'.$relativeDirectory;
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0775, true);
        }

        return $directory;
    }

    /**
     * The `deleteMdFilesExceptIndex` function is used to delete all markdown files in the 'docs'
     * directory of the public path, except for 'index.md'.
     */
    public function deleteMdFilesExceptIndex(string $directory): void
    {
        $files = File::files($directory);
        foreach ($files as $file) {
            if ($file->getExtension() == 'md' && $file->getFilename() != 'index.md') {
                File::delete($file->getPathname());
            }
        }
    }

    /**
     * Fetches and processes information about the API defined by controller files.
     *
     * @return array Collection of controller related details, including namespace and actions.
     *
     * @throws ReflectionException
     */
    private function scanApiInfo(): array
    {
        $controllerDir = app()->path('Http/Controllers');
        $files = ControllerHelper::scanForFiles($controllerDir);
        $controllerInfo = [];
        foreach ($files as $file) {
            $info = $this->getControllerInfo($file);
            $info['actions'] = $this->getActionsInfo($info['namespace']);
            // 读取方法注解
            $controllerInfo[] = $info;
        }

        return $controllerInfo;
    }

    /**
     * Get information about a controller.
     *
     * @param  string  $file  The path of the controller file.
     * @return array The controller information.
     *
     * @throws ReflectionException If an error occurs during the reflection process.
     */
    private function getControllerInfo(string $file): array
    {
        $info['path'] = $file;
        $ns = ControllerHelper::convertPathToNamespace($file);
        $info['namespace'] = $ns;
        // 读取类注解
        $reflectClazz = new ReflectionClass($ns);
        $clazzAttributes = collect($reflectClazz->getAttributes());
        $clazzAlias = $clazzAttributes
            ->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Alias::class)->first();
        $info['alias'] = $clazzAlias?->newInstance()->value;
        $clazzPrefix = $clazzAttributes
            ->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Prefix::class)->first();
        $info['prefix'] = $clazzPrefix?->newInstance()->value;

        return $info;
    }

    /**
     * Get information about the public methods of a given class.
     *
     * @param  string  $ns  The namespace of the class.
     * @return array An array containing information about the methods of the class.
     *
     * @throws ReflectionException if the given class does not exist.
     */
    private function getActionsInfo(string $ns): array
    {
        $reflectClazz = new ReflectionClass($ns);
        $methods = collect($reflectClazz->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method) => ! $method->isConstructor())->toArray();
        $infos = [];
        foreach ($methods as $method) {
            $infos[] = $this->getActionInfo(collect($method->getAttributes()), $method->getName());
        }

        return $infos;
    }

    /**
     * This method retrieves the information associated with an action.
     *
     * @param  Collection  $attributes  The attributes related to the action.
     * @param  string  $methodName  The name of the specific method being operated on.
     * @return ?array The information related to the action, or null if the route is not found.
     */
    private function getActionInfo(Collection $attributes, string $methodName): ?array
    {
        [$info['method'], $info['path']] = $this->getActionRoute($attributes);
        if ($info['path'] === null) {
            return null;
        }
        $info['name'] = $this->getActionAlias($attributes) ?? $methodName;
        $info['returnType'] = $this->getActionReturnType($attributes);
        $info['params'] = $this->getActionParameters($attributes);

        return $info;
    }

    /**
     * Handles the extraction of method parameters from a given collection of attributes.
     *
     * @param  Collection  $attributes  - A collection of ReflectionAttribute instances.
     * @return array - An array consisting of method parameters as associative arrays. Each parameter array
     *               contains 'key', 'rule', and 'message' derived from an instance of 'Param' class.
     */
    private function getActionParameters(Collection $attributes): array
    {
        $paramAttributes = $attributes->filter(
            fn (ReflectionAttribute $attribute) => $attribute->getName() === Param::class
        );
        $params = [];
        foreach ($paramAttributes as $paramAttribute) {
            $paramInstance = $paramAttribute->newInstance();
            $params[] = [
                'key' => $paramInstance->key,
                // 因为 `|` 和最终生成的 Markdown 中的表格语法冲突
                'rule' => Str::replace('|', ',', $paramInstance->rule),
                'message' => $paramInstance->message,
            ];
        }

        return $params;
    }

    /**
     * Gets the return type of method based on the provided attributes collection.
     *
     * @param  Collection  $attributes  A collection of ReflectionAttribute objects representing the attributes of the method.
     * @return string|null The return type of the method, or null if no return type is found.
     */
    private function getActionReturnType(Collection $attributes): ?string
    {
        $methodReturnType = $attributes->filter(
            fn (ReflectionAttribute $attribute) => $attribute->getName() === Response::class
        )->first();

        /** @var ReflectionAttribute $methodReturnType */
        return $methodReturnType?->newInstance()->clazz;
    }

    /**
     * Get the alias of a method based on its attributes.
     *
     * @param  Collection  $attributes  A collection of ReflectionAttribute objects representing the method's attributes.
     * @return string|null The alias of the method, or null if no Alias attribute is found.
     */
    private function getActionAlias(Collection $attributes): ?string
    {
        $methodAlias = $attributes->filter(
            fn (ReflectionAttribute $attribute) => $attribute->getName() === Alias::class
        )->first();

        /** @var ReflectionAttribute $methodAlias */
        return $methodAlias?->newInstance()->value;
    }

    /**
     * Get the route path from the given collection of attributes.
     *
     * @param  Collection  $attributes  The collection of attributes.
     * @return array The route path if found, null otherwise.
     */
    private function getActionRoute(Collection $attributes): array
    {
        $routeAttribute = $attributes->filter(
            fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), array_keys(self::ROUTE_ATTRIBUTES))
        )->first();

        /** @var ReflectionAttribute $routeAttribute */
        $path = $routeAttribute?->newInstance()->path;
        if ($path === null) {
            return [null, null];
        }
        $method = self::ROUTE_ATTRIBUTES[$routeAttribute->getName()] ?? 'undefined';

        return [$method, $path];
    }

    /**
     * Get the path to the directory containing the documentation files.
     *
     * @return string The path to the documentation directory.
     */
    private function getDocsDir(): string
    {
        return app()->basePath('docs');
    }
}
