<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;

class DocumentService extends TypedMediaService
{
    protected static function mediaType(): MediaTypeEnum
    {
        return MediaTypeEnum::Document;
    }
}
