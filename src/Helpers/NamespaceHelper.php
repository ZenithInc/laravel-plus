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
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        $explodedPath = explode(DIRECTORY_SEPARATOR, $filePath);
        $explodedPath = array_filter($explodedPath, fn ($path) => ! empty($path));
        $indexOfApp = array_search('app', $explodedPath);
        $namespaceArray = array_slice($explodedPath, $indexOfApp);
        $namespace = implode('\\', $namespaceArray);
        return ucfirst(str_replace('.php', '', $namespace));
    }
}
