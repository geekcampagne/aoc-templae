<?php

namespace App\Commands;

use App\AOC\AdventOfCode;
use LaravelZero\Framework\Commands\Command;
use Validator;

class UpdateDay extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'update:day {year} {day}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update AOC Day (For Part 2)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $validator = Validator::make([
            'year' => $this->argument('year'),
            'day' => $this->argument('day'),
        ], [
            'year' => ['required', 'integer', 'between:2015,2022'],
            'day' => ['required', 'integer', 'between:1,25'],
        ]);

        if ($validator->fails()) {
            return self::FAILURE;
        }

        return AdventOfCode::createFiles(
            $this->argument('year'),
            $this->argument('day')
        );
    }
}
