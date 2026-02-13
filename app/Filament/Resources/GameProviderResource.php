<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameProviderResource\Pages;
use App\Models\GameProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameProviderResource extends Resource
{
    protected static ?string $model = GameProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'Games';
    protected static ?string $navigationLabel = 'Providers';
    protected static ?string $modelLabel = 'Provider';
    protected static ?string $pluralModelLabel = 'Providers';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Provider')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->helperText('Ex: worldslot, fivers, venix, games2api...')
                            ->required()
                            ->maxLength(50)
                            ->regex('/^[a-z0-9_]+$/')
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => filled($record)) // não deixa mudar depois de criado
                            ->dehydrated(), // salva no create

                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Enabled')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('base_url')
                            ->label('Base URL')
                            ->placeholder('https://api.exemplo.com')
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Credentials (JSON)')
                    ->description('Atenção: não cole secrets em logs/prints. Se possível, use variáveis de ambiente em produção.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // Se você tiver o package creagia/filament-code-field (você tem), use o CodeField.
                        // Caso dê erro, troque por Textarea.
                        \Creagia\FilamentCodeField\CodeField::make('credentials_json')
                            ->label('credentials_json')
                            ->language('json')
                            ->helperText('JSON com as chaves do provedor. Ex: {"api_key":"...","secret":"..."}')
                            ->columnSpanFull()
                            ->rows(14),

                        // Alternativa (se o CodeField não funcionar):
                        // Forms\Components\Textarea::make('credentials_json')
                        //     ->label('credentials_json')
                        //     ->helperText('JSON com as chaves do provedor. Ex: {"api_key":"...","secret":"..."}')
                        //     ->columnSpanFull()
                        //     ->rows(14),
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
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
    }

    /**
     * Segurança extra: se tiver Policy, Filament respeita automaticamente.
     * Mesmo assim, deixo um "hard stop" aqui.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        // Se você usa Spatie Roles:
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        // Se você usa permissões:
        if (method_exists($user, 'can') && $user->can('manage providers')) {
            return true;
        }

        return false;
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
