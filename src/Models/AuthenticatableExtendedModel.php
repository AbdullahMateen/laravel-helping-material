<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Models;

use AbdullahMateen\LaravelHelpingMaterial\Enums\StatusEnum;
use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\AuthorizationTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ModelFetchTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ScopeTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\UserNotificationsTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ValidationRulesHelperTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ValidationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method static columns()
 * @method static auth()
 * @method static byUser()
 * @method static byLevel()
 * @method static whereDateBetween()
 * @method static active()
 * @method static inactive()
 * @method static blocked()
 */
class AuthenticatableExtendedModel extends Authenticatable implements ColorsInterface
{
    use HasFactory, Notifiable,
        AuthorizationTrait, ModelFetchTrait, UserNotificationsTrait,
        ScopeTrait, ValidationTrait, ValidationRulesHelperTrait;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token',];

    protected $casts = [];

    /*
    |--------------------------------------------------------------------------
    | Override Methods
    |--------------------------------------------------------------------------
    */

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes(array_merge($this->attributes, [
            'status' => StatusEnum::Active->value,
        ]), true);
        parent::__construct($attributes);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Const Variables
    |--------------------------------------------------------------------------
    */

    /* ================= Pages ======================== */
    public const KEY_PAGE_TYPE  = 'pageType';
    public const KEY_PAGE_INDEX = 'index';
    public const KEY_PAGE_TRASH = 'trash';

    /* ================= Forms ======================== */
    public const KEY_FORM_TYPE        = 'formType';
    public const KEY_FORM_TYPE_CREATE = 'create';
    public const KEY_FORM_TYPE_EDIT   = 'edit';

    /*
    |--------------------------------------------------------------------------
    | Scope Methods
    |--------------------------------------------------------------------------
    */

    public function scopeColumns($query, $columns = [], $overwrite = false)
    {
        $default = ['id', 'firstname', 'lastname'];
        $columns = is_array($columns) ? $columns : explode(',', $columns);
        $columns = $overwrite ? $columns : array_merge($default, $columns);
        return $query->select($columns);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Validations
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute()
    {
        return trim("$this->firstname $this->lastname");
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    */

    public function readable()
    {
        $this->statusName  = method_exists($this, 'statusName') ? $this->statusName() : '';
        $this->statusColor = method_exists($this, 'statusColor') ? $this->statusColor() : '';

        return $this;
    }

    public function isLevel($level)
    {
        return is_array($level) ? in_array($this->level, $level) : $this->level == $level;
    }

    public function isRole($role)
    {
        return $this->role == $role;
    }

    public function isActive()
    {
        return $this->casts['status']::Active->equalsTo($this->status->value);
    }

    public function isBlocked()
    {
        return $this->casts['status']::Blocked->equalsTo($this->status->value);
    }

    public function deletable()
    {
        return true;
    }

    public function deleteInstance()
    {
        $this->delete();
    }

    public function restorable()
    {
        return true;
    }

    public function restoreInstance()
    {
        return $this->restore();
    }

    public function forceDeletable()
    {
        return true;
    }

    public function forceDeleteInstance()
    {
        return $this->forceDelete();
    }

    public function statusName()
    {
        return $this->status instanceof \BackedEnum ? $this->status->toString() : StatusEnum::tryFrom($this->status)?->toString();
    }

    public function statusColor()
    {
        return $this->status instanceof \BackedEnum ? $this->status->color() : StatusEnum::tryFrom($this->status)?->color();
    }

    /**
     * @param string|null $token
     *
     * @return $this
     */
    public function manageDeviceToken(string $token = null): static
    {
        if (isset($token) && $this->device_token !== $token) {
            $this->update(['device_token' => $token]);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


}
