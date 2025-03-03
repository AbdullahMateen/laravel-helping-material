<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait AudioTrait
{
    /**
     * @return array{media: array, thumb: null, type: false|string, extension: string}
     */
    private function storeAudio(): array
    {
        $disk     = $this->getDisk();
        $path     = $this->getPath();
        $fileInfo = $this->fileInformation();
        $filename = $fileInfo['name'];

        if (!Storage::disk($disk)->directoryExists($path)) {
            File::makeDirectory(storage_path("app/$disk/$path"), 0755, true);
        }

        $mediaInfo = $this->generateAudio($this->getFile(), $path, $disk, $filename);
        if ($this->getHasThumbnail()) {
            // generate thumb
        }

        return [
            'media'     => $mediaInfo,
            'thumb'     => $thumbInfo ?? null,
            'type'      => Storage::disk($disk)->mimeType(trim("$path/$filename", '/')), // mime_content_type($storagePath . $fileNameToStore),
            'extension' => strtolower($fileInfo['_extension']),
        ];
    }

    /**
     * @param mixed  $media
     * @param string $path
     * @param string $disk
     * @param string $filename
     *
     * @return array{name: string, path: string, size: int, url: string}
     */
    private function generateAudio(mixed $media, string $path, string $disk, string $filename): array
    {
        $media->storeAs($path, $filename, $disk);

        $path = trim("$path/$filename", '/');
        return [
            'name' => $filename,
            'path' => trim(sprintf("%s/%s", $disk, $path), '/'),                               // Storage::disk($disk)->path($path),
            'size' => Storage::disk($disk)->size($path),           // Storage::disk($disk)->size($path),
            'url'  => sprintf("%s/%s", $disk, $path),    // Storage::disk($disk)->url($path),
        ];
    }
}
