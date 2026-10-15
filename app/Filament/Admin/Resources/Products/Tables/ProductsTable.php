<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.path')
                    ->label('Image')
                    ->disk('public')
                    ->limit(1),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('price_pence')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state) => money($state))
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
