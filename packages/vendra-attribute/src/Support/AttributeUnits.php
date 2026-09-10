<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Support;

use Illuminate\Support\Facades\Config;

final class AttributeUnits
{
    /** @return array<string, string> */
    public static function options(): array
    {
        $units = [];

        foreach (Config::array('vendra-attribute.units') as $value => $label) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $units[$value] = is_string($label) && $label !== '' ? $label : $value;
        }

        return $units;
    }
}
