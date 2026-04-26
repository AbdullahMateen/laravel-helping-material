<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class BinaryMediaHandler
{
    public function __construct(
        private readonly MediaFilesystem $filesystem,
    ) {
    }

    /**
     * @return array{media: array{name: string, unique: string, path: string, file_path: string, size: int, url: string}, thumb: null, type: false|string, extension: string}
     */
    public function store(MediaStoreRequest $request): array
    {
        if (! $request->file instanceof UploadedFile) {
            throw new RuntimeException('Binary media handler expects an uploaded file instance.');
        }

        $filename = $request->filename();

        $this->filesystem->storeUploadedFile($request->file, $request->path, $filename, $request->disk);

        return [
            'media' => $this->filesystem->fileAttributes(
                $request->disk,
                $request->path,
                $filename,
                $request->fileInformation['_original'],
            ),
            'thumb' => null,
            'type' => $this->filesystem->mimeType($request->disk, $request->path, $filename),
            'extension' => $request->extension(),
        ];
    }
}
