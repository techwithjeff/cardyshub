<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The last ten orders, newest first — the thing you actually open the admin for.
 */
class RecentOrders extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent orders')
            ->query(fn () => Order::query()->latest('created_at')->limit(10))
            ->paginated(false)
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight('medium')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Customer')
                    ->limit(28)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    // Same mapping as the Orders table, so a status never means
                    // two different things in two places.
                    ->color(fn (string $state) => match ($state) {
                        'paid', 'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('shipping_method_name')
                    ->label('Delivery')
                    ->placeholder('—')
                    ->limit(24)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_pence')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => money($state))
                    ->alignEnd(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->tooltip(fn (Order $record): string => $record->created_at->format('d M Y, H:i'))
                    ->alignEnd(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders appear here the moment a customer checks out.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
