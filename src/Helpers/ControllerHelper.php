<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ControllerHelper
{
    /**
     * An array constant that contains a list of file name filters.
     * These filters are used in scanForFiles function to filter out files while traversing through directories.
     */
    private const array FILENAME_FILTERS = [
        'Controller.php',
    ];

    /**
     * This function is used to scan recursively through the directory provided,
     * and collect all the controller files present in it.
     *
     * @param  string  $basePath  - The base path where the function starts scanning for controller files.
     * @return array - Returns an array containing the pathname of all the controller
     */
    public static function scanForFiles(string $basePath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);

        $controllerFiles = [];
        foreach ($recursiveIterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir() || in_array($file->getFilename(), self::FILENAME_FILTERS)) {
                continue;
            }
            if (str_contains($file->getFilename(), 'Controller.php')) {

                $controllerFiles[] = $file->getPathname();
            }
        }

        return $controllerFiles;
    }

    /**
     * Given a file path, this function converts it into a namespace that can be used within the Laravel framework.
     * It looks for the 'Controllers' directory in the given path, replaces all directory
     * separator symbols with namespace separator symbols and removes the '.php' extension.
     *
     * @param  string  $filePath  - The full path of the controller file that needs to be converted to a namespace.
     * @return string - A string that constitutes the namespace for the controller file.
     */
    public static function convertPathToNamespace(string $filePath): string
    {
        $startAt = strpos($filePath, 'Controllers');
        $nameSpace = substr($filePath, $startAt);
        $nameSpace = str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace);

        return 'App\\Http\\'.str_replace('.php', '', $nameSpace);
    }
}