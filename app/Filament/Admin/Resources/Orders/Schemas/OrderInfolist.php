<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(3)
                    ->components([
                        TextEntry::make('order_number')->label('Order #'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->dateTime('d M Y H:i'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('total_pence')->label('Total')->formatStateUsing(fn (int $state) => money($state)),
                    ]),
                Section::make('Shipping address')
                    ->columns(2)
                    ->components([
                        TextEntry::make('shipping_address.full_name')->label('Full name'),
                        TextEntry::make('shipping_address.phone')->label('Phone'),
                        TextEntry::make('shipping_address.line1')->label('Address line 1'),
                        TextEntry::make('shipping_address.line2')->label('Address line 2'),
                        TextEntry::make('shipping_address.city')->label('City'),
                        TextEntry::make('shipping_address.postcode')->label('Postcode'),
                        TextEntry::make('shipping_address.country')->label('Country'),
                    ]),
                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_name')->label('Product'),
                                TextEntry::make('quantity'),
                                TextEntry::make('unit_price_pence')->label('Unit price')->formatStateUsing(fn (int $state) => money($state)),
                                TextEntry::make('line_total_pence')->label('Line total')->formatStateUsing(fn (int $state) => money($state)),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
