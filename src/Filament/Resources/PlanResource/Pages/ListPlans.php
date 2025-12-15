<?php

namespace Mhmadahmd\Filasaas\Filament\Resources\PlanResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Mhmadahmd\Filasaas\Filament\Resources\PlanResource;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    // create plan button
    public function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
