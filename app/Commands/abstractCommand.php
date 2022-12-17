<?php

namespace App\Commands;

use Exception;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

abstract class abstractCommand extends Command
{
    const PATH = '/';

    const YEAR = 1970;

    const DAY = 1;

    protected array $answers;

    protected array $fileRows;

    final protected function setAnswers($answer1 = null, $answer2 = null, $answer3 = null, $answer4 = null)
    {
        $this->answers[1] = $answer1;
        $this->answers[2] = $answer2;
        $this->answers['part1'] = $answer3;
        $this->answers['part2'] = $answer4;
    }

    final protected function getInputFilename(): string
    {
        return static::PATH.static::YEAR.'/'.static::DAY.'/input.txt';
    }

    final protected function getSampleFilename(): string
    {
        return static::PATH.static::YEAR.'/'.static::DAY.'/sample.txt';
    }

    protected function getDatas(bool $sample = false)
    {
        $file = $sample ? $this->getSampleFilename() : $this->getInputFilename();
        if (! storage::exists($file)) {
            throw new \Exception('File '.$file.' does not exist', 1);
        }

        $this->fileRows = explode("\n", Storage::get($file));
        if (trim(last($this->fileRows)) === '') {
            array_pop($this->fileRows);
        }
    }

    abstract public function resolvePart1(bool $sample = false);

    abstract public function resolvePart2(bool $sample = false);

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    final public function handle()
    {
        try {
            for ($i = 1; $i <= 2; $i++) {
                if ($this->answers[$i] === null) {
                    $this->components->error('No answer in configuration');

                    return Command::INVALID;
                }

                $resolver = match ($i) {
                    1 => 'resolvePart1',
                    2 => 'resolvePart2',
                };

                $answer = $this->$resolver(true);

                if ($answer === null) {
                    $this->components->error('No answer for part '.$i);

                    return Command::INVALID;
                }

                if ($answer === $this->answers[$i]) {
                    $this->components->info('Sample Part '.$i.' : '.$answer);
                    $partAnswer = $this->$resolver();
                    if (isset($this->answers['part'.$i])) {
                        if ($this->answers['part'.$i] === $partAnswer) {
                            $this->components->info('AOC Part '.$i.' : '.$this->$resolver());
                        } else {
                            $this->components->error('AOC Part '.$i.' : '.$this->$resolver().' instead of '.$this->answers['part'.$i]);
                        }
                    } else {
                        $this->components->warn('AOC Part '.$i.' : '.$this->$resolver());
                    }
                } else {
                    $this->components->error('Sample Part '.$i.' : '.$answer.' instead of '.$this->answers[$i]);
                }
            }
        } catch (Exception $e) {
            $this->components->error($e->getMessage());
        }
    }
}
