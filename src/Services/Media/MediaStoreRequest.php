<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use Closure;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;

final class MediaStoreRequest
{
    /**
     * @param array{_original:string,_name:string,_extension:string,name:string,unique:string} $fileInformation
     */
    public function __construct(
        public readonly UploadedFile|Image|string|null $file,
        public readonly string $path,
        public readonly string $disk,
        public readonly array $fileInformation,
        public readonly bool $hasThumbnail = false,
        public readonly ?Closure $fileCallback = null,
        public readonly ?Closure $thumbnailCallback = null,
    ) {
    }

    public function filename(): string
    {
        return $this->fileInformation['unique'];
    }

    public function extension(): string
    {
        return strtolower($this->fileInformation['_extension']);
    }
}
