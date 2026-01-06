<?php

namespace App\Filament\Resources\MentorshipRelationResource\Pages;

use App\Filament\Resources\MentorshipRelationResource;
use App\Models\Goal;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;

class ViewMentorshipRelation extends ViewRecord
{
    protected static string $resource = MentorshipRelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action pour créer un objectif
            Actions\Action::make('create_goal')
                ->label('Créer un objectif')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->form([
                    Forms\Components\Section::make('Informations de l\'objectif')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Ex: Maîtriser Laravel Eloquent'),
                                
                            Forms\Components\Select::make('priority')
                                ->label('Priorité')
                                ->options([
                                    'low' => 'Faible',
                                    'medium' => 'Moyenne',
                                    'high' => 'Haute',
                                ])
                                ->required()
                                ->default('medium'),
                                
                            Forms\Components\Select::make('status')
                                ->label('Statut')
                                ->options([
                                    'not_started' => 'Non commencé',
                                    'in_progress' => 'En cours',
                                    'completed' => 'Terminé',
                                    'cancelled' => 'Annulé',
                                ])
                                ->required()
                                ->default('not_started'),
                        ])->columns(3),
                        
                    Forms\Components\Section::make('Détails')
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->label('Description')
                                ->rows(3)
                                ->placeholder('Décrivez l\'objectif en détail...')
                                ->columnSpanFull(),
                                
                            Forms\Components\DatePicker::make('target_date')
                                ->label('Date cible')
                                ->native(false)
                                ->minDate(now())
                                ->placeholder('Date limite pour atteindre cet objectif'),
                                
                            Forms\Components\TextInput::make('progress')
                                ->label('Progression (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(0)
                                ->suffix('%'),
                        ])->columns(2),
                        
                    Forms\Components\Section::make('Notes additionnelles')
                        ->schema([
                            Forms\Components\Textarea::make('notes')
                                ->label('Notes')
                                ->rows(3)
                                ->placeholder('Notes ou commentaires...')
                                ->columnSpanFull(),
                        ]),
                ])
                ->action(function (array $data) {
                    Goal::create([
                        'mentorship_relation_id' => $this->record->id,
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'priority' => $data['priority'],
                        'status' => $data['status'],
                        'target_date' => $data['target_date'] ?? null,
                        'progress' => $data['progress'] ?? 0,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Objectif créé avec succès')
                        ->success()
                        ->send();
                }),

            // Action pour créer une session
            Actions\Action::make('create_section')
                ->label('Planifier une session')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->form([
                    Forms\Components\Section::make('Informations de la session')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Ex: Session de révision Laravel')
                                ->columnSpanFull(),
                                
                            Forms\Components\Select::make('type')
                                ->label('Type')
                                ->options([
                                    'mentorat' => 'Mentorat',
                                    'coaching' => 'Coaching',
                                ])
                                ->required()
                                ->default('mentorat'),
                                
                            Forms\Components\Select::make('status')
                                ->label('Statut')
                                ->options([
                                    'scheduled' => 'Planifié',
                                    'completed' => 'Terminé',
                                    'cancelled' => 'Annulé',
                                    'rescheduled' => 'Reporté',
                                ])
                                ->required()
                                ->default('scheduled'),
                        ])->columns(2),
                        
                    Forms\Components\Section::make('Date et durée')
                        ->schema([
                            Forms\Components\DateTimePicker::make('scheduled_at')
                                ->label('Date et heure')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->minDate(now())
                                ->default(now()->addDay()->setTime(14, 0)),
                                
                            Forms\Components\TextInput::make('duration_minutes')
                                ->label('Durée (minutes)')
                                ->numeric()
                                ->required()
                                ->default(60)
                                ->minValue(15)
                                ->maxValue(480)
                                ->suffix('min')
                                ->helperText('Entre 15 minutes et 8 heures'),
                                
                            Forms\Components\TextInput::make('link')
                                ->label('Lien')
                                ->maxLength(255)
                                ->placeholder('https://meet.google.com/xxx')
                                ->helperText('Lien de visioconférence'),
                        ])->columns(3),
                        
                    Forms\Components\Section::make('Description et ordre du jour')
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->label('Description')
                                ->rows(3)
                                ->placeholder('Description de la session...')
                                ->columnSpanFull(),
                                
                            Forms\Components\Textarea::make('agenda')
                                ->label('Ordre du jour')
                                ->rows(3)
                                ->placeholder('Points à aborder durant la session...')
                                ->columnSpanFull()
                                ->helperText('Liste des sujets qui seront traités'),
                        ]),
                ])
                ->action(function (array $data) {
                    Section::create([
                        'mentorship_relation_id' => $this->record->id,
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'type' => $data['type'],
                        'scheduled_at' => $data['scheduled_at'],
                        'duration_minutes' => $data['duration_minutes'],
                        'link' => $data['location'] ?? null,
                        'status' => $data['status'],
                        'agenda' => $data['agenda'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Session planifiée avec succès')
                        ->success()
                        ->send();
                }),

            // Action pour éditer la relation
            Actions\EditAction::make(),
            
            // Action pour supprimer la relation
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}