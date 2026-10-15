<?php

namespace App\Filament\Admin\Resources\Brands\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('logo')->disk('public'),
                TextEntry::make('name'),
                TextEntry::make('description'),
            ]);
    }
}
