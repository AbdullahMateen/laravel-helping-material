<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\Temporal;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum TimeUnitEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Nanoseconds = 1;
    case Microseconds = 2;
    case Milliseconds = 3;
    case Seconds = 4;
    case Minutes = 5;
    case Hours = 6;
    case Days = 7;
    case Weeks = 8;
    case Months = 9;
    case Years = 10;
    case Decades = 11;
    case Centuries = 12;

    public function alias(): array
    {
        return match ($this) {
            self::Nanoseconds  => [__('ns'), __('nano')],
            self::Microseconds => [__('µs'), __('micro')],
            self::Milliseconds => [__('ms'), __('milli')],
            self::Seconds      => [__('s'), __('sec')],
            self::Minutes      => [__('m'), __('min')],
            self::Hours        => [__('h'), __('hr')],
            self::Days         => [__('d'), __('day')],
            self::Weeks        => [__('w'), __('wk')],
            self::Months       => [__('mo'), __('month')],
            self::Years        => [__('yr'), __('y')],
            self::Decades      => [__('dec'), __('decade')],
            self::Centuries    => [__('cent'), __('century')],
        };
    }

    public function singular(): string
    {
        return match ($this) {
            self::Nanoseconds  => __('Nanosecond'),
            self::Microseconds => __('Microsecond'),
            self::Milliseconds => __('Millisecond'),
            self::Seconds      => __('Second'),
            self::Minutes      => __('Minute'),
            self::Hours        => __('Hour'),
            self::Days         => __('Day'),
            self::Weeks        => __('Week'),
            self::Months       => __('Month'),
            self::Years        => __('Year'),
            self::Decades      => __('Decade'),
            self::Centuries    => __('Century'),
        };
    }

    public function toIso8601(): string
    {
        return match ($this) {
            self::Nanoseconds  => null,
            self::Microseconds => null,
            self::Milliseconds => null,
            self::Seconds      => __('PT1S'),
            self::Minutes      => __('PT1M'),
            self::Hours        => __('PT1H'),
            self::Days         => __('P1D'),
            self::Weeks        => __('P1W'),
            self::Months       => __('P1M'),
            self::Years        => __('P1Y'),
            self::Decades      => __('P10Y'),
            self::Centuries    => __('P100Y'),
        };
    }

    public function toSeconds(): int
    {
        return match ($this) {
            self::Nanoseconds  => 1 / 1000000000,
            self::Microseconds => 1 / 1000000,
            self::Milliseconds => 1 / 1000,
            self::Seconds      => 1,
            self::Minutes      => 60,
            self::Hours        => 3600,
            self::Days         => 86400,
            self::Weeks        => 604800,
            self::Months       => 2629746, // Approximation
            self::Years        => 31556952, // Approximation
            self::Decades      => 315569520, // Approximation
            self::Centuries    => 3155695200, // Approximation
        };
    }

    public function toString()
    {
        return match ($this) {
            self::Milliseconds => __('Milliseconds'),
            self::Seconds      => __('Seconds'),
            self::Minutes      => __('Minutes'),
            self::Hours        => __('Hours'),
            self::Days         => __('Days'),
            self::Weeks        => __('Weeks'),
            self::Months       => __('Months'),
            self::Years        => __('Years'),
            self::Decades      => __('Decades'),
            self::Centuries    => __('Centuries'),
        };
    }

    public function color()
    {
        return self::PRIMARY_CLASS;
    }

}
