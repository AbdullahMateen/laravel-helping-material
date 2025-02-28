<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Models;

use AbdullahMateen\LaravelHelpingMaterial\Enums\StatusEnum;
use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\AuthorizationTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ModelFetchTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ScopeTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ValidationRulesHelperTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model\ValidationTrait;
use Illuminate\Database\Eloquent\Model;

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
class ExtendedModel extends Model implements ColorsInterface
{
    use AuthorizationTrait, ModelFetchTrait, ScopeTrait, ValidationTrait, ValidationRulesHelperTrait;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

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
        return $this->status instanceof StatusEnum ? $this->status->toString() : StatusEnum::tryFrom($this->status)?->toString();
    }

    public function statusColor()
    {
        return $this->status instanceof StatusEnum ? $this->status->color() : StatusEnum::tryFrom($this->status)?->color();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


}
