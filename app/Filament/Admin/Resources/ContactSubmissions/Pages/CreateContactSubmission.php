<?php

namespace App\Filament\Admin\Resources\ContactSubmissions\Pages;

use App\Filament\Admin\Resources\ContactSubmissions\ContactSubmissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactSubmission extends CreateRecord
{
    protected static string $resource = ContactSubmissionResource::class;
}
