<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoalResource\Pages;
use App\Models\Goal;
use App\Models\MentorshipRelation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GoalResource extends Resource
{
    protected static ?string $model = Goal::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    
    protected static ?string $navigationLabel = 'Objectifs';
    
    protected static ?string $navigationGroup = 'Mentorat et Coaching';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Relation de mentorat')
                    ->schema([
                        Forms\Components\Select::make('mentorship_relation_id')
                            ->label('Relation de mentorat')
                            ->relationship(
                                name: 'mentorshipRelation',
                                modifyQueryUsing: fn ($query) => $query->where('status', 'active')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->mentor->name} → {$record->mentee->name}"
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                    
                Forms\Components\Section::make('Informations de l\'objectif')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                            
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
                                'pending' => 'Attente',
                                'active' => 'Active',
                                'done' => 'Terminé',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Détails')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\DatePicker::make('target_date')
                            ->label('Date cible')
                            ->native(false)
                            ->minDate(now()),
                            
                        Forms\Components\TextInput::make('progress')
                            ->label('Progression (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Hidden::make('created_by')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mentorshipRelation.mentor.name')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('mentorshipRelation.mentee.name')
                    ->label('Mentoré')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priorité')
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Faible',
                        'medium' => 'Moyenne',
                        'high' => 'Haute',
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'Attente',
                        'info' => 'Active',
                        'success' => 'Terminé',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Attente',
                        'active' => 'Active',
                        'done' => 'Terminé',
                    }),
                    
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progression')
                    ->suffix('%')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                    
                Tables\Columns\TextColumn::make('target_date')
                    ->label('Date cible')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'Attente',
                        'active' => 'Active',
                        'done' => 'Terminé',
                    ]),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priorité')
                    ->options([
                        'low' => 'Faible',
                        'medium' => 'Moyenne',
                        'high' => 'Haute',
                    ]),
                    
                Tables\Filters\Filter::make('overdue')
                    ->label('En retard')
                    ->query(fn ($query) => $query->overdue()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Terminer')
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoals::route('/'),
            'create' => Pages\CreateGoal::route('/create'),
            'edit' => Pages\EditGoal::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_progress')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}