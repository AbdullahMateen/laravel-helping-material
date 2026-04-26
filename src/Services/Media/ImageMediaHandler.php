<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use Exception;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageMediaHandler
{
    public function __construct(
        private readonly MediaFilesystem $filesystem,
    ) {
    }

    /**
     * @return array{media: array{name: string, unique: string, path: string, file_path: string, size: int, url: string}, thumb: array{name: string, unique: string, path: string, file_path: string, size: int, url: string}|null, type: false|string, extension: string}
     *
     * @throws Exception
     */
    public function store(MediaStoreRequest $request): array
    {
        $filename = $request->filename();
        $media = $this->resolveMainImage($request);

        $mediaInfo = $this->persistImage(
            $media,
            $request,
            $filename,
        );

        $thumbInfo = $request->hasThumbnail
            ? $this->persistImage(
                $this->resolveThumbnail($media, $request),
                $request,
                'thumb_'.$filename,
            )
            : null;

        return [
            'media' => $mediaInfo,
            'thumb' => $thumbInfo,
            'type' => $this->filesystem->mimeType($request->disk, $request->path, $filename),
            'extension' => $request->extension(),
        ];
    }

    /**
     * @throws Exception
     */
    private function resolveMainImage(MediaStoreRequest $request): UploadedFile|Image
    {
        $media = $request->file;

        if ($request->fileCallback === null) {
            return $this->ensureSupportedImage($media);
        }

        $image = $media instanceof Image ? $media : ImageManager::gd()->read($media);

        return $request->fileCallback->__invoke($image) ?? $this->ensureSupportedImage($media);
    }

    /**
     * @throws Exception
     */
    private function resolveThumbnail(UploadedFile|Image $media, MediaStoreRequest $request): Image|UploadedFile
    {
        $image = $media instanceof Image ? $media : ImageManager::gd()->read($media);

        if ($request->thumbnailCallback !== null) {
            return $request->thumbnailCallback->__invoke($image) ?? $image;
        }

        return $image->scale(200, 200);
    }

    /**
     * @throws Exception
     */
    private function persistImage(UploadedFile|Image $media, MediaStoreRequest $request, string $filename): array
    {
        match (true) {
            $media instanceof UploadedFile => $this->filesystem->storeUploadedFile($media, $request->path, $filename, $request->disk),
            $media instanceof Image => $this->filesystem->saveImage($media, $request->path, $filename, $request->disk),
            default => throw new Exception('Unable to recognize image media type.'),
        };

        return $this->filesystem->fileAttributes(
            $request->disk,
            $request->path,
            $filename,
            $request->fileInformation['_original'],
        );
    }

    /**
     * @throws Exception
     */
    private function ensureSupportedImage(mixed $media): UploadedFile|Image
    {
        if ($media instanceof UploadedFile || $media instanceof Image) {
            return $media;
        }

        throw new Exception('Unsupported image media supplied.');
    }
}
