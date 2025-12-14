<?php

namespace Mhmadahmd\Filasaas\Traits;

use Spatie\Sluggable\HasSlug as SpatieHasSlug;
use Spatie\Sluggable\SlugOptions;

trait HasSlug
{
    use SpatieHasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                // Handle translatable fields
                if (method_exists($model, 'getTranslations')) {
                    $name = $model->getTranslation('name', app()->getLocale());

                    return $name ?: $model->name;
                }

                return $model->name ?? '';
            })
            ->saveSlugsTo('slug');
    }
}
