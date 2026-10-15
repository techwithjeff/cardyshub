<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('image')->disk('public'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('description')->html(),
                TextEntry::make('sort_order'),
            ]);
    }
}
