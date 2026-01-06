<?php

namespace App\Filament\Resources\MentorshipRelationResource\Pages;

use App\Filament\Resources\MentorshipRelationResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListMentorshipRelations extends ListRecords
{
    protected static string $resource = MentorshipRelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle relation de mentorat')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toutes')
                ->badge(fn () => \App\Models\MentorshipRelation::count()),
                
            'active' => Tab::make('Actives')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => \App\Models\MentorshipRelation::where('status', 'active')->count())
                ->badgeColor('success'),
                
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\MentorshipRelation::where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'completed' => Tab::make('Terminées')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\MentorshipRelation::where('status', 'completed')->count())
                ->badgeColor('secondary'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
