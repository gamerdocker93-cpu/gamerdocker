<?php

namespace App\Filament\Resources\GameProviderResource\Pages;

use App\Filament\Resources\GameProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGameProvider extends EditRecord
{
    protected static string $resource = GameProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
