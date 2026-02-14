<?php

namespace App\Filament\Admin\Resources\GameProviderResource\Pages;

use App\Filament\Admin\Resources\GameProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameProviders extends ListRecords
{
    protected static string $resource = GameProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}