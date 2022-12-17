<?php

namespace App\AOC;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;

class AdventOfCode
{
    const API = 'https://adventofcode.com';

    private static function getFromSite(string $url): string
    {
        $authID = env('AOC_TOKEN');
        $context = stream_context_create([
            'http' => [
                'header' => "Cookie: session=$authID\r\n",
            ],
        ]);

        return (string) file_get_contents(
            sprintf('%s/%s', self::API, $url),
            false,
            $context
        );
    }

    public static function fetchTaskDescription(int $year, int $day): array
    {
        $rawDescription = self::getFromSite(
            sprintf('%d/day/%d', $year, $day)
        );

        preg_match_all('/<article class="day-desc">(.*?)<\/article>/s', $rawDescription, $match);
        preg_match_all('/<p>Your puzzle answer was <code>(.*?)<\/code>.<\/p>/s', $rawDescription, $answer);
        preg_match('/<p class="day-success">Both parts of this puzzle are complete! They provide two gold stars: (.*?)<\/p>/s', $rawDescription, $success);

        $converter = new HtmlConverter();

        $description['part1'] = $converter->convert($match[1][0] ?? '');
        $description['part2'] = $converter->convert($match[1][1] ?? '');
        $description['answer1'] = $converter->convert($answer[1][0] ?? '');
        $description['answer2'] = $converter->convert($answer[1][1] ?? '');
        $description['success'] = isset($success[1]);

        return $description;
    }

    public static function createFiles(int $year, int $day): int
    {
        try {
            if (! Storage::exists(AdventOfCode::getSuccessFilename($year, $day))) {
                $adventDay = AdventOfCode::fetchTaskDescription($year, $day);
                Storage::delete([AdventOfCode::getDescriptionFilename($year, $day).'-part1.md', AdventOfCode::getDescriptionFilename($year, $day).'-part2.md']);
                Storage::put(AdventOfCode::getDescriptionFilename($year, $day).'-part1'.($adventDay['answer1'] !== '' ? '(√)' : '').'.md', $adventDay['part1']);
                Storage::put(AdventOfCode::getDescriptionFilename($year, $day).'-part2'.($adventDay['answer2'] !== '' ? '(√)' : '').'.md', $adventDay['part2']);
                if ($adventDay['success']) {
                    Storage::put(AdventOfCode::getSuccessFilename($year, $day), $adventDay['answer1']."\n".$adventDay['answer2']);
                }
            }
            if (! Storage::exists(AdventOfCode::getInputFilename($year, $day))) {
                Storage::put(AdventOfCode::getInputFilename($year, $day), AdventOfCode::fetchTaskInput($year, $day));
            }
            if (! Storage::exists(AdventOfCode::getSampleFilename($year, $day))) {
                Storage::put(AdventOfCode::getSampleFilename($year, $day), '');
            }
        } catch (\Exception $e) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public static function fetchTaskInput(int $year, int $day): string
    {
        return trim(
            self::getFromSite(
                sprintf('%d/day/%d/input', $year, $day)
            )
        );
    }

    private static function getPath(int $year, int $day): string
    {
        return $year.'/'.$day;
    }

    public static function getInputFilename(int $year, int $day): string
    {
        return self::getPath($year, $day).'/input.txt';
    }

    public static function getSampleFilename(int $year, int $day): string
    {
        return self::getPath($year, $day).'/sample.txt';
    }

    public static function getDescriptionFilename(int $year, int $day): string
    {
        return self::getPath($year, $day).'/description';
    }

    public static function getSuccessFilename(int $year, int $day): string
    {
        return self::getPath($year, $day).'/finishWithSuccess';
    }
}
