<?php


namespace ITLeague\Microservice\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class LanguageTableCreate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'microservice:languages-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the languages database table';

    /**
     * The filesystem instance.
     */
    protected Filesystem $files;
    protected Composer $composer;

    /**
     * Create a new failed queue jobs table command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Support\Composer $composer
     *
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function handle(): void
    {
        $table = 'languages';

        $this->replaceMigration(
            $this->createBaseMigration($table), $table, Str::studly($table)
        );

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     *
     * @param string $table
     *
     * @return string
     * @throws \Exception
     */
    protected function createBaseMigration(string $table): string
    {
        return $this->laravel['migration.creator']->create(
            'create_languages_table', $this->laravel->databasePath() . '/migrations'
        );
    }

    /**
     * Replace the generated migration with the failed job table stub.
     *
     * @param string $path
     * @param string $table
     * @param string $tableClassName
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function replaceMigration(string $path, string $table, string $tableClassName): void
    {
        $stub = str_replace(
            ['{{table}}', '{{tableClassName}}'],
            [$table, $tableClassName],
            $this->files->get(__DIR__ . '/../stubs/languages.stub')
        );

        $this->files->put($path, $stub);
    }
}
