<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('No. pesanan')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Jumlah item')
                    ->counts('items'),
                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn (?string $state): string => $state === 'cash' ? 'Tunai' : (string) $state)
                    ->badge(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                        'refunded' => 'Dikembalikan',
                        default => 'Menunggu pembayaran',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed', 'refunded' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu pembelian')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('details')
                    ->label('Detail')
                    ->url(fn (Order $record): string => URL::temporarySignedRoute(
                        'orders.show',
                        now()->addHours(12),
                        ['order' => $record],
                    ))
                    ->openUrlInNewTab(),
                Action::make('markPaid')
                    ->label('Tandai lunas')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->payment_method === 'cash'
                        && $record->status === 'pending')
                    ->action(function (Order $record): void {
                        $markedAsPaid = DB::transaction(function () use ($record): bool {
                            $order = Order::query()->lockForUpdate()->findOrFail($record->id);

                            if ($order->status !== 'pending' || $order->payment_method !== 'cash') {
                                return false;
                            }

                            $order->update(['status' => 'paid']);

                            return true;
                        });

                        $notification = Notification::make()
                            ->title($markedAsPaid
                                ? 'Pembayaran tunai ditandai lunas.'
                                : 'Status pesanan sudah berubah. Muat ulang daftar pesanan.');

                        if ($markedAsPaid) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    }),
            ]);
    }
}
