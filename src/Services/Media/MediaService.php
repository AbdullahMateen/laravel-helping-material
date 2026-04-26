<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaDiskEnum;
use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use AbdullahMateen\LaravelHelpingMaterial\Models\Media;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HigherOrderTapProxy;
use Intervention\Image\Image;
use RuntimeException;
use Throwable;

class MediaService
{
    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    private MediaFilesystem $filesystem;
    private ImageMediaHandler $imageHandler;
    private BinaryMediaHandler $binaryHandler;

    private mixed $mediaModel = null;
    private mixed $mediaDiskEnum = null;
    private bool $isSharedStorage = false;
    private ?string $sharedStoragePath = null;

    private Closure|string|array|bool $name = false;

    private ?string $path = '';

    private mixed $disk = null;

    private ?MediaTypeEnum $mediaType = null;

    private UploadedFile|Image|string|null $uploadedFile = null;

    private UploadedFile|Image|string|null $file = null;
    private ?Closure $fileCallback = null;

    private bool $hasThumbnail = false;
    private UploadedFile|Image|string|null $thumbnail = null;
    private ?Closure $thumbnailCallback = null;

    private ?array $extensions = null;

    private ?array $fileInformation = null;

    private array $data = [];
    private array $ids = [];

    private ?Model $model = null;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        ?MediaFilesystem $filesystem = null,
        ?ImageMediaHandler $imageHandler = null,
        ?BinaryMediaHandler $binaryHandler = null,
    ) {
        $this->filesystem = $filesystem ?? new MediaFilesystem();
        $this->imageHandler = $imageHandler ?? new ImageMediaHandler($this->filesystem);
        $this->binaryHandler = $binaryHandler ?? new BinaryMediaHandler($this->filesystem);

        $this->mediaModel = $this->config('lhm.media_service.model', Media::class);
        $this->mediaDiskEnum = $this->config('lhm.media_service.media_disk_enum', MediaDiskEnum::class);
        $this->isSharedStorage = (bool) $this->config('lhm.storage.shared.enabled', false);
        $this->sharedStoragePath = $this->config('lhm.storage.shared.path');
    }

    /*
    |--------------------------------------------------------------------------
    | Configs
    |--------------------------------------------------------------------------
    */

    public function getMediaModel()
    {
        return $this->mediaModel;
    }

    public function mediaModel($mediaModel = null)
    {
        $this->mediaModel = $mediaModel;

        return $this;
    }

    public function getMediaDiskEnum()
    {
        return $this->mediaDiskEnum;
    }

    public function mediaDiskEnum($mediaDiskEnum = null)
    {
        $this->mediaDiskEnum = $mediaDiskEnum;

        return $this;
    }

    private function getIsSharedStorage()
    {
        return $this->isSharedStorage;
    }

    private function isSharedStorage($isSharedStorage = false)
    {
        $this->isSharedStorage = $isSharedStorage;

        return $this;
    }

    private function getSharedStoragePath()
    {
        return $this->sharedStoragePath;
    }

    private function sharedStoragePath($sharedStoragePath = null)
    {
        $this->sharedStoragePath = $sharedStoragePath;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Setters / Getters
    |--------------------------------------------------------------------------
    */

    public function getName(): Closure|string|bool
    {
        return $this->name;
    }

    public function name(Closure|string|bool $name = false): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function path(?string $path): static
    {
        $this->path = is_null($path) ? '' : trim($path, '/\\');

        return $this;
    }

    public function getDisk(): mixed
    {
        return $this->disk;
    }

    public function disk(mixed $disk = 'public'): static
    {
        $mediaEnum = $this->getMediaDiskEnum();
        $this->disk = match (true) {
            $disk instanceof $mediaEnum => $disk->disk(),
            is_numeric($disk) => $mediaEnum::tryFrom($disk)->disk(),
            is_string($disk) => $mediaEnum::fromName($disk)->disk(),
            default => $mediaEnum::fromName('public')->disk(),
        };

        return $this;
    }

    public function getMediaType(): ?MediaTypeEnum
    {
        return $this->mediaType;
    }

    private function mediaType(?MediaTypeEnum $mediaType = null): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getFile(): UploadedFile|Image|string|null
    {
        return $this->uploadedFile;
    }

    public function file(UploadedFile|Image|string|null $file): static
    {
        $this->uploadedFile = $this->resolveFile($file);
        $this->captureFileInformation();

        if (($this->fileInformation['_extension'] ?? null) !== null) {
            $this->resolveMediaTypeByExtension($this->fileInformation['_extension']);
        }

        return $this;
    }

    private function fileMutated(mixed $file = null): static
    {
        $this->file = $file;

        return $this;
    }

    private function thumbnailMutated(mixed $file = null): static
    {
        $this->thumbnail = $file;

        return $this;
    }

    public function getHasThumbnail(): bool
    {
        return $this->hasThumbnail;
    }

    public function hasThumbnail(bool $hasThumbnail): static
    {
        $this->hasThumbnail = $hasThumbnail;

        return $this;
    }

    public function getExtensions(): array
    {
        return $this->extensions ?? $this->extensionsFor($this->getMediaType());
    }

    public function extensions(array|string $extensions, bool $merge = false): static
    {
        $this->extensions = $this->filterExtensions($extensions, $merge);

        return $this;
    }

    public function fileInformation(): ?array
    {
        return $this->fileInformation;
    }

    public function captureFileInformation(): static
    {
        try {
            if (is_null($this->getFile())) {
                $this->fileInformation = null;

                return $this;
            }

            $media = $this->getFile();
            $fileNameWithExt = $media->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $media->getClientOriginalExtension();
            $uniqueName = sprintf('%s_%s.%s', uniqid('', true), time(), $extension);

            $name = $this->getName();
            $fileNameToStore = match (true) {
                $name instanceof Closure => $name($fileName, $extension),
                is_string($name) => $name,
                $name === false => $uniqueName,
                $name => $fileNameWithExt,
            };

            $this->fileInformation = [
                '_original' => $fileNameWithExt,
                '_name' => $fileName,
                '_extension' => $extension,
                'name' => $fileNameToStore,
                'unique' => $uniqueName,
            ];
        } catch (Exception) {
            $this->fileInformation = null;
        }

        return $this;
    }

    public function getData(): Collection
    {
        return collect($this->data);
    }

    private function data(array $data, bool $fresh = false): static
    {
        $this->data = $fresh ? $data : collect([...($this->data ?? []), $data])->unique('media.unique')->toArray();

        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function model(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function modifying(Closure $callback): static
    {
        if ($this->getMediaType() !== MediaTypeEnum::Image) {
            return $this;
        }

        $this->fileCallback = $callback;

        return $this;
    }

    public function thumbnail(Closure $callback): static
    {
        if ($this->getMediaType() !== MediaTypeEnum::Image) {
            return $this;
        }

        $this->hasThumbnail(true);
        $this->thumbnailCallback = $callback;

        return $this;
    }

    private function resolveFile(Image|string|UploadedFile|null $file): Image|UploadedFile|string|null
    {
        return match (true) {
            is_string($file) && File::exists($file) => path_to_uploaded_file($file),
            is_valid_url($file) => url_to_uploaded_file($file, 'temporary.png'),
            is_base64_image($file) => base64_to_uploaded_file($file, 'temporary.png'),
            default => $file,
        };
    }

    private function resolveMediaTypeByExtension(?string $extension = null): void
    {
        $extension = strtolower($extension ?? $this->fileInformation['_extension'] ?? '');

        if ($extension === '') {
            return;
        }

        foreach (MediaTypeEnum::cases() as $mediaType) {
            if (in_array($extension, $this->extensionsFor($mediaType), true)) {
                $this->mediaType($mediaType);

                return;
            }
        }

        throw new Exception("Unable to resolve media type by extension '$extension'");
    }

    private function filterExtensions(array|string $extensions, bool $merge = false): ?array
    {
        $extensions = array_unique(
            array_filter(
                array_map('strtolower', arrayify($extensions)),
            ),
        );

        if ($merge) {
            $extensions = array_unique(array_merge($this->extensionsFor($this->getMediaType()), $extensions));
        }

        return empty($extensions) ? null : $extensions;
    }

    private function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), $this->getExtensions(), true);
    }

    private function reset(): static
    {
        $this->fileMutated();
        $this->thumbnailMutated();
        $this->fileCallback = null;
        $this->thumbnailCallback = null;

        return $this;
    }

    public function when(bool $condition, Closure $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function tap(callable|null $callback = null): HigherOrderTapProxy|static
    {
        return tap($this, $callback);
    }

    private function ensureFolderExists($disk, $path): void
    {
        $path = trim((string) $path, '/\\');

        if ($path === '') {
            return;
        }

        Storage::disk($disk)->makeDirectory($path);
    }

    private function resolveStorageLocation(string $disk, string $path): string
    {
        return match ($disk) {
            'local' => trim($path, '/'),
            default => trim(sprintf('%s/%s', $disk, $path), '/'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Put/Remove files to/from Storage
    |--------------------------------------------------------------------------
    */

    public function store(?string $path = null, ?string $filename = null, mixed $disk = null): static
    {
        $fileInfo = $this
            ->when(isset($disk), fn () => $this->disk($disk))
            ->when(isset($path), fn () => $this->path($path))
            ->when(isset($filename), fn () => $this->name(fn ($firstname, $extension) => $filename))
            ->captureFileInformation()
            ->fileInformation();

        if ($fileInfo === null) {
            throw new RuntimeException('Unable to store media because no file is currently attached.');
        }

        if (! $this->isExtensionAllowed($fileInfo['_extension'])) {
            throw new RuntimeException('This file type is not allowed');
        }

        $this->ensureFolderExists($this->getDisk(), $this->getPath());

        $request = new MediaStoreRequest(
            file: $this->getFile(),
            path: $this->getPath() ?? '',
            disk: (string) $this->getDisk(),
            fileInformation: $fileInfo,
            hasThumbnail: $this->getHasThumbnail(),
            fileCallback: $this->fileCallback,
            thumbnailCallback: $this->thumbnailCallback,
        );

        $this->data(
            array_merge(
                $this->storeUsingHandler($request),
                ['media_type' => $this->getMediaType()?->value],
            ),
        )->reset();

        return $this;
    }

    public function filesStore(array $files, ?string $path = null, ?string $filename = null, mixed $disk = null): static
    {
        foreach (array_filter($files) as $file) {
            $this->file($file)->store($path, $filename, $disk);
        }

        return $this;
    }

    public function remove(?string $filename = null, ?string $path = null, mixed $disk = null): static
    {
        $this
            ->when(isset($disk), fn () => $this->disk($disk))
            ->when(isset($path), fn () => $this->path($path))
            ->when(isset($filename), fn () => $this->name(fn ($firstname, $extension) => $filename));

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = $this->getName() instanceof Closure ? ($this->getName())($name, $extension) : $this->getName();

        Storage::disk($this->getDisk())->delete(trim("{$this->getPath()}/$name", '/'));
        Storage::disk($this->getDisk())->delete(trim("{$this->getPath()}/thumb_$name", '/'));

        return $this;
    }

    public function removeFiles(array $files): static
    {
        foreach (array_filter($files) as $disk => $file) {
            $path = pathinfo($file, PATHINFO_DIRNAME);
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $this->remove($filename, $path, $disk);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Add/Remove data to/from Database
    |--------------------------------------------------------------------------
    */

    public function getIds($reset = true): array
    {
        $ids = array_filter($this->ids);

        if ($reset) {
            $this->setIds([], true);
        }

        return $ids;
    }

    private function setIds(mixed $id, bool $fresh = false): static
    {
        if ($fresh) {
            $this->ids = [];
        }

        if (is_array($id)) {
            $this->ids = [...$this->ids, ...array_filter($id)];
        } else {
            $this->ids[] = $id;
        }

        return $this;
    }

    public function save(?Model $model = null): static
    {
        $this->when(isset($model), fn () => $this->model($model));
        $model = $this->getModel();

        $files = [];
        foreach ($this->getData() as $file) {
            $files[] = [
                'group' => $this->getMediaDiskEnum()::fromName($this->getDisk())->value,
                'category' => $file['media_type'],
                'mediaable_id' => isset($model) ? $model->id : null,
                'mediaable_type' => isset($model)
                    ? (function_exists('get_morphs_maps') ? get_morphs_maps($model::class) : $model::class)
                    : null,
                'media_url' => $file['media']['url'],
                'thumb_url' => $file['thumb']['url'] ?? $file['media']['url'],
                'name' => $file['media']['name'],
                'media_name' => $file['media']['unique'],
                'thumb_name' => $file['thumb']['unique'] ?? $file['media']['unique'],
                'path' => $file['media']['path'],
                'file_path' => $file['media']['file_path'],
                'type' => $file['type'],
                'extension' => $file['extension'],
                'media_size' => $file['media']['size'],
                'thumb_size' => $file['thumb']['size'] ?? $file['media']['size'],
                'created_at' => now_now(),
                'updated_at' => now_now(),
            ];
        }

        $mediaClass = $this->getMediaModel();
        foreach (array_chunk($files, 500) as $filesChunk) {
            DB::table(get_model_table($mediaClass))->insert($filesChunk);
        }

        $this->setIds(
            $mediaClass::toBase()->whereIn('media_name', $this->getData()->pluck('media.unique')->all())->pluck('id')->all(),
            true,
        );

        return $this;
    }

    public function update(Model|array|string $media, mixed $disk = null, $column = 'id'): mixed
    {
        $this->when(isset($disk), fn () => $this->disk($disk));

        $mediaClass = $this->getMediaModel();
        $isMediaInstance = $media instanceof $mediaClass;
        if (! $isMediaInstance) {
            $medias = $mediaClass::whereIn($column, arrayify($media))->get();

            if ($medias->count() !== 1 && $medias->count() !== $this->getData()->count()) {
                throw new RuntimeException('Either pass single instance of media or id, or pass the same number of ids as the files');
            }

            if ($medias->count() === 1) {
                $media = $medias->last();
                $isMediaInstance = true;
            }
        }

        foreach ($this->getData() as $index => $file) {
            if (! $isMediaInstance) {
                $media = $medias[$index];
            }

            $media->group = $this->getMediaDiskEnum()::fromName($this->getDisk())->value ?? $media->group->value;
            $media->category = $file['media_type'] ?? $media->category;
            $media->media_url = $file['media']['url'];
            $media->thumb_url = $file['thumb']['url'] ?? $file['media']['url'];
            $media->name = $file['media']['name'];
            $media->media_name = $file['media']['unique'];
            $media->thumb_name = $file['thumb']['unique'] ?? $file['media']['unique'];
            $media->path = $file['media']['path'];
            $media->file_path = $file['media']['file_path'];
            $media->type = $file['type'];
            $media->extension = $file['extension'];
            $media->media_size = $file['media']['size'];
            $media->thumb_size = $file['thumb']['size'] ?? $file['media']['size'];
            $media->save();
        }

        $this->setIds(
            $mediaClass::toBase()->whereIn('media_name', $this->getData()->pluck('media.unique')->all())->pluck('id')->all(),
            true,
        );

        return $this;
    }

    public function move(array|string $values, mixed $fromDisk = 'public', string $fromPath = '', mixed $toDisk = 'public', string $toPath = '', string $column = 'id'): static
    {
        $model = $this->getModel();
        if (is_null($model)) {
            throw new ModelNotFoundException('Unable to move file, Model is not provided');
        }

        $values = arrayify($values);
        $medias = $this->getMediaModel()::whereIn($column, $values)->get();

        $this->setIds([], true);
        foreach ($medias as $media) {
            $filename = $media->media_name;
            $fromPath = trim($this->resolveStorageLocation($this->disk($fromDisk)->getDisk(), $this->path($fromPath)->getPath()), '/\\');
            $toPath = trim($this->resolveStorageLocation($this->disk($toDisk)->getDisk(), $this->path($toPath)->getPath()), '/\\');

            $this->ensureFolderExists($this->getDisk(), $this->getPath());

            if (! Storage::move("$fromPath/$filename", "$toPath/$filename")) {
                continue;
            }

            if (isset($media->thumb_name)) {
                Storage::move("$fromPath/$media->thumb_name", "$toPath/$media->thumb_name");
            }

            $disk = $this->getDisk();
            $path = $this->getPath();

            $media->group = $this->getMediaDiskEnum()::fromName($this->getDisk())->value;
            $media->mediaable_id = $model->id;
            $media->mediaable_type = get_morphs_maps($model::class);

            $media->media_url = $this->resolveStorageLocation($disk, trim("$path/$filename", '/'));
            $media->thumb_url = $this->resolveStorageLocation($disk, trim("$path/thumb_$filename", '/'));
            $media->path = $path;
            $media->file_path = Storage::disk($disk)->path(trim("$path/$filename", '/'));
            $media->save();

            $this->setIds($media->id);
        }

        return $this;
    }

    public function destroy(array|string $values, string $column = 'id', bool $removeFromStorage = true): static
    {
        $values = arrayify($values);
        $query = $this->getMediaModel()::whereIn($column, $values);

        $medias = $query->toBase()->select('id', 'group', 'media_name', 'path')->get();

        $query->delete();

        $this->setIds(
            $medias->pluck('id')->all(),
            true,
        );

        if ($removeFromStorage) {
            $this->removeFiles(
                $medias->map(function ($media) {
                    $media->full_path = "$media->path/$media->media_name";

                    return $media;
                })->pluck('full_path', 'group')->all(),
            );
        }

        return $this;
    }

    /**
     * @return array{media: array, thumb: array|null, type: false|string, extension: string}
     *
     * @throws Exception
     */
    private function storeUsingHandler(MediaStoreRequest $request): array
    {
        return match ($this->getMediaType()) {
            MediaTypeEnum::Image => $this->imageHandler->store($request),
            MediaTypeEnum::Audio,
            MediaTypeEnum::Video,
            MediaTypeEnum::Document,
            MediaTypeEnum::Archive => $this->binaryHandler->store($request),
            default => throw new RuntimeException('Unable to determine media handler.'),
        };
    }

    private function extensionsFor(?MediaTypeEnum $mediaType): array
    {
        if ($mediaType === null) {
            return [];
        }

        return $this->config(
            'lhm.media_service.extensions.'.strtolower($mediaType->name),
            $mediaType->extensions(),
        );
    }

    private function config(string $key, mixed $default = null): mixed
    {
        try {
            if (function_exists('config')) {
                return config($key, $default);
            }
        } catch (Throwable) {
        }

        return $default;
    }
}
