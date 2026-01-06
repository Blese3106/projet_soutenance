<?php

namespace App\Filament\Resources\MentorshipRelationResource\Pages;

use App\Filament\Resources\MentorshipRelationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMentorshipRelation extends EditRecord
{
    protected static string $resource = MentorshipRelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
