<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
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
                RichEditor::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('categories')
                    ->visibility('public'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
