<?php

namespace App\Filament\Resources\MentorshipRelationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Sessions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la session')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
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
                            ->minDate(now()),
                            
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Durée (minutes)')
                            ->numeric()
                            ->required()
                            ->default(60)
                            ->minValue(15)
                            ->maxValue(480)
                            ->suffix('min'),
                            
                        Forms\Components\TextInput::make('link')
                            ->label('Lien')
                            ->maxLength(255)
                            ->placeholder('lien de visioconférence'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('agenda')
                            ->label('Ordre du jour')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Après la session')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('feedback')
                            ->label('Retour du mentoré')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('rating')
                            ->label('Évaluation')
                            ->options([
                                1 => '⭐ (1/5)',
                                2 => '⭐⭐ (2/5)',
                                3 => '⭐⭐⭐ (3/5)',
                                4 => '⭐⭐⭐⭐ (4/5)',
                                5 => '⭐⭐⭐⭐⭐ (5/5)',
                            ]),
                    ])->columns(2),
                    
                Forms\Components\Hidden::make('created_by')
                    ->default(Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'mentorat',
                        'success' => 'coaching',
                    ]),
                    
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Date et heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->isUpcoming() ? 'success' : 'secondary'),
                    
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durée')
                    ->suffix(' min')
                    ->alignCenter(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'info' => 'rescheduled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Planifié',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'rescheduled' => 'Reporté',
                    }),
                    
                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : '-')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'scheduled' => 'Planifié',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'rescheduled' => 'Reporté',
                    ]),
                    
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'meeting' => 'Réunion',
                        'course' => 'Cours',
                        'workshop' => 'Atelier',
                        'review' => 'Revue',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Marquer comme terminé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->markAsCompleted())
                    ->visible(fn ($record) => $record->status !== 'completed'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
