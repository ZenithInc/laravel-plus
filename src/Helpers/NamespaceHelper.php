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
        $explodedPath = array_values(array_filter($explodedPath, fn ($path) => ! empty($path)));
        $indexOfApp = -1;
        foreach ($explodedPath as $index => $part) {
            if ($part === 'app') {
                $indexOfApp = $index;
            }
        }
        $namespaceArray = array_slice($explodedPath, $indexOfApp);
        $namespace = implode('\\', $namespaceArray);
        return ucfirst(str_replace('.php', '', $namespace));
    }
}
