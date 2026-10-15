<?php

namespace App\Filament\Admin\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Select::make('rating')
                    ->options(array_combine(range(1, 5), range(1, 5)))
                    ->required(),
                TextInput::make('title'),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->label('Approved (visible on site)'),
            ]);
    }
}
