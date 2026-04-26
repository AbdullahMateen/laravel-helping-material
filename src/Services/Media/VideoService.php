<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;

class VideoService extends TypedMediaService
{
    protected static function mediaType(): MediaTypeEnum
    {
        return MediaTypeEnum::Video;
    }
}
