<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

trait ValidationTrait
{
    public static function VALIDATION_ATTRIBUTES($self = null, ...$data)
    {
        return [];
    }

    public static function CREATE_VALIDATION_RULES($self = null, ...$data)
    {
        return [];
    }

    public static function CREATE_VALIDATION_MESSAGES($self = null, ...$data)
    {
        return [];
    }

    public static function UPDATE_VALIDATION_RULES($self = null, ...$data)
    {
        return [];
    }

    public static function UPDATE_VALIDATION_MESSAGES($self = null, ...$data)
    {
        return [];
    }
}
