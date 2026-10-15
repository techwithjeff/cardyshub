<?php

namespace App\Filament\Admin\Resources\ContactSubmissions\Pages;

use App\Filament\Admin\Resources\ContactSubmissions\ContactSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactSubmissions extends ListRecords
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
