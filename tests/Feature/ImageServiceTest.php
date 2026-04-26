<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ImageService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImageServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        ImageService::clearResolver();

        parent::tearDown();
    }

    public function test_image_service_store_uses_media_service_convenience_flow(): void
    {
        $service = new class extends MediaService
        {
            public array $calls = [];

            public function __construct()
            {
            }

            public function file(\Intervention\Image\Image|\Illuminate\Http\UploadedFile|string|null $file): static
            {
                $this->calls[] = 'file';

                return $this;
            }

            public function getMediaType(): ?MediaTypeEnum
            {
                return MediaTypeEnum::Image;
            }

            public function store(?string $path = null, ?string $filename = null, mixed $disk = null): static
            {
                $this->calls[] = 'store';

                return $this;
            }

            public function getData(): Collection
            {
                return collect([[
                    'media' => ['unique' => 'avatar_unique.png'],
                    'thumb' => null,
                    'type' => 'image/png',
                    'extension' => 'png',
                ]]);
            }
        };

        ImageService::resolveUsing(fn (): MediaService => $service);

        $result = ImageService::store('fake-image');

        self::assertSame(['file', 'store'], $service->calls);
        self::assertSame('avatar_unique.png', $result['media']['unique']);
    }

    public function test_image_service_rejects_non_image_media(): void
    {
        $service = new class extends MediaService
        {
            public function __construct()
            {
            }

            public function file(\Intervention\Image\Image|\Illuminate\Http\UploadedFile|string|null $file): static
            {
                return $this;
            }

            public function getMediaType(): ?MediaTypeEnum
            {
                return MediaTypeEnum::Document;
            }
        };

        ImageService::resolveUsing(fn (): MediaService => $service);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ImageService only accepts image files.');

        ImageService::store('fake-document');
    }
}
