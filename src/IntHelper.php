<?php
namespace Katapoka\Ahgora;

abstract class IntHelper
{
    public static function parseInt($value)
    {
        if (!!preg_match('/\d+/', $value)) {
            return (int) $value;
        }

        return 0;
    }

    public static function parseNullableInt($value)
    {
        if ($value === null) {
            return null;
        }

        return self::parseInt($value);
    }
}
