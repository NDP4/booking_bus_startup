<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrewAssignmentResource\Pages;
use App\Models\CrewAssignment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CrewAssignmentResource extends Resource
{
    protected static ?string $model = CrewAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Booking Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('crew_id')
                        ->label('Crew Member')
                        ->options(User::where('role', 'crew')->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('booking_id')
                        ->relationship('booking', 'id')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(
                            fn($record) =>
                            "Booking #{$record->id} - {$record->customer->name} - {$record->booking_date->format('d M Y')}"
                        ),
                    Forms\Components\Select::make('status')
                        ->options([
                            'assigned' => 'Assigned',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default('assigned'),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('crew.name')
                    ->label('Crew Member')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking.booking_date')
                    ->label('Booking Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking.bus.name')
                    ->label('Bus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'assigned' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'assigned' => 'Assigned',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('crew_id')
                    ->label('Crew Member')
                    ->options(User::where('role', 'crew')->pluck('name', 'id')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrewAssignments::route('/'),
            'create' => Pages\CreateCrewAssignment::route('/create'),
            'edit' => Pages\EditCrewAssignment::route('/{record}/edit'),
        ];
    }
}
