<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('category.name')->label('Category'),
                TextEntry::make('sku')->label('SKU'),
                TextEntry::make('price_pence')->label('Price')->formatStateUsing(fn (int $state) => money($state)),
                TextEntry::make('stock_quantity')->label('Stock'),
                TextEntry::make('status')->badge(),
                TextEntry::make('description')->html()->columnSpanFull(),
                RepeatableEntry::make('variants')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('stock_quantity'),
                    ])
                    ->columns(2),
            ]);
    }
}
