<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zenith\LaravelPlus\Providers\LogicServiceProvider;

class GenerateLogicFactoryStub extends Command
{

    protected $signature = 'stub:logic';

    protected $description = 'Scan logic interface, generate factory stub';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $repositoryDir = app()->path('Logic'.DIRECTORY_SEPARATOR.'Interfaces');
        $interfaceFiles = self::scanForInterfaces($repositoryDir);
        $files = collect();
        foreach ($interfaceFiles as $file) {
            $files->add($file);
        }
        $this->generateStubFile($files);
    }

    private static function scanForInterfaces(string $repositoryDir): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($repositoryDir);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);

        $repositoryFiles = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (str_contains($file->getFilename(), 'Logic.php')) {
                $repositoryFiles[] = $file->getPathname();
            }
        }

        return $repositoryFiles;
    }

    /**
     * Generates a stub file for the RepoFactory class.
     *
     * @param  Collection  $files  A collection of file paths.
     */
    private function generateStubFile(Collection $files): void
    {
        $code = '<?php'.PHP_EOL;
        $code .= 'declare(strict_types=1);'.PHP_EOL.PHP_EOL;

        $code .= 'namespace App\Stubs;'.PHP_EOL.PHP_EOL;

        $classes = [];
        foreach ($files as $file) {
            $ns = LogicServiceProvider::convertPathToNamespace($file);
            $tokens = explode('\\', $ns);
            $classes[] = $tokens[count($tokens) - 1];
            $code .= 'use '.$ns.';'.PHP_EOL;
        }
        $code .= PHP_EOL.'class LogicFactoryStub'.PHP_EOL;
        $code .= '{'.PHP_EOL.PHP_EOL;

        foreach ($classes as $clazz) {
            $code .= "\tpublic static function create".$clazz.'(): '.$clazz.PHP_EOL;
            $code .= "\t{".PHP_EOL;
            $code .= "\t\t"."return app($clazz::class);".PHP_EOL;
            $code .= "\t}".PHP_EOL;
        }

        $code .= '}'.PHP_EOL;

        file_put_contents(app()->path('Stubs').DIRECTORY_SEPARATOR.'LogicFactoryStub.php', $code);
    }
}
