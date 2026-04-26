<?php

declare(strict_types=1);

use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ArchiveService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\AudioService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\DocumentService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ImageService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\VideoService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Media Service API Examples
|--------------------------------------------------------------------------
|
| Rule of thumb:
| - store() writes files to storage only.
| - persist() writes files to storage and then inserts media rows in the DB.
| - MediaService::store()->save($model) gives full control and also writes DB.
| - Passing null as the model stores the DB row without a mediaable relation.
|
| Typed services:
| - ImageService: images only
| - AudioService: audio only
| - VideoService: video only
| - DocumentService: documents only
| - ArchiveService: archives only
|
| Common return shapes:
| - TypedService::store(...) returns the latest stored media data array.
| - TypedService::persist(...) returns ['data' => [...], 'ids' => [...]].
| - MediaService::getData() returns a collection of stored file data.
| - MediaService::getIds(false) returns inserted/updated media DB IDs.
|
*/

final class MediaServiceApiExamples
{
    public function uploadProfilePicture(Request $request, User $user): array
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        return ImageService::persist(
            file: $request->file('avatar'),
            model: $user,
            path: "users/{$user->id}/profile",
            filename: 'avatar',
            disk: 'public',
        );
    }

    public function uploadProfilePictureWithThumbnail(Request $request, User $user): array
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $media = ImageService::service($request->file('avatar'))
            ->thumbnail(fn ($image) => $image->scale(width: 320))
            ->store("users/{$user->id}/profile", 'avatar', 'public')
            ->save($user);

        return [
            'data' => $media->getData()->values()->all(),
            'ids' => $media->getIds(false),
        ];
    }

    public function storeImageOnFilesystemOnly(Request $request): array
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        return ImageService::store(
            file: $request->file('image'),
            path: 'uploads/images',
            disk: 'public',
        );
    }

    public function storeDocumentOnFilesystemOnly(Request $request): array
    {
        $request->validate([
            'document' => ['required', 'file', 'max:10240'],
        ]);

        return DocumentService::store(
            file: $request->file('document'),
            path: 'uploads/documents',
            disk: 'public',
        );
    }

    public function storeFileInDatabaseWithoutModel(Request $request): array
    {
        $request->validate([
            'document' => ['required', 'file', 'max:10240'],
        ]);

        return DocumentService::persist(
            file: $request->file('document'),
            model: null,
            path: 'unattached/documents',
            disk: 'public',
        );
    }

    public function storeManyImagesForModel(Request $request, Model $model): array
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image', 'max:4096'],
        ]);

        $media = ImageService::service()
            ->filesStore($request->file('images'), "models/{$model->getKey()}/gallery", null, 'public')
            ->save($model);

        return [
            'data' => $media->getData()->values()->all(),
            'ids' => $media->getIds(false),
        ];
    }

    public function storeManyMixedFiles(Request $request, Model $model): array
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:51200'],
        ]);

        $media = app(MediaService::class)
            ->filesStore($request->file('files'), "models/{$model->getKey()}/attachments", null, 'public')
            ->save($model);

        return [
            'data' => $media->getData()->values()->all(),
            'ids' => $media->getIds(false),
        ];
    }

    public function storeTemporaryImageWithoutDatabase(Request $request): array
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        return ImageService::store(
            file: $request->file('image'),
            path: 'temporary/images',
            disk: 'public',
        );
    }

    public function finalizeTemporaryImageWithoutDatabase(string $temporaryFilePath, User $user): array
    {
        $media = ImageService::persist(
            file: $temporaryFilePath,
            model: $user,
            path: "users/{$user->id}/images",
            disk: 'public',
        );

        app(MediaService::class)->remove(
            filename: basename($temporaryFilePath),
            path: dirname($temporaryFilePath),
            disk: 'public',
        );

        return $media;
    }

    public function storeTemporaryDocumentWithDatabase(Request $request): array
    {
        $request->validate([
            'document' => ['required', 'file', 'max:10240'],
        ]);

        return DocumentService::persist(
            file: $request->file('document'),
            model: null,
            path: 'temporary/documents',
            disk: 'public',
        );
    }

    public function moveTemporaryDocumentsToModel(array $mediaIds, User $user): array
    {
        $media = app(MediaService::class)
            ->model($user)
            ->move(
                values: $mediaIds,
                fromDisk: 'public',
                fromPath: 'temporary/documents',
                toDisk: 'public',
                toPath: "users/{$user->id}/documents",
                column: 'id',
            );

        return [
            'ids' => $media->getIds(false),
        ];
    }

    public function moveMediaFromPublicToS3(array $mediaIds, User $user): array
    {
        $media = app(MediaService::class)
            ->model($user)
            ->move(
                values: $mediaIds,
                fromDisk: 'public',
                fromPath: "users/{$user->id}/documents",
                toDisk: 's3',
                toPath: "users/{$user->id}/documents",
                column: 'id',
            );

        return [
            'ids' => $media->getIds(false),
        ];
    }

    public function replaceExistingMedia(Request $request, int $mediaId): array
    {
        $request->validate([
            'document' => ['required', 'file', 'max:10240'],
        ]);

        $media = DocumentService::service($request->file('document'))
            ->store('replacement/documents', null, 'public')
            ->update($mediaId, 'public', 'id');

        return [
            'data' => $media->getData()->values()->all(),
            'ids' => $media->getIds(false),
        ];
    }

    public function deleteMediaAndFiles(array $mediaIds): array
    {
        $media = app(MediaService::class)->destroy(
            values: $mediaIds,
            column: 'id',
            removeFromStorage: true,
        );

        return [
            'ids' => $media->getIds(false),
        ];
    }

    public function deleteMediaRowsOnly(array $mediaIds): array
    {
        $media = app(MediaService::class)->destroy(
            values: $mediaIds,
            column: 'id',
            removeFromStorage: false,
        );

        return [
            'ids' => $media->getIds(false),
        ];
    }

    public function removeFilesystemOnlyFile(string $filename): void
    {
        app(MediaService::class)->remove(
            filename: $filename,
            path: 'uploads/documents',
            disk: 'public',
        );
    }

    public function audioVideoDocumentArchiveExamples(Request $request, Model $model): array
    {
        $request->validate([
            'audio' => ['nullable', 'file', 'max:51200'],
            'video' => ['nullable', 'file', 'max:204800'],
            'document' => ['nullable', 'file', 'max:10240'],
            'archive' => ['nullable', 'file', 'max:102400'],
        ]);

        return [
            'audio' => $request->hasFile('audio')
                ? AudioService::persist($request->file('audio'), $model, 'media/audio', null, 'public')
                : null,
            'video' => $request->hasFile('video')
                ? VideoService::persist($request->file('video'), $model, 'media/video', null, 'public')
                : null,
            'document' => $request->hasFile('document')
                ? DocumentService::persist($request->file('document'), $model, 'media/documents', null, 'public')
                : null,
            'archive' => $request->hasFile('archive')
                ? ArchiveService::persist($request->file('archive'), $model, 'media/archives', null, 'public')
                : null,
        ];
    }
}
