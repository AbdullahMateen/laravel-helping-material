<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum BooleanEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Yes = 1;
    case No = 0;

    /**
     * @return string
     */
    public function toString(): string
    {
        return match ($this) {
            self::No  => 'No',
            self::Yes => 'Yes',
        };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::No  => self::DANGER_CLASS,
            self::Yes => self::SUCCESS_CLASS,
        };
    }

}
