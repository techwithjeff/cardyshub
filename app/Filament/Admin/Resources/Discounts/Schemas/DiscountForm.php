<?php

namespace App\Filament\Admin\Resources\Discounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : $state)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Select::make('type')
                    ->options([
                        'percent' => 'Percentage off',
                        'fixed' => 'Fixed amount off (£)',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->numeric()
                    ->required()
                    ->suffix(fn (callable $get) => $get('type') === 'percent' ? '%' : 'pence'),
                TextInput::make('max_uses')
                    ->numeric()
                    ->label('Max uses (blank = unlimited)'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
