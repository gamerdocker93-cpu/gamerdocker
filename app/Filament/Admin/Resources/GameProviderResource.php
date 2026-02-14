<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GameProviderResource\Pages;
use App\Models\GameProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Creagia\FilamentCodeField\CodeField;

class GameProviderResource extends Resource
{
    protected static ?string $model = GameProvider::class;

    // URL: /admin/game-providers
    protected static ?string $slug = 'game-providers';

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'Meus Jogos';
    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return 'Provedores';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Provider')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Code (ex: worldslot)')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->helperText('Identificador único usado nos comandos (games:sync {code})'),

                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\Toggle::make('enabled')
                        ->label('Habilitado')
                        ->default(false),

                    Forms\Components\TextInput::make('base_url')
                        ->label('Base URL')
                        ->placeholder('https://api.exemplo.com')
                        ->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make('Credenciais')
                ->description('Salvo criptografado no banco. Edite como JSON.')
                ->schema([
                    CodeField::make('credentials_json')
                        ->label('credentials_json (JSON)')
                        ->language('json')
                        ->height('240px')
                        ->helperText('Ex.: {"token":"...","secret":"..."}')
                        ->formatStateUsing(fn ($state) => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        ->dehydrateStateUsing(function ($state) {
                            if (is_array($state)) {
                                return $state;
                            }
                            if (is_string($state)) {
                                $decoded = json_decode($state, true);
                                return is_array($decoded) ? $decoded : [];
                            }
                            return [];
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_url')
                    ->label('Base URL')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGameProviders::route('/'),
            'create' => Pages\CreateGameProvider::route('/create'),
            'edit'   => Pages\EditGameProvider::route('/{record}/edit'),
        ];
    }
}