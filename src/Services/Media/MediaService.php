<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaDiskEnum;
use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use AbdullahMateen\LaravelHelpingMaterial\Models\Media;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Media\ArchiveTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Media\AudioTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Media\DocumentTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Media\ImageTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Media\VideoTrait;
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
use Illuminate\Support\Traits\Tappable;
use Intervention\Image\Image;
use RuntimeException;

class MediaService
{
    use ImageTrait, AudioTrait, VideoTrait, DocumentTrait, ArchiveTrait, Tappable;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    private mixed $mediaDiskEnum = null;

    private Closure|string|array|bool $name = false;

    private string|null $path = '';

    private mixed $disk = 'public';

    private MediaTypeEnum|null $mediaType = null;

    private UploadedFile|Image|string|null $uploadedFile = null;

    private UploadedFile|Image|string|null $file         = null;
    private Closure|null                   $fileCallback = null;

    private bool                           $hasThumbnail      = false;
    private UploadedFile|Image|string|null $thumbnail         = null;
    private Closure|null                   $thumbnailCallback = null;

    private array|null $extensions = null;

    private array|null $fileInformation = null;

    private array $data = [];
    private array $ids  = [];

    private Model|null $model = null;


    /*
    |--------------------------------------------------------------------------
    | Configs
    |--------------------------------------------------------------------------
    */

    public function getMediaDiskEnum()
    {
        return $this->mediaDiskEnum ?? config('lhm.media_service.media_disk_enum');
    }

    public function mediaDiskEnum($mediaDiskEnum = null)
    {
        $this->mediaDiskEnum = $mediaDiskEnum;
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Setters / Getters
    |--------------------------------------------------------------------------
    */

    /* ==================== name ==================== */

    /**
     * @return Closure|string|bool
     */
    public function getName(): Closure|string|bool
    {
        return $this->name;
    }

    /**
     * @param Closure|string|bool $name False: use system generated unique name, <br> True: use original file name <br> String: provide custom filename string <br> Closure(string $filename, string $extension): provide custom filename string
     *
     * @return $this
     */
    public function name(Closure|string|bool $name = false): static
    {
        $this->name = $name;
        return $this;
    }

    /* ==================== path ==================== */

    /**
     * @return string|null
     */
    public function getPath(): string|null
    {
        return $this->path;
    }

    /**
     * @param string|null $path This path is relative to provided storage disk default is 'public'
     *
     * @return $this
     */
    public function path(string|null $path): static
    {
        $this->path = is_null($path) ? '' : trim($path, '/\\');
        return $this;
    }

    /* ==================== disk ==================== */

    /**
     * @return mixed
     */
    public function getDisk(): mixed
    {
        return $this->disk;
    }

    /**
     * @param mixed $disk
     *
     * @return $this
     */
    public function disk(mixed $disk = 'public'): static
    {
        $mediaEnum  = $this->getMediaDiskEnum();
        $this->disk = $disk instanceof $mediaEnum ? $disk->disk() : $disk;
        if (blank($this->disk)) {
            $this->disk = 'public';
        }
        return $this;
    }

    /* ==================== media type ==================== */

    /**
     * @return MediaTypeEnum|null
     */
    private function getMediaType(): MediaTypeEnum|null
    {
        return $this->mediaType;
    }

    /**
     * @param MediaTypeEnum|null $mediaType
     *
     * @return $this
     */
    private function mediaType(MediaTypeEnum|null $mediaType = null): static
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    /* ==================== file ==================== */

    /**
     * @return UploadedFile|Image|string|null
     */
    public function getFile(): UploadedFile|Image|string|null
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile|Image|string|null $file
     *
     * @return $this
     */
    public function file(UploadedFile|Image|string|null $file): static
    {
        $this->uploadedFile = $this->resolveFile($file);
        $this->captureFileInformation()->resolveMediaTypeByExtension();
        return $this;
    }

    /**
     * @param mixed|null $file
     *
     * @return $this
     */
    private function fileMutated(mixed $file = null): static
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @param mixed|null $file
     *
     * @return $this
     */
    private function thumbnailMutated(mixed $file = null): static
    {
        $this->thumbnail = $file;
        return $this;
    }

    /* ==================== thumbnail ==================== */

    /**
     * @return bool
     */
    public function getHasThumbnail(): bool
    {
        return $this->hasThumbnail;
    }

    /**
     * @param bool $hasThumbnail False: don't generate thumbnail, <br> True: generate thumbnail with default settings
     *
     * @return $this
     */
    public function hasThumbnail(bool $hasThumbnail): static
    {
        $this->hasThumbnail = $hasThumbnail;
        return $this;
    }

    /* ==================== allowed extensions ==================== */

    /**
     * @return array
     */
    public function getExtensions(): array
    {
        $type = strtolower($this->getMediaType()?->name);
        return $this->extensions ?? config("lhm.media_service.extensions.$type") ?? $this->getMediaType()?->extensions();
    }

    /**
     * @param array|string $extensions
     * @param bool         $merge
     *
     * @return $this
     */
    public function setExtensions(array|string $extensions, bool $merge = false): static
    {
        $this->extensions = $this->filterExtensions($extensions, $merge);
        return $this;
    }

    /* ==================== file information ==================== */

    /**
     * @return array|null
     */
    public function fileInformation(): ?array
    {
        return $this->fileInformation;
    }

    /**
     * @return $this
     */
    public function captureFileInformation(): static
    {
        try {
            if (is_null($this->getFile())) {
                $this->fileInformation = null;
                return $this;
            }

            $media           = $this->getFile();
            $fileNameWithExt = $media->getClientOriginalName();
            $fileName        = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension       = $media->getClientOriginalExtension();
            $uniqueName      = sprintf('%s_%s.%s', uniqid('', true), time(), $extension);

            $name            = $this->getName();
            $fileNameToStore = match (true) {
                $name instanceof Closure => $name($fileName, $extension),
                is_string($name)         => $name,
                $name === false          => $uniqueName,
                $name                    => $fileNameWithExt,
            };

            $this->fileInformation = [
                '_original'  => $fileNameWithExt,
                '_name'      => $fileName,
                '_extension' => $extension,
                'name'       => $fileNameToStore,
                'unique'     => $uniqueName,
            ];
        } catch (Exception) {
            $this->fileInformation = null;
        }

        return $this;
    }

    /* ==================== data ==================== */

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return collect($this->data);
    }

    /**
     * @param array $data
     * @param bool  $fresh
     *
     * @return $this
     */
    private function data(array $data, bool $fresh = false): static
    {
        $this->data = $fresh ? $data : collect([...($this->data ?? []), $data])->unique('media.name')->toArray();
        return $this;
    }

    /* ==================== Model ==================== */

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function model(Model $model): static
    {
        $this->model = $model;
        return $this;
    }

    /* ==================== intervention ==================== */

    /**
     * @param Closure $callback Closure(\Intervention\Image\Interfaces\ImageInterface $file) using Intervention api to generate file and return file object
     *
     * @return $this
     */
    public function modifying(Closure $callback): static
    {
        if ($this->getMediaType() !== MediaTypeEnum::Image) {
            return $this;
        }

        //        $file = ImageManager::gd()->read($this->getFile());
        //        $file = $callback($file);
        //        if (!isset($file)) {
        //            return $this;
        //        }

        $this->fileCallback = $callback;
        return $this;
    }

    /**
     * @param Closure $callback Closure(\Intervention\Image\Interfaces\ImageInterface $file) using Intervention api to generate thumb and return file object
     *
     * @return $this
     */
    public function thumbnail(Closure $callback): static
    {
        if ($this->getMediaType() !== MediaTypeEnum::Image) {
            return $this;
        }

        $this->hasThumbnail(true);

        //        $file = ImageManager::gd()->read($this->getFile());
        //        $file = $callback($file);
        //        if (!isset($file)) {
        //            return $this;
        //        }

        $this->thumbnailCallback = $callback;
        return $this;
    }

    /* ==================== helpers ==================== */

    /**
     * @param Image|string|UploadedFile|null $file
     *
     * @return Image|UploadedFile
     */
    private function resolveFile(Image|string|UploadedFile|null $file): Image|UploadedFile
    {
        return match (true) {
            File::exists($file)    => path_to_uploaded_file($file),
            is_valid_url($file)    => url_to_uploaded_file($file, 'temporary.png'),
            is_base64_image($file) => base64_to_uploaded_file($file, 'temporary.png'),
            default                => $file
        };
    }

    /**
     * @param string|null $extension
     *
     * @return void
     */
    private function resolveMediaTypeByExtension(string $extension = null): void
    {
        $extension = strtolower($extension ?? $this->fileInformation['_extension']);
        $this->mediaType(match (true) {
            in_array($extension, filled(config("lhm.media_service.extensions.image")) ? config("lhm.media_service.extensions.image") : MediaTypeEnum::Image->extensions(), true)          => MediaTypeEnum::Image,
            in_array($extension, filled(config("lhm.media_service.extensions.audio")) ? config("lhm.media_service.extensions.audio") : MediaTypeEnum::Audio->extensions(), true)          => MediaTypeEnum::Audio,
            in_array($extension, filled(config("lhm.media_service.extensions.video")) ? config("lhm.media_service.extensions.video") : MediaTypeEnum::Video->extensions(), true)          => MediaTypeEnum::Video,
            in_array($extension, filled(config("lhm.media_service.extensions.document")) ? config("lhm.media_service.extensions.document") : MediaTypeEnum::Document->extensions(), true) => MediaTypeEnum::Document,
            in_array($extension, filled(config("lhm.media_service.extensions.archive")) ? config("lhm.media_service.extensions.archive") : MediaTypeEnum::Archive->extensions(), true)    => MediaTypeEnum::Archive,
        });
    }

    /**
     * @param array|string $extensions
     * @param bool         $merge
     *
     * @return array|null
     */
    private function filterExtensions(array|string $extensions, bool $merge = false): array|null
    {
        $extensions = array_unique(
            array_filter(
                array_map('strtolower', is_array($extensions) ? $extensions : explode(',', $extensions))
            )
        );

        if ($merge) {
            $type       = strtolower($this->getMediaType()?->name);
            $extensions = array_merge(config("lhm.media_service.extensions.$type"), $extensions);
        }

        return empty($extensions) ? null : $extensions;
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    private function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), $this->getExtensions(), true);
    }


    /**
     * @return $this
     */
    private function reset(): static
    {
        $this
            // ->name()
            // ->path()
            // ->disk()
            // ->mediaType()
            // ->file(null)
            ->fileMutated()
            ->thumbnailMutated()
            // ->hasThumbnail()
            // ->setExtensions()
        ;

        return $this;
    }

    /**
     * @param bool    $condition
     * @param Closure $callback
     *
     * @return $this
     */
    public function when(bool $condition, Closure $callback): static
    {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param callable|null $callback
     *
     * @return $this|HigherOrderTapProxy
     */
    public function tap($callback = null): HigherOrderTapProxy|static
    {
        return tap($this, $callback($this));
    }

    /*
    |--------------------------------------------------------------------------
    | Put/Remove files to/from Storage
    |--------------------------------------------------------------------------
    */

    /* ==================== store to filesystem ==================== */

    /**
     * @param string|null $path
     * @param string|null $filename
     * @param mixed       $disk
     *
     * @return $this
     * @throws Exception
     */
    public function store(?string $path = null, ?string $filename = null, mixed $disk = null): static
    {
        $fileInfo = $this
            ->when(isset($disk), fn () => $this->disk($disk))
            ->when(isset($path), fn () => $this->path($path))
            ->when(isset($filename), fn () => $this->name(fn ($firstname, $extension) => $filename))
            ->captureFileInformation()->fileInformation();

        if (!$this->isExtensionAllowed($fileInfo['_extension'])) {
            throw new RuntimeException('This file type is not allowed');
        }

        $this->data(match ($this->getMediaType()) {
            MediaTypeEnum::Image    => array_merge($this->storeImage(), ['media_type' => strtolower(MediaTypeEnum::Image->toString())]),
            MediaTypeEnum::Audio    => array_merge($this->storeAudio(), ['media_type' => strtolower(MediaTypeEnum::Audio->toString())]),
            MediaTypeEnum::Video    => array_merge($this->storeVideo(), ['media_type' => strtolower(MediaTypeEnum::Video->toString())]),
            MediaTypeEnum::Document => array_merge($this->storeDocument(), ['media_type' => strtolower(MediaTypeEnum::Document->toString())]),
            MediaTypeEnum::Archive  => array_merge($this->storeArchive(), ['media_type' => strtolower(MediaTypeEnum::Archive->toString())]),
            default                 => null,
        })->reset();

        return $this;
    }

    /**
     * @param array       $files
     * @param string|null $path
     * @param string|null $filename
     * @param mixed       $disk
     *
     * @return $this
     * @throws Exception
     */
    public function filesStore(array $files, ?string $path = null, ?string $filename = null, mixed $disk = null): static
    {
        foreach (array_filter($files) as $file) {
            $this->file($file)->store($path, $filename, $disk);
        }
        return $this;
    }

    /* ==================== remove from filesystem ==================== */

    /**
     * @param string|null $path
     * @param string|null $filename
     * @param mixed       $disk
     *
     * @return $this
     */
    public function remove(?string $path = null, ?string $filename = null, mixed $disk = null): static
    {
        $this
            ->when(isset($disk), fn () => $this->disk($disk))
            ->when(isset($path), fn () => $this->path($path))
            ->when(isset($filename), fn () => $this->name(fn ($firstname, $extension) => $filename));

        $name      = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name      = $this->getName() instanceof Closure ? ($this->getName())($name, $extension) : $this->getName();

        Storage::disk($this->getDisk())->delete(trim("{$this->getPath()}/$name", '/'));
        Storage::disk($this->getDisk())->delete(trim("{$this->getPath()}/thumb_$name", '/'));

        return $this;
    }

    /**
     * @param array $files {disk: file path with name} e.g. ['public' => 'path/to/file/example.png']
     *
     * @return $this
     */
    public function removeFiles(array $files): static
    {
        foreach (array_filter($files) as $disk => $file) {
            $path     = pathinfo($file, PATHINFO_DIRNAME);
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $this->remove($path, $filename, $disk);
        }
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Add/Remove data to/from Database
    |--------------------------------------------------------------------------
    */

    /* ==================== ids ==================== */

    /**
     * @return array
     */
    public function getIds(): array
    {
        return array_filter($this->ids);
    }

    /**
     * @param mixed $id
     * @param bool  $fresh
     *
     * @return $this
     */
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

    /* ==================== store to database ==================== */

    /**
     * @param Model|null $model Give the model that you are saving these image(s) for
     *
     * @return $this
     */
    public function save(Model $model = null): static
    {
        $this->when(isset($model), fn () => $this->model($model));
        $model = $this->getModel();
        if (is_null($model)) {
            throw new ModelNotFoundException("Unable to save file to database, Model is not provided");
        }

        $files = [];
        foreach ($this->getData() as $file) {
            $files[] = [
                'group'          => $this->getDisk(),
                'category'       => $file['media_type'],
                'mediaable_id'   => $this->getModel()->id,
                'mediaable_type' => get_morphs_maps($model::class),
                'media_url'      => $file['media']['url'],
                'thumb_url'      => $file['thumb']['url'] ?? $file['media']['url'],
                'media_name'     => $file['media']['name'],
                'thumb_name'     => $file['thumb']['name'] ?? $file['media']['name'],
                'path'           => $file['media']['path'],
                'type'           => $file['type'],
                'extension'      => $file['extension'],
                'media_size'     => $file['media']['size'],
                'thumb_size'     => $file['thumb']['size'] ?? $file['media']['size'],
                'created_at'     => now_now(),
                'updated_at'     => now_now(),
            ];
        }

        foreach (array_chunk($files, 500) as $filesChunk) {
            DB::table(get_model_table($model))->insert($filesChunk);
        }

        $this->setIds(
            Media::toBase()->whereIn('media_name', $this->getData()->pluck('media.name')->all())->pluck('id')->all(),
            true,
        );

        return $this;
    }

    /**
     * @param Media|array|string $media
     * @param mixed              $disk
     *
     * @return mixed
     * @throws Exception
     */
    public function update(Media|array|string $media, mixed $disk = null): mixed
    {
        $this->when(isset($disk), fn () => $this->disk($disk));

        $isMediaInstance = $media instanceof Media;
        if (!$isMediaInstance) {
            $medias = Media::toBase()->whereIn('id', is_array($media) ? $media : explode(',', $media))->get();

            if (collect($medias)->count() !== 1 && collect($medias)->count() !== $this->getData()->count()) {
                throw new RuntimeException('Either pass single instance of media or id, or pass the same number of ids as the files');
            }

            if (collect($medias)->count() === 1) {
                $media           = last($medias);
                $isMediaInstance = true;
            }
        }

        foreach ($this->getData() as $index => $file) {
            if (!$isMediaInstance) {
                $media = $medias[$index];
            }

            $media->group      = $this->getDisk() ?? $media->group;
            $media->category   = $file['media_type'] ?? $media->category;
            $media->media_url  = $file['media']['url'];
            $media->thumb_url  = $file['thumb']['url'] ?? $file['media']['url'];
            $media->media_name = $file['media']['name'];
            $media->thumb_name = $file['thumb']['name'] ?? $file['media']['name'];
            $media->path       = $file['media']['path'];
            $media->type       = $file['type'];
            $media->extension  = $file['extension'];
            $media->media_size = $file['media']['size'];
            $media->thumb_size = $file['thumb']['size'] ?? $file['media']['size'];
            $media->save();
        }

        $this->setIds(
            Media::toBase()->whereIn('media_name', $this->getData()->pluck('media.name')->all())->pluck('id')->all(),
            true,
        );

        return $media;
    }

    /**
     * @param array|string $values
     * @param mixed        $fromDisk
     * @param string       $fromPath
     * @param mixed        $toDisk
     * @param string       $toPath
     * @param string       $column
     *
     * @return $this
     */
    public function move(array|string $values, mixed $fromDisk = 'public', string $fromPath = '', mixed $toDisk = 'public', string $toPath = '', string $column = 'media_name'): static
    {
        $model = $this->getModel();
        if (is_null($model)) {
            throw new ModelNotFoundException("Unable to move file, Model is not provided");
        }

        $values = is_array($values) ? $values : explode(',', $values);
        $medias = Media::whereIn($column, $values)->get();

        $this->setIds([], true);
        foreach ($medias as $media) {
            $filename = $media->media_name;
            $fromPath = trim(sprintf("%s/%s", $this->disk($fromDisk)->getDisk(), $this->path($fromPath)->getPath()), '/\\');
            $toPath   = trim(sprintf("%s/%s", $this->disk($toDisk)->getDisk(), $this->path($toPath)->getPath()), '/\\');

            if (!Storage::directoryExists($toPath)) {
                File::makeDirectory(storage_path("app/$toPath"), 0755, true);
            }

            if (!Storage::move("$fromPath/$filename", "$toPath/$filename")) {
                continue;
            }

            if (isset($media->thumb_name)) {
                Storage::move("$fromPath/thumb_$filename", "$toPath/thumb_$filename");
            }

            $disk = $this->getDisk();
            $path = $this->getPath();

            $media->group          = $disk;
            $media->mediaable_id   = $this->getModel()->id;
            $media->mediaable_type = get_morphs_maps($this->getModel()::class);

            $media->media_url = sprintf("%s/%s", $disk, filled($path) ? "$path/$filename" : $filename); // Storage::disk($disk)->url("$path/$filename");
            $media->thumb_url = sprintf("%s/%s", $disk, filled($path) ? "$path/thumb_$filename" : "thumb_$filename"); // Storage::disk($disk)->url("$path/thumb_$filename");
            $media->path      = Storage::disk($disk)->path("$path/$filename");
            $media->save();

            $this->setIds($media->id);
        }

        return $this;
    }

    /* ==================== remove from database ==================== */

    /**
     * @param array|string $values
     * @param string       $column
     * @param bool         $removeFromStorage
     *
     * @return $this
     */
    public function destroy(array|string $values, string $column = 'id', bool $removeFromStorage = false): static
    {
        $values = is_array($values) ? $values : explode(',', $values);
        $query  = Media::whereIn($column, $values);

        $medias = collect($query->select('id', 'group', 'media_name', 'path')->all());

        $query->delete();

        $this->setIds(
            $medias->pluck('id')->all(),
            true
        );

        if ($removeFromStorage) {
            $this->removeFiles(
                $medias->map(function ($media) {
                    $media->full_path = "$media->path/$media->media_name";
                    return $media;
                })->pluck('full_path', 'group')->all()
            );
        }

        return $this;
    }

}
