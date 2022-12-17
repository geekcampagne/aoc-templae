# Create Laravel Command to resolve Advent Of Code Day

## Install

* git clone
* composer install
* add your AOC token in .env

## Create Day

php advent make:day 2022 15

* After Create you have a new command 2022:15
* Inputs and Description are in storage/YYYY/DD/
    * description-part1.md
    * description-part2.md (when part1 resolved)
    * finishWithSuccess (when part1 and part2 resolved)
    * input.txt (Your inputs)
    * sample.txt (Copy/Paste sample data here)


## Update Day (Getting Part2, stars etc)

php advent update:day 2022 15
