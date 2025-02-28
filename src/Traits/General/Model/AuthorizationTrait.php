<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

use Illuminate\Support\Facades\Gate;

trait AuthorizationTrait
{
    /*
    |--------------------------------------------------------------------------
    | Gate Allow
    |--------------------------------------------------------------------------
    */

    public static function allows($ability, $self = null)
    {
        return Gate::allows($ability, $self ?? static::class);
    }

    public static function allowIf($condition, $message, $code)
    {
        return Gate::allowIf($condition, $message, $code);
    }

    /*
    |--------------------------------------------------------------------------
    | Gate Denies
    |--------------------------------------------------------------------------
    */

    public static function denies($ability, $self = null)
    {
        return Gate::denies($ability, $self ?? static::class);
    }

    public static function denyIf($condition, $message, $code)
    {
        return Gate::denyIf($condition, $message, $code);
    }

    /*
    |--------------------------------------------------------------------------
    | Gate Authorize
    |--------------------------------------------------------------------------
    */

    public static function authorize($ability, $self = null, ...$data)
    {
        Gate::authorize($ability, [$self ?? static::class, ...$data]);
    }

    public static function authorizeViewAny(...$data)
    {
        Gate::authorize('viewAny', [static::class, ...$data]);
    }

    public static function authorizeCreate(...$data)
    {
        Gate::authorize('create', [static::class, ...$data]);
    }

    public static function authorizeView($self, ...$data)
    {
        Gate::authorize('view', [$self, ...$data]);
    }

    public static function authorizeEdit($self, ...$data)
    {
        Gate::authorize('update', [$self, ...$data]);
    }

    public static function authorizeDelete($self, ...$data)
    {
        Gate::authorize('delete', [$self, ...$data]);
    }

    public static function authorizeRestore($self, ...$data)
    {
        Gate::authorize('restore', [$self, ...$data]);
    }

    public static function authorizeForceDelete($self, ...$data)
    {
        Gate::authorize('forceDelete', [$self, ...$data]);
    }
}
