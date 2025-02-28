<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

use AbdullahMateen\LaravelHelpingMaterial\Enums\StatusEnum;
use AbdullahMateen\LaravelHelpingMaterial\Enums\User\AccountStatusEnum;
use Illuminate\Support\Facades\Request;

/**
 * @method  columns
 * @method  auth
 * @method  byUser
 * @method  byLevel
 * @method  whereDateBetween
 * @method  search
 * @method  active
 * @method  inactive
 * @method  blocked
 */
trait ScopeTrait
{
    /**
     * @param $query
     * @param $columns
     * @param $overwrite
     *
     * @return mixed
     */
    public function scopeColumns($query, $columns = [], $overwrite = false)
    {
        $default = ['id', 'name'];
        $columns = is_array($columns) ? $columns : explode(',', $columns);
        $columns = $overwrite ? $columns : array_merge_recursive($default, $columns);
        return $query->select($columns);
    }

    /**
     * @param $query
     * @param $columnName
     * @param $authId
     *
     * @return mixed
     */
    public function scopeAuth($query, $columnName = 'user_id', $authId = null)
    {
        if (!auth_check()) return $query;
        return $query->byUser($columnName, $authId);
    }

    /**
     * @param $query
     * @param $columnName
     * @param $authId
     *
     * @return mixed
     */
    public function scopeByUser($query, $columnName = 'user_id', $userId = null)
    {
        $authId = $userId ?? auth_id();
        return $query->where($columnName, '=', $authId);
    }

    /**
     * @param $query
     * @param $levels
     *
     * @return mixed
     */
    public function scopeByLevel($query, $levels = null)
    {
        $levels = is_array($levels) ? $levels : explode(',', $levels);
        return $query->whereIn('level', $levels);
    }

    /**
     * @param                   $query
     * @param string            $column
     * @param string|array|null $fromDate
     * @param string|null       $toDate
     *
     * @return mixed
     */
    public function scopeWhereDateBetween($query, string $column, string|array|null $fromDate, string|null $toDate = null)
    {
        $dateRange = is_array($fromDate) ? array_values($fromDate) : [$fromDate, $toDate];

        //        $dateRange = array_filter($dateRange);
        //        if (count($dateRange) < 1) return $query;

        $start = $dateRange[0] ?? $dateRange[1] ?? null;
        $end   = $dateRange[1] ?? $dateRange[0] ?? null;
        return $query->whereDate($column, '>=', $start)->whereDate($column, '<=', $end);
    }

    /**
     * @param       $query
     * @param array $data
     *
     * @return mixed
     */
    public function scopeSearch($query, array $data = [])
    {
        $data = match (true) {
            $data instanceof Request => $data->all(),
            blank($data)             => request()->all(),
            default                  => $data,
        };

        return $query;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeActive($query)
    {
        return match ($this->casts['status']) {
            StatusEnum::class        => $query->where(get_model_table(get_called_class()) . '.status', '=', StatusEnum::Active),
            AccountStatusEnum::class => $query->where(get_model_table(get_called_class()) . '.status', '=', AccountStatusEnum::Active),
            default                  => $query->where(get_model_table(get_called_class()) . '.status', '=', 1)
        };
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeInActive($query)
    {
        return match ($this->casts['status']) {
            StatusEnum::class        => $query->where(get_model_table(get_called_class()) . '.status', '=', StatusEnum::Inactive),
            AccountStatusEnum::class => $query->where(get_model_table(get_called_class()) . '.status', '=', AccountStatusEnum::Inactive),
            default                  => $query->where(get_model_table(get_called_class()) . '.status', '=', 0)
        };
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeBlocked($query)
    {
        return $query->where(get_model_table(get_called_class()) . '.status', '=', AccountStatusEnum::Blocked);
    }
}
