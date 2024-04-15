<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

class NamespaceHelper
{

    /**
     * Converts a file path to a namespace
     *
     * @param  string  $filePath  The file path to be converted to a namespace
     * @return string The namespace corresponding to the given file path
     */
    public static function path2namespace(string $filePath): string
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
