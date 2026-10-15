<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(2)
                    ->components([
                        TextInput::make('order_number')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('pending'),
                        TextInput::make('email')
                            ->email()
                            ->required(),
                        TextInput::make('phone'),
                    ]),

                Section::make('Totals (£)')
                    ->columns(4)
                    ->components([
                        TextInput::make('subtotal_pence')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('discount_pence')
                            ->label('Discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('shipping_pence')
                            ->label('Shipping')
                            ->numeric()
                            ->default(0)
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('total_pence')
                            ->label('Total')
                            ->numeric()
                            ->required()
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                    ]),

                Section::make('Shipping address')
                    ->columns(2)
                    ->components([
                        TextInput::make('shipping_address.full_name')->label('Full name')->required(),
                        TextInput::make('shipping_address.phone')->label('Phone'),
                        TextInput::make('shipping_address.line1')->label('Address line 1')->required()->columnSpan(2),
                        TextInput::make('shipping_address.line2')->label('Address line 2')->columnSpan(2),
                        TextInput::make('shipping_address.city')->label('City')->required(),
                        TextInput::make('shipping_address.postcode')->label('Postcode')->required(),
                        TextInput::make('shipping_address.country')->label('Country')->default('GB')->required(),
                    ]),

                Section::make('Items')
                    ->components([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                TextInput::make('product_name')->required(),
                                TextInput::make('sku'),
                                TextInput::make('unit_price_pence')
                                    ->label('Unit price (£)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('£')
                                    ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                                TextInput::make('quantity')->numeric()->required()->default(1),
                                TextInput::make('line_total_pence')
                                    ->label('Line total (£)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('£')
                                    ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Add item'),
                    ]),

                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
