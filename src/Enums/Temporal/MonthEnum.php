<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\Temporal;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum MonthEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case Jun = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    public function alias(): array
    {
        return match ($this) {
            self::January   => __('Jan'),
            self::February  => __('Feb'),
            self::March     => __('Mar'),
            self::April     => __('Apr'),
            self::May       => __('May'),
            self::Jun       => __('Jun'),
            self::July      => __('Jul'),
            self::August    => __('Aug'),
            self::September => __('Sep'),
            self::October   => __('Oct'),
            self::November  => __('Nov'),
            self::December  => __('Dec'),
        };
    }

    public function days($year = null): int
    {
        return match ($this) {
            self::January   => 31,
            self::February  => is_leap_year($year) ? 29 : 28,
            self::March     => 31,
            self::April     => 30,
            self::May       => 31,
            self::Jun       => 30,
            self::July      => 31,
            self::August    => 31,
            self::September => 30,
            self::October   => 31,
            self::November  => 30,
            self::December  => 31,
        };
    }

    public function toString()
    {
        return $this->name;
    }

    public function color()
    {
        return self::PRIMARY_CLASS;
    }

}
