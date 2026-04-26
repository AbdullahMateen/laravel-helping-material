# Upgrade Guide: 2.x To 3.x

This guide is for applications upgrading from `abdullah-mateen/laravel-helping-material:2.x` to `3.x`.

The largest change in `3.x` is the media layer. The public chainable `MediaService` API is mostly preserved, but the internal implementation is now class-based and no longer depends on the old media traits. Version `3.x` also adds typed convenience services for images, audio, video, documents, and archives.

## Quick Upgrade Checklist

1. Update Composer to require `3.x`.
2. Refresh package discovery and autoload files.
3. Republish config only if you want the latest comments/defaults.
4. Review any custom app-level media service overrides.
5. Replace split facade calls with a single chain or an injected/resolved `MediaService` instance.
6. Use typed services for new upload code: `ImageService`, `AudioService`, `VideoService`, `DocumentService`, and `ArchiveService`.
7. Run your file upload flows and database media persistence tests.

```sh
composer require abdullah-mateen/laravel-helping-material:^3.0
composer dump-autoload
php artisan package:discover
```

## Compatibility Summary

| Area | 2.x | 3.x | Upgrade action |
| --- | --- | --- | --- |
| PHP runtime | `^8.1` | `^8.1`, verified on PHP 8.4/8.5 | No composer change required unless your app pins older PHP. |
| Laravel dev compatibility | Laravel 10/11 | Laravel 10/11/12/13 | Upgrade app dependencies normally. |
| PHPUnit dev compatibility | PHPUnit 10 | PHPUnit 10/11/12 | Update tests only if your app moves PHPUnit major versions. |
| `MediaService` internals | Uses `ImageTrait`, `AudioTrait`, `VideoTrait`, `DocumentTrait`, `ArchiveTrait` | Uses `MediaFilesystem`, `ImageMediaHandler`, `BinaryMediaHandler`, `MediaStoreRequest` | Update subclasses or custom code that called trait/private internals. |
| Typed media services | Not available | `ImageService`, `AudioService`, `VideoService`, `DocumentService`, `ArchiveService` | Optional, recommended for new code. |
| Facade state | Facade could keep a resolved service instance | Facade is uncached and the service binding is transient | Do not split stateful facade calls across statements. |
| `getMediaType()` | Private | Public | You may now inspect resolved media type safely. |
| Published model stub | Had `namespace {{ namespace }};;` typo | Fixed to `namespace {{ namespace }};` | Republish or manually fix generated stubs if needed. |
| `lhm:publish` paths | Used vendor path assumptions | Uses package-relative paths | Republish works better for local/path repositories. |
| Nullable signatures | Some nullable defaults were not explicitly nullable | Explicit nullable types | Update child method signatures if you override those methods. |

## Breaking Change: MediaService No Longer Uses Media Traits Internally

In `2.x`, `MediaService` used these traits directly:

```php
use ImageTrait, AudioTrait, VideoTrait, DocumentTrait, ArchiveTrait, Tappable;
```

In `3.x`, `MediaService` delegates storage work to dedicated classes:

```php
MediaFilesystem
ImageMediaHandler
BinaryMediaHandler
MediaStoreRequest
```

The common public methods still exist:

```php
file()
store()
filesStore()
save()
update()
move()
destroy()
remove()
removeFiles()
getData()
getIds()
modifying()
thumbnail()
```

### Who Needs To Change Code?

You need changes only if your app extended `MediaService` and called or overrode internal implementation details such as:

```php
storeImage()
storeAudio()
storeVideo()
storeDocument()
storeArchive()
generateFileAttributes()
resolveFilePath()
resolveFileUrl()
```

Those were implementation details in `2.x` and are no longer part of `MediaService` in `3.x`.

### Fix

Use the public API for normal uploads:

```php
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;

$media = app(MediaService::class)
    ->file($request->file('attachment'))
    ->store('tickets/'.$ticket->id, null, 'public')
    ->save($ticket);

$data = $media->getData()->values()->all();
$ids = $media->getIds(false);
```

For image-specific uploads, use `ImageService`:

```php
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\ImageService;

$result = ImageService::persist(
    file: $request->file('avatar'),
    model: $request->user(),
    path: 'users/'.$request->user()->id.'/profile',
    filename: 'avatar',
    disk: 'public',
);
```

For custom storage behavior, prefer composing around the public API. If you need lower-level customization, extend the new handler/filesystem classes instead of depending on the old media traits.

## Breaking Change: Stateful Facade Calls Must Be Chained

In `3.x`, `MediaServiceFacade` sets:

```php
protected static bool $cached = false;
```

The container binding for `MediaService::class` is also transient. This prevents file/data state from leaking between unrelated uploads, but code that split a single upload across multiple facade calls can break.

### Before

```php
MediaService::file($request->file('avatar'));
MediaService::store('avatars', 'avatar', 'public');
MediaService::save($user);
```

### After

Use one chain:

```php
MediaService::file($request->file('avatar'))
    ->store('avatars', 'avatar', 'public')
    ->save($user);
```

Or resolve one instance:

```php
$media = app(MediaService::class);

$media->file($request->file('avatar'));
$media->store('avatars', 'avatar', 'public');
$media->save($user);
```

Typed services are simpler for common cases:

```php
ImageService::persist($request->file('avatar'), $user, 'avatars', 'avatar', 'public');
```

## New Typed Media Services

Version `3.x` adds service classes that wrap `MediaService` and enforce the expected media type.

| Service | Accepts | Example |
| --- | --- | --- |
| `ImageService` | image files | `ImageService::store($file, 'images', null, 'public')` |
| `AudioService` | audio files | `AudioService::store($file, 'audio', null, 'public')` |
| `VideoService` | video files | `VideoService::store($file, 'videos', null, 'public')` |
| `DocumentService` | document files | `DocumentService::store($file, 'documents', null, 'public')` |
| `ArchiveService` | archive files | `ArchiveService::store($file, 'archives', null, 'public')` |

Each typed service supports:

```php
store()
storeMany()
persist()
service()
make()
resolveUsing()
clearResolver()
```

### Store To Filesystem Only

```php
$data = DocumentService::store(
    file: $request->file('document'),
    path: 'uploads/documents',
    disk: 'public',
);
```

### Store To Filesystem And Database

```php
$result = DocumentService::persist(
    file: $request->file('document'),
    model: $invoice,
    path: 'invoices/'.$invoice->id,
    disk: 'public',
);

$data = $result['data'];
$ids = $result['ids'];
```

### Store Without A Model Relation

```php
$result = DocumentService::persist(
    file: $request->file('document'),
    model: null,
    path: 'unattached/documents',
    disk: 'public',
);
```

## MediaService Public API Migration Examples

### Profile Picture

```php
// 2.x and 3.x compatible chainable style
$media = app(MediaService::class)
    ->file($request->file('avatar'))
    ->thumbnail(fn ($image) => $image->scale(width: 320))
    ->store('users/'.$user->id.'/profile', 'avatar', 'public')
    ->save($user);
```

Recommended `3.x` typed style:

```php
$result = ImageService::persist(
    file: $request->file('avatar'),
    model: $user,
    path: 'users/'.$user->id.'/profile',
    filename: 'avatar',
    disk: 'public',
);
```

### Multiple Attachments

```php
$media = app(MediaService::class)
    ->filesStore($request->file('attachments'), 'tickets/'.$ticket->id, null, 'public')
    ->save($ticket);
```

### Temporary Upload Then Move

```php
$temporary = DocumentService::persist(
    file: $request->file('document'),
    model: null,
    path: 'temporary/documents',
    disk: 'public',
);

$media = app(MediaService::class)
    ->model($user)
    ->move(
        values: $temporary['ids'],
        fromDisk: 'public',
        fromPath: 'temporary/documents',
        toDisk: 'public',
        toPath: 'users/'.$user->id.'/documents',
        column: 'id',
    );
```

### Move From Public Disk To S3

```php
app(MediaService::class)
    ->model($user)
    ->move(
        values: [1, 2, 3],
        fromDisk: 'public',
        fromPath: 'users/'.$user->id.'/documents',
        toDisk: 's3',
        toPath: 'users/'.$user->id.'/documents',
        column: 'id',
    );
```

## Published Files And Overrides

If you published files in `2.x`, those files do not update automatically when Composer upgrades the package.

Review these paths in your app:

```text
config/lhm.php
app/Services/Media/MediaService.php
app/Models/Media.php
app/Enums/Media/MediaDiskEnum.php
app/Http/Middleware/AuthorizationMiddleware.php
```

### App Service Override

If your app has this published file:

```php
namespace App\Services\Media;

use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService as BaseService;

class MediaService extends BaseService
{
}
```

It can remain as-is.

If it overrides internal media trait behavior, move that code to the public chainable API or to a custom wrapper service.

## Config Review

The `config/lhm.php` shape is compatible between `2.x` and `3.x`.

Still review these values after upgrading:

```php
'media_service' => [
    'model' => App\Models\Media::class,
    'media_disk_enum' => App\Enums\Media\MediaDiskEnum::class,
    'extensions' => [
        'image' => ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'svg', 'webp'],
        'audio' => ['mp3', 'aac', 'ogg', 'flac', 'alac', 'wav', 'aiff', 'dsd', 'pcm'],
        'video' => ['mp3', 'mp4', 'mov', 'webm'],
        'document' => ['pdf', 'doc', 'docx', 'csv', 'xlx', 'txt', 'pptx', 'divx'],
        'archive' => ['7z', 's7z', 'apk', 'jar', 'rar', 'tar.gz', 'tgz', 'tarZ', 'tar', 'zip', 'zipx'],
    ],
],
```

If you use a custom media model or custom disk enum, keep those config values.

## Model And Stub Changes

### `manageDeviceToken()`

`AuthenticatableExtendedModel::manageDeviceToken()` now explicitly accepts a nullable string:

```php
public function manageDeviceToken(?string $token = null): static
```

If your app overrides this method, update the child signature to match:

```php
public function manageDeviceToken(?string $token = null): static
{
    return parent::manageDeviceToken($token);
}
```

### Model Stub Namespace Fix

The `make:lhm-model` stub no longer generates a double semicolon after the namespace.

If you generated models with `2.x` and see this:

```php
namespace App\Models;;
```

Fix it manually:

```php
namespace App\Models;
```

## Command Changes

`lhm:publish` now resolves files relative to the package directory instead of assuming the package is installed at `vendor\abdullah-mateen\laravel-helping-material`.

This matters if you use:

- Composer path repositories.
- Local package development.
- Symlinked packages.
- Non-standard vendor directories.

No application code change is needed. If `lhm:publish` failed in `2.x` in a local package setup, retry it after upgrading.

## Enum Trait Fix

`GeneralTrait::asArray()` now uses `static::class` instead of `__CLASS__`.

This improves enum/helper behavior when used through the consuming enum class. No migration is required unless your app depended on the old incorrect reflection behavior.

## Migrations

The migration files are the same between `2.x` and `3.x`. Do not re-run already executed migrations.

If this is a new install:

```sh
php artisan migrate
```

If you publish migrations:

```sh
php artisan vendor:publish --tag=laravel-helping-material-migrations
```

## Recommended Upgrade Test Plan

Run the flows your app actually uses:

```sh
composer update abdullah-mateen/laravel-helping-material --with-dependencies
php artisan package:discover
php artisan config:clear
php artisan test
```

Then manually or automatically test:

- Single image upload.
- Thumbnail generation.
- Document upload.
- Multiple file upload.
- `save()` database persistence.
- `update()` media replacement.
- Temporary upload then `move()`.
- `destroy()` with and without storage deletion.
- Any custom published `App\Services\Media\MediaService` behavior.

## Common Upgrade Errors

### `Call to undefined method MediaService::storeImage()`

Your app is calling an old internal trait method.

Fix by using:

```php
app(MediaService::class)->file($file)->store($path, null, 'public');
```

Or:

```php
ImageService::store($file, $path, null, 'public');
```

### File State Is Missing Between Facade Calls

This usually means stateful facade calls were split across statements.

Fix by chaining:

```php
MediaService::file($file)->store($path)->save($model);
```

Or by resolving one service instance:

```php
$media = app(MediaService::class);
$media->file($file);
$media->store($path);
$media->save($model);
```

### `Declaration ... must be compatible`

Your app overrides a method whose nullable signature changed.

Fix child signatures to use explicit nullable types, for example:

```php
public function manageDeviceToken(?string $token = null): static
```

### Typed Service Rejects A File

Typed services validate media type. For example, `ImageService` rejects documents.

Fix by using the correct typed service:

```php
DocumentService::store($request->file('document'), 'documents', null, 'public');
```

Or use raw `MediaService` for mixed uploads:

```php
app(MediaService::class)->filesStore($files, 'attachments', null, 'public');
```
