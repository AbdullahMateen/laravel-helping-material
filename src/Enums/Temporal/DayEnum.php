<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\Temporal;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum DayEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    public function alias(): array
    {
        return match ($this) {
            self::Monday    => __('Mon'),
            self::Tuesday   => __('Tue'),
            self::Wednesday => __('Wed'),
            self::Thursday  => __('Thu'),
            self::Friday    => __('Fri'),
            self::Saturday  => __('Sat'),
            self::Sunday    => __('Sun'),
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
