<?php

namespace Mhmadahmd\Filasaas\Traits;

use Spatie\Translatable\HasTranslations as SpatieHasTranslations;

trait HasTranslations
{
    use SpatieHasTranslations;

    public $translatable = ['name', 'description'];
}
