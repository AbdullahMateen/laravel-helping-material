<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;

class ImageService extends TypedMediaService
{
    protected static function mediaType(): MediaTypeEnum
    {
        return MediaTypeEnum::Image;
    }
}
