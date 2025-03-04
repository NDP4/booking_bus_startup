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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Section;
use App\Forms\Components\BusCard;
use Filament\Support\Facades\FilamentView;
use App\Forms\Components\BusGrid;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Booking Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form->schema([
            Forms\Components\Grid::make()->schema([
                Forms\Components\Section::make('Pilih Bus')
                    ->description('Pilih bus yang ingin anda sewa')
                    ->columnSpanFull()
                    ->schema([
                        BusGrid::make('bus_grid')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('bus_id')
                            ->relationship('bus', 'name', fn($query) =>
                            $query->where('status', 'available'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->preload()
                            ->label('Pilih Bus')
                            ->afterStateUpdated(fn($state, $get, $set) =>
                            static::calculateTotalFromState($get, $set))
                    ]),

                Forms\Components\Section::make('Detail Booking')
                    ->columns(2)
                    ->schema([
                        $user->role === 'admin' ?
                            Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            : Hidden::make('customer_id')
                            ->default($user->id),

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
                        Forms\Components\Section::make('Total Biaya')
                            ->schema([
                                Forms\Components\TextInput::make('total_amount')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->label('Total Biaya')
                                    ->extraAttributes(['class' => 'text-2xl font-bold']),
                            ])
                            ->columnSpanFull(),
                        $user->role === 'admin' ?
                            Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->disabled()
                            ->default('pending')
                            : Hidden::make('status')
                            ->default('pending'),

                        Hidden::make('payment_status')
                            ->default('pending'),

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
                    ])->columnSpanFull(),
            ])
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
                Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription('Anda akan diarahkan ke halaman pembayaran dalam tab baru.')
                    ->action(function (Booking $record) {
                        // Create payment token if not exists
                        if (empty($record->payment_token)) {
                            $record->createMidtransPayment();
                            $record->refresh();
                        }

                        // Return URL to be opened in new tab
                        return redirect()->route('payment.checkout', $record);
                    })
                    ->visible(fn(Booking $record): bool =>
                    $record->getAttribute('payment_status') === 'pending'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role === 'customer') {
            $query->where('customer_id', Auth::id());
        }

        return $query;
    }

    public static function afterCreate(Booking $record): void
    {
        try {
            if ($record->createMidtransPayment()) {
                // Redirect to payment page
                $url = route('payment.checkout', $record);

                echo "
                    <script>
                        window.open('{$url}', '_blank');
                    </script>
                ";

                Notification::make()
                    ->success()
                    ->title('Booking berhasil dibuat')
                    ->body('Halaman pembayaran telah dibuka di tab baru.')
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('pay')
                            ->label('Buka Pembayaran')
                            ->url($url)
                            ->openUrlInNewTab(),
                    ])
                    ->send();
            } else {
                throw new \Exception('Gagal membuat token pembayaran');
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error creating payment')
                ->body($e->getMessage())
                ->send();
        }
    }
}
