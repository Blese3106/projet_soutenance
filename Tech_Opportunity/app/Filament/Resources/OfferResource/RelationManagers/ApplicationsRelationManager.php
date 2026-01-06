<?php

namespace App\Filament\Resources\OfferResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'Candidatures';
    
    protected static ?string $recordTitleAttribute = 'applicant.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du candidat')
                    ->schema([
                        Forms\Components\Placeholder::make('applicant_info')
                            ->label('Candidat')
                            ->content(fn ($record) => $record ? $record->applicant->name . ' (' . $record->applicant->email . ')' : '-'),
                            
                        Forms\Components\Placeholder::make('application_date')
                            ->label('Date de candidature')
                            ->content(fn ($record) => $record ? $record->created_at->format('d/m/Y H:i') : '-'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Lettre de motivation')
                    ->schema([
                        Forms\Components\Textarea::make('cover_letter')
                            ->label('Lettre de motivation')
                            ->rows(5)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && $record->cover_letter),
                    
                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\Placeholder::make('resume')
                            ->label('CV')
                            ->content(fn ($record) => $record && $record->resume_path ? 
                                '<a href="' . asset('storage/' . $record->resume_path) . '" target="_blank" class="text-primary-600 underline">Télécharger le CV</a>' : 
                                'Aucun CV fourni')
                            ->extraAttributes(['class' => 'prose']),
                            
                        Forms\Components\TextInput::make('portfolio_link')
                            ->label('Lien Portfolio')
                            ->url()
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && ($record->resume_path || $record->portfolio_link)),
                    
                Forms\Components\Section::make('Résultats du test')
                    ->schema([
                        Forms\Components\Placeholder::make('test_info')
                            ->label('Statut du test')
                            ->content(fn ($record) => $record ? match($record->status) {
                                'test_in_progress' => '⏳ Test en cours',
                                'test_completed' => '✅ Test terminé',
                                'test_passed' => '✅ Test réussi',
                                'test_failed' => '❌ Test échoué',
                                default => 'Pas encore passé'
                            } : '-'),
                            
                        Forms\Components\Placeholder::make('test_score')
                            ->label('Score')
                            ->content(fn ($record) => $record && $record->test_score ? 
                                $record->test_score . '/' . $record->test_total_points . ' (' . round($record->getTestPercentage(), 2) . '%)' : 
                                '-'),
                                
                        Forms\Components\Placeholder::make('test_duration')
                            ->label('Durée du test')
                            ->content(function ($record) {
                                if (!$record || !$record->test_started_at || !$record->test_completed_at) {
                                    return '-';
                                }

                                $duration = $record->test_started_at->diffInMinutes($record->test_completed_at);

                                return $duration . ' minutes';
                            }),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record && in_array($record->status, [
                        'test_in_progress', 'test_completed', 'test_passed', 'test_failed'
                    ])),
                    
                Forms\Components\Section::make('Rendez-vous')
                    ->schema([
                        Forms\Components\DateTimePicker::make('meet_scheduled_at')
                            ->label('Date du rendez-vous')
                            ->native(false)
                            ->seconds(false),
                            
                        Forms\Components\Textarea::make('meet_notes')
                            ->label('Notes du rendez-vous')
                            ->rows(4)
                            ->placeholder('Notes prises pendant ou après le rendez-vous'),
                    ])
                    ->columns(1),
                    
                Forms\Components\Section::make('Décision')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut de la candidature')
                            ->options([
                                'pending' => 'En attente',
                                'test_in_progress' => 'Test en cours',
                                'test_completed' => 'Test terminé',
                                'test_passed' => 'Test réussi',
                                'test_failed' => 'Test échoué',
                                'meet_scheduled' => 'Rendez-vous planifié',
                                'under_review' => 'En cours d\'évaluation',
                                'accepted' => 'Accepté',
                                'rejected' => 'Rejeté',
                                'withdrawn' => 'Retiré',
                            ])
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\Textarea::make('acceptance_note')
                            ->label('Note d\'acceptation')
                            ->rows(3)
                            ->placeholder('Message pour le candidat accepté')
                            ->visible(fn ($get) => $get('status') === 'accepted'),
                            
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->rows(3)
                            ->placeholder('Expliquez pourquoi la candidature est rejetée')
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('applicant.name')
            ->columns([
                Tables\Columns\TextColumn::make('applicant.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
