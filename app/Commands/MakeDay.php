<?php

namespace App\Commands;

use App\AOC\AdventOfCode;
use Illuminate\Foundation\Console\ConsoleMakeCommand;

class MakeDay extends ConsoleMakeCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:day {year} {day}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected function getNameInput()
    {
        return 'day'.trim($this->argument('day'));
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        $stub = str_replace(['{{ year }}', '{{year}}'], $this->argument('year'), $stub);
        $stub = str_replace(['{{ day }}', '{{day}}'], $this->argument('day'), $stub);

        return str_replace(['dummy:command', '{{ command }}'], $this->argument('year').':'.$this->argument('day'), $stub);
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Commands\AOC'.$this->argument('year');
    }

    protected function getStub(): string
    {
        $relativePath = '/stubs/resolveAdventOfCodeDay.stub';

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    protected function getAocDatas(int $year, int $day): int
    {
        $AdventDay = AdventOfCode::fetchTaskDescription($year, $day);

        return AdventOfCode::createFiles($year, $day);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($this->getNameInput())
        ) {
            $this->components->error($this->type.' already exists.');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $info = $this->type;

        $this->components->info(sprintf('%s [%s] created successfully.', $info, $path));

        $this->components->info(sprintf('Get Advent Of Code Inputs'));

        return $this->getAocDatas($this->argument('year'), $this->argument('day'));
    }
}
