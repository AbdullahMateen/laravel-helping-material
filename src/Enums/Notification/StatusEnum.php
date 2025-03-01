<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\Notification;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum StatusEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Read = 1;
    case Unread = 0;

    /**
     * @return string
     */
    public function toString(): string
    {
        return match ($this) {
            self::Read   => 'Read',
            self::Unread => 'Unread',
        };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Read   => self::SUCCESS_CLASS,
            self::Unread => self::DANGER_CLASS,
        };
    }

}
