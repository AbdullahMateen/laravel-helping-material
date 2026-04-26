<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;

class AudioService extends TypedMediaService
{
    protected static function mediaType(): MediaTypeEnum
    {
        return MediaTypeEnum::Audio;
    }
}
