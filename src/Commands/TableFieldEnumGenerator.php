<?php

namespace Zenith\LaravelPlus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TableFieldEnumGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate table field enum';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tables = DB::getSchemaBuilder()->getTables();
        $bar = $this->output->createProgressBar(count($tables));
        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table['name']);
            $info[$table['name']] = [
                'comment' => $table['comment'],
                'columns' => $columns,
            ];
            $className = Str::singular(Str::ucfirst(Str::camel($table['name']))).'Fields';
            $fileName = $className.'.php';
            $path = app()->path('Enums/Tables/'.$fileName);
            $content = '<?php'."\n";
            $content .= "\n".'declare(strict_types=1);'."\n";
            $content .= "\n"."namespace App\Enums\Tables;"."\n";
            $content .= "\n".'enum '.$className.': string'."\n";
            $content .= '{'."\n";
            $content .= "    const _TABLE_NAME = '${table['name']}';"."\n";
            $content .= "\n    const _TABLE_COMMENT = '${table['comment']}';"."\n";
            $content .= "    const array COLUMNS = ['".implode("', '", $columns)."'];"."\n";
            foreach ($columns as $column) {
                $content .= "\n".'    case '.Str::upper($column)." = '".$column."';"."\n";
            }
            $content .= '}'."\n";
            File::put($path, $content);
            $bar->advance();
        }
        $bar->finish();
        $this->info("\n");
        $this->info('Table field enumeration generated!');
    }
}
