<?php

namespace Mhmadahmd\Filasaas\Filament\Resources\FeatureResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Mhmadahmd\Filasaas\Filament\Resources\FeatureResource;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    // create feature button
    public function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
