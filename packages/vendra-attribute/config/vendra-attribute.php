<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Filament panels that the attribute administration
    | UI is registered on. The attribute navigation only appears within the
    | panels listed here. You may provide a single panel ID or an array of IDs
    | to mount the module across multiple panels.
    |
    | Supported: "admin" (string), ["admin", "vendor"] (array)
    |
    */

    'panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | This value determines the sidebar navigation group that the Attributes
    | cluster is nested under. You may provide a translation key or a literal
    | label, allowing you to place the attribute UI alongside a host
    | application's own groups. When empty, the module default is used.
    |
    */

    'navigation_group' => 'vendra-support::navigation.groups.Catalog',

    /*
    |--------------------------------------------------------------------------
    | Attribute Units
    |--------------------------------------------------------------------------
    |
    | These options are available when defining an attribute. The array key is
    | the value stored with the attribute definition, and the array value is
    | the label shown in the Filament form. Applications may publish this
    | configuration and replace or extend the list for their domain.
    |
    */

    'units' => [
        'kg' => 'Kilogram (kg)',
        'g' => 'Gram (g)',
        'm' => 'Meter (m)',
        'cm' => 'Centimeter (cm)',
        'l' => 'Liter (l)',
        'ml' => 'Milliliter (ml)',
        'item' => 'Item',
        'piece' => 'Piece',
        'pack' => 'Pack',
        'month' => 'Month',
        'year' => 'Year',
    ],

];
