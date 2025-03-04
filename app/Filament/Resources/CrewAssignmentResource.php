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
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class CrewAssignmentResource extends Resource
{
    protected static ?string $model = CrewAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Penugasan Crew';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Penugasan')
                ->schema([
                    Forms\Components\Select::make('booking_id')
                        ->relationship('booking', 'id')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('crew_id')
                        ->relationship('crew', 'name', fn($query) =>
                        $query->where('role', 'crew'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'assigned' => 'Ditugaskan',
                            'on_duty' => 'Sedang Bertugas',
                            'completed' => 'Selesai',
                        ])
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        $columns = match ($user->role) {
            'admin' => self::getAdminColumns(),
            'crew' => self::getCrewColumns(),
            'customer' => self::getCustomerColumns(),
            default => self::getDefaultColumns(),
        };

        return $table
            ->columns($columns)
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'assigned' => 'Ditugaskan',
                        'on_duty' => 'Sedang Bertugas',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                ...($user->role === 'admin' ? [
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ] : []),
            ]);
    }

    protected static function getAdminColumns(): array
    {
        return [
            Split::make([
                Stack::make([
                    Tables\Columns\TextColumn::make('booking.id')
                        ->label('Booking ID')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('crew.name')
                        ->label('Nama Crew')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'assigned' => 'warning',
                            'on_duty' => 'info',
                            'completed' => 'success',
                            default => 'gray',
                        }),
                ]),
                Stack::make([
                    Tables\Columns\TextColumn::make('booking.customer.name')
                        ->label('Customer'),
                    Tables\Columns\TextColumn::make('booking.customer.phone')
                        ->label('No. Telp Customer'),
                    Tables\Columns\TextColumn::make('crew.phone')
                        ->label('No. Telp Crew'),
                ]),
            ]),
        ];
    }

    protected static function getCrewColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('booking.bus.name')
                    ->label('Bus')
                    ->formatStateUsing(fn($state, $record) =>
                    "{$state} ({$record->booking->bus->number_plate})")
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'assigned' => 'warning',
                        'on_duty' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('booking.booking_date')
                    ->label('Tanggal Keberangkatan')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('booking.return_date')
                    ->label('Tanggal Kembali')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('')
                    ->label('Rute')
                    ->formatStateUsing(fn($state, $record) =>
                    "{$record->booking->pickup_location} → {$record->booking->destination}"),
                Stack::make([
                    Tables\Columns\TextColumn::make('booking.customer.name')
                        ->label('Customer'),
                    Tables\Columns\TextColumn::make('booking.customer.phone')
                        ->label('Kontak'),
                    Tables\Columns\TextColumn::make('notes')
                        ->label('Catatan')
                ])->space(1),
            ])->space(3),
        ];
    }

    protected static function getCustomerColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('crew.name')
                    ->label('Nama Crew')
                    ->formatStateUsing(fn($state) => "Crew: {$state}")
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('crew.phone')
                    ->label('Kontak Crew')
                    ->formatStateUsing(fn($state) => "No. Telp: {$state}"),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status Crew')
                    ->color(fn(string $state): string => match ($state) {
                        'assigned' => 'warning',
                        'on_duty' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])->space(2),
        ];
    }

    protected static function getDefaultColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id'),
        ];
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
