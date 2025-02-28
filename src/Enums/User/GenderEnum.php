<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\User;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum GenderEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Male = 1;
    case Female = 2;
    case Other = 3;

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
        // return match ($this) {
        //     self::Male   => 'Male',
        //     self::Female => 'Female',
        //     self::Other  => 'Other',
        // };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Male   => self::DEEPSKYBLUE_CLASS,
            self::Female => self::HOTPINK_CLASS,
            self::Other  => self::SECONDARY_CLASS,
        };
    }

}
