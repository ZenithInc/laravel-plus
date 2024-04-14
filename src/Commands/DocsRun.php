<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Symfony\Component\Process\Process;

class DocsRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $process = Process::fromShellCommandline('cd docs && npm run docs:dev');
        $process->setTimeout(null);
        $this->info('Docs development server is start on http://localhost:5173');
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $this->error('Docs development server failed to start!');
        }
    }
}
