<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Sessions';
    
    protected static ?string $navigationGroup = 'Mentorat et Coaching';
    
    protected static ?int $navigationSort = 3;

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
                            ->placeholder('Lien de visioconférence'),
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
                            ->columnSpanFull()
                            ->helperText('Notes prises pendant ou après la session'),
                            
                        Forms\Components\Textarea::make('feedback')
                            ->label('Retour du mentoré')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('rating')
                            ->label('Évaluation')
                            ->options([
                                1 => '⭐ ',
                                2 => '⭐⭐ ',
                                3 => '⭐⭐⭐ ',
                                4 => '⭐⭐⭐⭐ ',
                                5 => '⭐⭐⭐⭐⭐ ',
                            ])
                            ->helperText('Note donnée par le mentoré'),
                    ])->columns(2),
                    
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
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'mentorat',
                        'success' => 'coaching',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'mentorat' => 'Mentorat',
                        'coaching' => 'Coaching',
                    }),
                    
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
                    
                Tables\Columns\TextColumn::make('link')
                    ->label('Lien')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'mentorat' => 'Mentorat',
                        'coaching' => 'Coaching',
                    ]),
                    
                Tables\Filters\Filter::make('upcoming')
                    ->label('À venir')
                    ->query(fn ($query) => $query->upcoming()),
                    
                Tables\Filters\Filter::make('today')
                    ->label('Aujourd\'hui')
                    ->query(fn ($query) => $query->today()),
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
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}