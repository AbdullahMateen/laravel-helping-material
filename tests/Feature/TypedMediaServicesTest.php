<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ArchiveService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\AudioService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\DocumentService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\VideoService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TypedMediaServicesTest extends TestCase
{
    protected function tearDown(): void
    {
        AudioService::clearResolver();
        VideoService::clearResolver();
        DocumentService::clearResolver();
        ArchiveService::clearResolver();

        parent::tearDown();
    }

    public function test_remaining_typed_services_use_media_service_convenience_flow(): void
    {
        foreach ($this->serviceTypes() as $serviceClass => $mediaType) {
            $service = $this->fakeMediaService($mediaType);

            $serviceClass::resolveUsing(fn (): MediaService => $service);

            $result = $serviceClass::store('fake-media');

            self::assertSame(['file', 'store'], $service->calls);
            self::assertSame(strtolower($mediaType->name), $result['extension']);

            $serviceClass::clearResolver();
        }
    }

    public function test_remaining_typed_services_reject_wrong_media_type(): void
    {
        $expectedMessages = [
            AudioService::class => 'AudioService only accepts audio files.',
            VideoService::class => 'VideoService only accepts video files.',
            DocumentService::class => 'DocumentService only accepts document files.',
            ArchiveService::class => 'ArchiveService only accepts archive files.',
        ];

        foreach ($expectedMessages as $serviceClass => $message) {
            $serviceClass::resolveUsing(fn (): MediaService => $this->fakeMediaService(MediaTypeEnum::Image));

            try {
                $serviceClass::store('fake-image');
                self::fail("Expected $serviceClass to reject an image media type.");
            } catch (RuntimeException $exception) {
                self::assertSame($message, $exception->getMessage());
            }

            $serviceClass::clearResolver();
        }
    }

    /**
     * @return array<class-string, MediaTypeEnum>
     */
    private function serviceTypes(): array
    {
        return [
            AudioService::class => MediaTypeEnum::Audio,
            VideoService::class => MediaTypeEnum::Video,
            DocumentService::class => MediaTypeEnum::Document,
            ArchiveService::class => MediaTypeEnum::Archive,
        ];
    }

    private function fakeMediaService(MediaTypeEnum $mediaType): MediaService
    {
        return new class($mediaType) extends MediaService
        {
            public array $calls = [];

            public function __construct(private MediaTypeEnum $mediaType)
            {
            }

            public function file(\Intervention\Image\Image|\Illuminate\Http\UploadedFile|string|null $file): static
            {
                $this->calls[] = 'file';

                return $this;
            }

            public function getMediaType(): ?MediaTypeEnum
            {
                return $this->mediaType;
            }

            public function store(?string $path = null, ?string $filename = null, mixed $disk = null): static
            {
                $this->calls[] = 'store';

                return $this;
            }

            public function getData(): Collection
            {
                return collect([[
                    'media' => ['unique' => strtolower($this->mediaType->name).'_unique'],
                    'thumb' => null,
                    'type' => false,
                    'extension' => strtolower($this->mediaType->name),
                ]]);
            }
        };
    }
}
