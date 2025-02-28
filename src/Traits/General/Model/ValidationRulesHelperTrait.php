<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

trait ValidationRulesHelperTrait
{
    public function ruleUnique($column, $id = null)
    {
        $id = $id ?? $this->id;
        $unique = sprintf('unique:%s,%s', get_model_table($this), $column);
        return is_null($id) ? $unique : "$unique,$id";
    }
}
