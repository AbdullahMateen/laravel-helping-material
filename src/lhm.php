<?php

return [

    'api' => [
        'convert_keys_to_snake_case' => true,
    ],

    'models' => [
        'should_be_strict' => true,
    ],

    'storage' => [
        'default_folder' => 'storage',
    ],

    'media_service' => [
        'media_disk_enum' => \AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaDiskEnum::class,
        'extensions'      => [
            'image'    => ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'svg', 'webp'],
            'audio'    => ['mp3', 'aac', 'ogg', 'flac', 'alac', 'wav', 'aiff', 'dsd', 'pcm'],
            'video'    => ['mp3', 'mp4', 'mov', 'webm'],
            'document' => ['pdf', 'doc', 'docx', 'csv', 'xlx', 'txt', 'pptx', 'divx'],
            'archive'  => ['7z', 's7z', 'apk', 'jar', 'rar', 'tar.gz', 'tgz', 'tarZ', 'tar', 'zip', 'zipx'],
        ],
    ],

];




