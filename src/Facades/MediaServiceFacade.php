<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Facades;

use Illuminate\Support\Facades\Facade;

class MediaServiceFacade extends Facade
{
    protected static $cached = false;

    protected static function getFacadeAccessor()
    {
        return 'MediaService';
    }
}
