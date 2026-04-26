<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Commands\LhmPublishCommand;
use AbdullahMateen\LaravelHelpingMaterial\Models\AuthenticatableExtendedModel;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
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
}
