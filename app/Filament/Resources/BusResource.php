<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusResource\Pages;
use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Transportation';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('number_plate')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('default_seat_capacity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'available' => 'Available',
                        'maintenance' => 'Maintenance',
                        'booked' => 'Booked',
                    ])
                    ->default('available'),
                Forms\Components\FileUpload::make('images')
                    ->image()
                    ->multiple()
                    ->maxFiles(5)
                    ->directory('buses')
                    ->columnSpanFull(),
                Forms\Components\Select::make('pricing_type')
                    ->required()
                    ->options([
                        'daily' => 'Per Hari',
                        'distance' => 'Per Kilometer',
                    ])
                    ->default('daily')
                    ->reactive(),
                Forms\Components\TextInput::make('price_per_day')
                    ->required(fn($get) => $get('pricing_type') === 'daily')
                    ->visible(fn($get) => $get('pricing_type') === 'daily')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Forms\Components\TextInput::make('price_per_km')
                    ->required(fn($get) => $get('pricing_type') === 'distance')
                    ->visible(fn($get) => $get('pricing_type') === 'distance')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Forms\Components\TextInput::make('legrest_price_per_seat')
                    ->label('Harga Tambahan per Kursi Legrest')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->required()
                    ->hint('Harga tambahan untuk setiap kursi legrest'),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_plate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_seat_capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'maintenance' => 'warning',
                        'booked' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('images')
                    ->circular()
                    ->stacked(),
                Tables\Columns\TextColumn::make('pricing_type')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'daily' => 'Per Hari',
                        'distance' => 'Per Kilometer',
                        default => 'Tidak Diatur',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'daily' => 'info',
                        'distance' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->money('idr')
                    ->visible(fn($record): bool => $record?->pricing_type === 'daily'),
                Tables\Columns\TextColumn::make('price_per_km')
                    ->money('idr')
                    ->visible(fn($record): bool => $record?->pricing_type === 'distance'),
                Tables\Columns\TextColumn::make('legrest_price_per_seat')
                    ->label('Harga Legrest')
                    ->money('idr')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'maintenance' => 'Maintenance',
                        'booked' => 'Booked',
                    ]),
                Tables\Filters\SelectFilter::make('pricing_type')
                    ->options([
                        'daily' => 'Per Hari',
                        'distance' => 'Per Kilometer',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }
}
