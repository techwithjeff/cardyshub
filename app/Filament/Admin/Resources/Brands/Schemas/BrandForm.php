<?php

namespace App\Filament\Admin\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                FileUpload::make('logo')
                    ->image()
                    ->disk('public')
                    ->directory('brands')
                    ->visibility('public'),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_featured')
                    ->label('Featured on Brands page'),
            ]);
    }
}
