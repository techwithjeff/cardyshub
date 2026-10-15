<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Published products that are out of stock or nearly there.
 *
 * Scoped to published products on purpose: a draft running low is not a
 * problem, and listing drafts here would bury the ones customers can actually
 * try to buy.
 */
class StockAlerts extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    /** Anything at or below this is worth restocking. */
    protected const LOW_STOCK_AT = 5;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Stock to watch')
            ->description('Published products that are out of stock or running low.')
            ->query(fn () => Product::query()
                ->where('status', 'published')
                ->where('stock_quantity', '<=', self::LOW_STOCK_AT)
                // Zero first: those are actively losing you sales.
                ->orderBy('stock_quantity')
                ->orderBy('name')
                ->limit(10))
            ->paginated(false)
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->limit(44)
                    ->weight('medium'),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state <= 0 ? 'Out of stock' : (string) $state)
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : 'warning')
                    ->alignEnd(),

                TextColumn::make('price_pence')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => money($state))
                    ->alignEnd()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('restock')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Everything is in stock')
            ->emptyStateDescription('Products drop into this list once they fall to '.self::LOW_STOCK_AT.' or fewer.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
