<?php

namespace App\Filament\Resources\MentorshipRelationResource\Pages;

use App\Filament\Resources\MentorshipRelationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMentorshipRelation extends CreateRecord
{
    protected static string $resource = MentorshipRelationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
