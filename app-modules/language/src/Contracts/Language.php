<?php

declare(strict_types=1);

namespace Termehsoft\Language\Contract;

use Termehsoft\Language\Models\Language as ModelsLanguage;

abstract class Language
{
    abstract public function get(): ModelsLanguage;
}
