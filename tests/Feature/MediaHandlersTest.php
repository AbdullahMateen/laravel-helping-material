<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Services\Media\BinaryMediaHandler;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ImageMediaHandler;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaFilesystem;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaStoreRequest;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use PHPUnit\Framework\TestCase;

final class MediaHandlersTest extends TestCase
{
    public function test_binary_media_handler_stores_uploaded_file_through_filesystem_class(): void
    {
        $uploadedFile = $this->createUploadedFile('audio/mp3', 'track.mp3', 'audio-content');

        $filesystem = $this->createMock(MediaFilesystem::class);
        $filesystem->expects(self::once())
            ->method('storeUploadedFile')
            ->with($uploadedFile, 'audio', 'track_unique.mp3', 'public');
        $filesystem->expects(self::once())
            ->method('fileAttributes')
            ->with('public', 'audio', 'track_unique.mp3', 'track.mp3')
            ->willReturn([
                'name' => 'track.mp3',
                'unique' => 'track_unique.mp3',
                'path' => 'audio/track_unique.mp3',
                'file_path' => 'public/audio/track_unique.mp3',
                'size' => 12,
                'url' => 'public/audio/track_unique.mp3',
            ]);
        $filesystem->expects(self::once())
            ->method('mimeType')
            ->with('public', 'audio', 'track_unique.mp3')
            ->willReturn('audio/mpeg');

        $handler = new BinaryMediaHandler($filesystem);

        $result = $handler->store(new MediaStoreRequest(
            file: $uploadedFile,
            path: 'audio',
            disk: 'public',
            fileInformation: [
                '_original' => 'track.mp3',
                '_name' => 'track',
                '_extension' => 'mp3',
                'name' => 'track_unique.mp3',
                'unique' => 'track_unique.mp3',
            ],
        ));

        self::assertSame('audio/mpeg', $result['type']);
        self::assertSame('mp3', $result['extension']);
        self::assertNull($result['thumb']);
        self::assertSame('track_unique.mp3', $result['media']['unique']);
    }

    public function test_image_media_handler_generates_thumbnail_without_traits(): void
    {
        $uploadedFile = $this->createPngUploadedFile('avatar.png');

        $filesystem = $this->createMock(MediaFilesystem::class);
        $filesystem->expects(self::once())
            ->method('storeUploadedFile')
            ->with($uploadedFile, 'avatars', 'avatar_unique.png', 'public');
        $filesystem->expects(self::once())
            ->method('saveImage')
            ->with(
                self::isInstanceOf(Image::class),
                'avatars',
                'thumb_avatar_unique.png',
                'public',
            );
        $filesystem->expects(self::exactly(2))
            ->method('fileAttributes')
            ->willReturnCallback(function (string $disk, string $path, string $filename, string $originalName): array {
                return [
                    'name' => $originalName,
                    'unique' => $filename,
                    'path' => trim("$path/$filename", '/'),
                    'file_path' => "public/$path/$filename",
                    'size' => 12,
                    'url' => "public/$path/$filename",
                ];
            });
        $filesystem->expects(self::once())
            ->method('mimeType')
            ->with('public', 'avatars', 'avatar_unique.png')
            ->willReturn('image/png');

        $handler = new ImageMediaHandler($filesystem);

        $result = $handler->store(new MediaStoreRequest(
            file: $uploadedFile,
            path: 'avatars',
            disk: 'public',
            fileInformation: [
                '_original' => 'avatar.png',
                '_name' => 'avatar',
                '_extension' => 'png',
                'name' => 'avatar_unique.png',
                'unique' => 'avatar_unique.png',
            ],
            hasThumbnail: true,
        ));

        self::assertSame('image/png', $result['type']);
        self::assertSame('png', $result['extension']);
        self::assertSame('avatar_unique.png', $result['media']['unique']);
        self::assertSame('thumb_avatar_unique.png', $result['thumb']['unique']);
    }

    private function createUploadedFile(string $mimeType, string $originalName, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'lhm-media');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $originalName, $mimeType, null, true);
    }

    private function createPngUploadedFile(string $originalName): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'lhm-png');
        $image = imagecreatetruecolor(2, 2);

        imagepng($image, $path);

        return new UploadedFile($path, $originalName, 'image/png', null, true);
    }
}
