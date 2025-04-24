<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This section controls the API response settings. Enabling the
    | conversion of response keys to snake_case ensures consistency and
    | better readability across your RESTful API responses.
    |
    | If true, all keys in API responses will be converted to snake_case.
    */
    'api'           => [
        'response_keys' => [
            'snake_case' => env('LHM_SNAKE_CASE', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Strict Model Behavior
    | This setting controls whether to enforces strict model behavior
    | via Laravel's Model::shouldBeStrict(). Enabling strict mode
    | can help prevent issues such as lazy loading of relationships.
    | Set it to false to disable strict model behavior.
    |
    | User Model
    | This setting allows you to specify the user model class used
    */
    'models'        => [
        'should_be_strict' => env('LHM_SHOULD_BE_STRICT', false),

        'user' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | These settings define how to handles file storage.
    | - 'folder' specifies the folder name for symbolic links created by
    |   "php artisan storage:link".
    | - 'shared' enables you to use a shared storage location across multiple
    |   projects by specifying a shared path.
    |
    */
    'storage'       => [
        // The default folder name for symbolic links in the public directory.
        'folder' => env('STORAGE_FOLDER', 'storage'),

        // Shared storage configuration.
        'shared' => [
            // Set to true to enable shared storage across projects.
            'enabled' => env('SHARED_STORAGE', false),
            // Define the path to the shared storage location.
            'path'    => env('SHARED_STORAGE_PATH', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Service Configuration
    |--------------------------------------------------------------------------
    |
    | This section configures the media service functionality.
    | - 'media_disk_enum' specifies the class used for managing media disks.
    | - 'extensions' lists the allowed file extensions for various media types.
    |
    */
    'media_service' => [
        // Class that defines media model.
        'model'           => \AbdullahMateen\LaravelHelpingMaterial\Models\Media::class,

        // Class that defines available media disks.
        'media_disk_enum' => \AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaDiskEnum::class,

        // Allowed file extensions categorized by media type.
        'extensions'      => [
            'image'    => ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'svg', 'webp'],
            'audio'    => ['mp3', 'aac', 'ogg', 'flac', 'alac', 'wav', 'aiff', 'dsd', 'pcm'],
            'video'    => ['mp3', 'mp4', 'mov', 'webm'],
            'document' => ['pdf', 'doc', 'docx', 'csv', 'xlx', 'txt', 'pptx', 'divx'],
            'archive'  => ['7z', 's7z', 'apk', 'jar', 'rar', 'tar.gz', 'tgz', 'tarZ', 'tar', 'zip', 'zipx'],
        ],
    ],

];


