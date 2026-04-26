<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use RuntimeException;
use Throwable;

abstract class TypedMediaService
{
    /**
     * @var array<class-string<static>, Closure>
     */
    private static array $resolvers = [];

    abstract protected static function mediaType(): MediaTypeEnum;

    public static function resolveUsing(?Closure $resolver): void
    {
        if ($resolver === null) {
            static::clearResolver();

            return;
        }

        self::$resolvers[static::class] = $resolver;
    }

    public static function clearResolver(): void
    {
        unset(self::$resolvers[static::class]);
    }

    public static function make(): MediaService
    {
        $resolver = self::$resolvers[static::class] ?? null;

        if ($resolver !== null) {
            return $resolver();
        }

        if (function_exists('app')) {
            try {
                return app(MediaService::class);
            } catch (Throwable) {
            }
        }

        return new MediaService();
    }

    /**
     * @return array{media: array, thumb: array|null, type: false|string, extension: string}
     */
    public static function store(UploadedFile|Image|string $file, ?string $path = null, ?string $filename = null, mixed $disk = null): array
    {
        $service = static::make()->file($file);

        static::ensureMediaType($service);

        $service->store($path, $filename, $disk);

        return $service->getData()->last() ?? [];
    }

    /**
     * @return array<int, array{media: array, thumb: array|null, type: false|string, extension: string}>
     */
    public static function storeMany(array $files, ?string $path = null, ?string $filename = null, mixed $disk = null): array
    {
        $service = static::make();

        foreach (array_filter($files) as $file) {
            $service->file($file);
            static::ensureMediaType($service);
            $service->store($path, $filename, $disk);
        }

        return $service->getData()->values()->all();
    }

    /**
     * @return array{data: array<int, array{media: array, thumb: array|null, type: false|string, extension: string}>, ids: array}
     */
    public static function persist(UploadedFile|Image|string $file, ?Model $model = null, ?string $path = null, ?string $filename = null, mixed $disk = null): array
    {
        $service = static::make()->file($file);

        static::ensureMediaType($service);

        $service->store($path, $filename, $disk)->save($model);

        return [
            'data' => $service->getData()->values()->all(),
            'ids' => $service->getIds(false),
        ];
    }

    public static function service(UploadedFile|Image|string|null $file = null): MediaService
    {
        $service = static::make();

        if ($file !== null) {
            $service->file($file);
            static::ensureMediaType($service);
        }

        return $service;
    }

    protected static function ensureMediaType(MediaService $service): void
    {
        if ($service->getMediaType() !== static::mediaType()) {
            throw new RuntimeException(sprintf(
                '%s only accepts %s files.',
                static::shortName(),
                strtolower(static::mediaType()->name),
            ));
        }
    }

    private static function shortName(): string
    {
        $class = static::class;
        $separatorPosition = strrpos($class, '\\');

        return $separatorPosition === false ? $class : substr($class, $separatorPosition + 1);
    }
}
