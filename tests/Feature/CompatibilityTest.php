<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Commands\LhmPublishCommand;
use AbdullahMateen\LaravelHelpingMaterial\LaravelHelpingMaterialServiceProvider;
use AbdullahMateen\LaravelHelpingMaterial\Models\AuthenticatableExtendedModel;
use AbdullahMateen\LaravelHelpingMaterial\Models\Media;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

final class CompatibilityTest extends TestCase
{
    public function test_nullable_parameters_are_explicitly_declared(): void
    {
        self::assertTrue((new ReflectionFunction('url_to_uploaded_file'))->getParameters()[1]->getType()?->allowsNull());
        self::assertTrue((new ReflectionFunction('calculate_age'))->getParameters()[1]->getType()?->allowsNull());
        self::assertTrue((new ReflectionFunction('is_age_acceptable'))->getParameters()[1]->getType()?->allowsNull());
        self::assertTrue((new ReflectionMethod(AuthenticatableExtendedModel::class, 'manageDeviceToken'))->getParameters()[0]->getType()?->allowsNull());
        self::assertTrue((new ReflectionMethod(MediaService::class, 'resolveMediaTypeByExtension'))->getParameters()[0]->getType()?->allowsNull());
        self::assertTrue((new ReflectionMethod(MediaService::class, 'save'))->getParameters()[0]->getType()?->allowsNull());
    }

    public function test_publish_command_declares_internal_path_properties(): void
    {
        $reflection = new ReflectionClass(LhmPublishCommand::class);

        self::assertTrue($reflection->hasProperty('basepath'));
        self::assertTrue($reflection->hasProperty('prefix'));
    }

    public function test_media_service_no_longer_relies_on_media_traits(): void
    {
        self::assertSame([], (new ReflectionClass(MediaService::class))->getTraitNames());
    }

    public function test_package_config_keeps_published_nested_values(): void
    {
        $app = new Container();
        $app->instance('config', new Repository([
            'lhm' => [
                'models' => [
                    'should_be_strict' => true,
                ],
                'storage' => [
                    'folder' => 'uploads',
                ],
            ],
        ]));

        (new LaravelHelpingMaterialServiceProvider($app))->register();

        self::assertTrue($app->make('config')->get('lhm.models.should_be_strict'));
        self::assertSame('uploads', $app->make('config')->get('lhm.storage.folder'));
        self::assertSame(Media::class, $app->make('config')->get('lhm.media_service.model'));
    }

    public function test_package_config_replaces_list_values_from_published_config(): void
    {
        $app = new Container();
        $app->instance('config', new Repository([
            'lhm' => [
                'media_service' => [
                    'extensions' => [
                        'image' => ['avif'],
                    ],
                ],
            ],
        ]));

        (new LaravelHelpingMaterialServiceProvider($app))->register();

        self::assertSame(['avif'], $app->make('config')->get('lhm.media_service.extensions.image'));
    }
}
