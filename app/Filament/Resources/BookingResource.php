<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\Component;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Booking Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('bus_id')
                        ->relationship('bus', 'name')
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, $get, $set) {
                            if (!$state) return;

                            $bus = Bus::find($state);
                            if (!$bus) return;

                            $days = 1;
                            if (!empty($get('return_date'))) {
                                $start = Carbon::parse($get('booking_date') ?? now());
                                $end = Carbon::parse($get('return_date'));
                                $days = $start->diffInDays($end) + 1;
                            }

                            $distance = 0; // Todo: implement distance calculation

                            $total = $bus->calculateTotalPrice(
                                $get('total_seats') ?? 0,
                                $get('seat_type') ?? 'standard',
                                $days,
                                $distance
                            );

                            $set('total_amount', $total);
                        }),
                    Forms\Components\DateTimePicker::make('booking_date')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn($state, $get, $set) => static::calculateTotalFromState($get, $set)),
                    Forms\Components\DateTimePicker::make('return_date')
                        ->live()
                        ->afterStateUpdated(fn($state, $get, $set) => static::calculateTotalFromState($get, $set)),
                    Forms\Components\TextInput::make('total_seats')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->live()
                        ->afterStateUpdated(fn($state, $get, $set) => static::calculateTotalFromState($get, $set)),
                    Forms\Components\Select::make('seat_type')
                        ->options([
                            'standard' => 'Standard',
                            'legrest' => 'Legrest',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn($state, $get, $set) => static::calculateTotalFromState($get, $set)),
                    Forms\Components\TextInput::make('pickup_location')
                        ->required()
                        ->label('Lokasi Penjemputan'),
                    Forms\Components\TextInput::make('destination')
                        ->required()
                        ->label('Tujuan'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('total_amount')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp')
                        ->numeric()
                        ->label('Total Biaya'),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('payment_token')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('special_requests')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('calculate')
                            ->label('Hitung Total')
                            ->action(function ($livewire) {
                                $formData = $livewire->form->getRawState();

                                $bus = Bus::find($formData['bus_id']);
                                if (!$bus) return;

                                $days = 1;
                                if (!empty($formData['return_date'])) {
                                    $start = Carbon::parse($formData['booking_date']);
                                    $end = Carbon::parse($formData['return_date']);
                                    $days = $start->diffInDays($end) + 1;
                                }

                                $distance = 0; // Todo: implement distance calculation

                                $total = $bus->calculateTotalPrice(
                                    $formData['total_seats'],
                                    $formData['seat_type'],
                                    $days,
                                    $distance
                                );

                                $livewire->form->fill(['total_amount' => $total]);
                            }),
                    ])->columnSpanFull(),
                ])->columns(2)
        ])
            ->extraAttributes([
                'class' => 'space-y-6',
            ]);
    }

    protected static function calculateTotalFromState($get, $set): void
    {
        $bus = Bus::find($get('bus_id'));
        if (!$bus) return;

        $days = 1;
        if (!empty($get('return_date'))) {
            $start = Carbon::parse($get('booking_date') ?? now());
            $end = Carbon::parse($get('return_date'));
            $days = $start->diffInDays($end) + 1;
        }

        $distance = 0; // Todo: implement distance calculation

        $total = $bus->calculateTotalPrice(
            $get('total_seats') ?? 0,
            $get('seat_type') ?? 'standard',
            $days,
            $distance
        );

        $set('total_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bus.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_seats')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('idr')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
