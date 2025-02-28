<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum;

use ReflectionClass;

trait GeneralTrait
{
    public function __call(string $name, array $arguments)
    {
        if ($name === 'key') {
            return $this->name;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function asArray(): array
    {
        $oClass    = new ReflectionClass(__CLASS__);
        $constants = $oClass->getReflectionConstants(1);
        $constants = collect($constants)->filter(fn ($c) => $c->class === $oClass->getName());

        $array = [];
        foreach ($constants as $constant) {
            $array[$constant->name] = $constant->getValue();
        }
        return $array;
    }

    //    public static function cases($function = 'asArray'): array
    //    {
    //        $cases = [];
    //        foreach (self::$function() as $key => $value) $cases[] = self::fromKey($key);
    //        return $cases;
    //    }

    /**
     * @param mixed  $value
     * @param string $type name, value
     * @param string $function
     *
     * @return bool
     */
    public static function exists(mixed $value, string $type = 'value', string $function = 'cases'): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_array($value)) {
            $cases = self::toArray(false, $function);
            if (!count(array_diff($value, $cases))) {
                return true;
            }
        } else {
            foreach (self::$function() as $item) {
                if ($item->$type == $value) return true;
            }
        }

        return false;
    }

    /**
     * @param string $type name, value, null
     * @param string $function
     *
     * @return mixed
     */
    public static function random(string|null $type = 'value', string $function = 'cases'): mixed
    {
        $enum = self::$function()[array_rand(self::$function())];
        return is_null($type) ? $enum : $enum->$type;
    }

    public static function toArray(bool $useToStringValue = false, $function = 'cases'): array
    {
        $array = [];
        foreach (self::$function() as $item) {
            $array[$useToStringValue ? $item->toString() : $item->name] = $item->value;
        }
        return $array;
    }

    /**
     * @param string $function
     *
     * @return array
     */
    public static function toFullArray(string $function = 'cases'): array
    {
        $fns   = [];
        $array = [];

        if (method_exists(self::class, 'toFullArrayInclude')) {
            $fns = self::toFullArrayInclude() ?? [];
        }

        foreach (self::$function() as $item) {
            foreach ($fns as $fn) {
                $extra[$fn] = $item->$fn();
            }

            $array[$item->value] = array_merge([
                'name'      => $item->name,
                'value'     => $item->value,
                'string'    => $item->toString(),
                'color'     => $item->color(),
                'colorCode' => $item->colorCode(),
            ], $extra ?? []);
        }
        return $array;
    }

    /**
     * @param string $column
     * @param string $function
     * @param bool   $toString
     *
     * @return array|string
     */
    public static function column(string $column = 'value', string $function = 'cases', bool $toString = false): array|string
    {
        $array = array_column(self::toFullArray($function), $column);
        return $toString ? implode(',', $array) : $array;
    }

    /**
     * @return array
     */
    public static function toFullArrayInclude(): array { return []; }

    /**
     * @param mixed  $value
     * @param string $type name, value
     * @param bool   $strict
     *
     * @return bool
     */
    public function equalsTo(mixed $value, string $type = 'value', bool $strict = false): bool
    {
        return $strict ? $this->$type === $value : $this->$type == $value;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return [
            'value'     => $this->value,
            'name'      => $this->toString(),
            'color'     => $this->color(),
            'colorCode' => $this->colorCode(),
        ];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return self::PRIMARY_CLASS;
    }

    /**
     * @return string
     */
    public function colorCode(): string
    {
        $value     = strtoupper($this->color());
        $colorCode = (
            str_starts_with($value, '#') ||
            str_starts_with($value, 'RGB(') ||
            str_starts_with($value, 'RGBA(')
        ) ? $value : constant("self::$value");
        return strtolower($colorCode);
    }
}
