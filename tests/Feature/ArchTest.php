<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('vendra packages never depend on the host application')
    ->expect('Misaf')
    ->not->toUse('App');

arch('the host app declares strict types everywhere')
    ->expect('App')
    ->toUseStrictTypes();
