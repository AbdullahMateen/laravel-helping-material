<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class MediaFilesystem
{
    public function storeUploadedFile(UploadedFile $file, string $path, string $filename, string $disk): void
    {
        $file->storeAs($path, $filename, $disk);
    }

    public function saveImage(Image $image, string $path, string $filename, string $disk): void
    {
        $image->save(Storage::disk($disk)->path(trim("$path/$filename", '/')));
    }

    public function mimeType(string $disk, string $path, string $filename): string|false
    {
        return Storage::disk($disk)->mimeType(trim("$path/$filename", '/'));
    }

    /**
     * @return array{name: string, unique: string, path: string, file_path: string, size: int, url: string}
     */
    public function fileAttributes(string $disk, string $path, string $filename, string $originalName): array
    {
        $path = trim("$path/$filename", '/');

        return [
            'name' => $originalName,
            'unique' => $filename,
            'path' => $path,
            'file_path' => $this->resolveFilePath($disk, $path),
            'size' => Storage::disk($disk)->size($path),
            'url' => $this->resolveFileUrl($disk, $path),
        ];
    }

    private function resolveFilePath(string $disk, string $path): string
    {
        return match ($disk) {
            'local' => trim($path, '/'),
            default => trim(sprintf('%s/%s', $disk, $path), '/'),
        };
    }

    private function resolveFileUrl(string $disk, string $path): string
    {
        return match ($disk) {
            'local' => trim($path, '/'),
            default => trim(sprintf('%s/%s', $disk, $path), '/'),
        };
    }
}
