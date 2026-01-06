<?php
// app/Filament/Resources/MentorshipRelationResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\MentorshipRelationResource\Pages;
use App\Filament\Resources\MentorshipRelationResource\RelationManagers;
use App\Models\MentorshipRelation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class MentorshipRelationResource extends Resource
{
    protected static ?string $model = MentorshipRelation::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Relations de Mentorat';
    
    protected static ?string $pluralLabel = 'Relations de Mentorat';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la relation')
                    ->schema([
                        Forms\Components\Select::make('mentor_id')
                            ->label('Mentor / Coach')
                            ->options(function () {
                                return User::whereIn('role', ['mentor', 'coach'])
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        $label = '[' . ucfirst($user->role) . '] ' . $user->name;
                                        return [$user->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('mentee_id')
                            ->label('Mentoré')
                            ->options(fn () => User::query()
                                ->where('role', 'mentee')
                                ->pluck('name', 'id')
                                ->toArray()
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'active' => 'Actif',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->native(false),
                            
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->native(false)
                            ->after('start_date'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mentor.name')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->mentor->email),
                    
                Tables\Columns\TextColumn::make('mentee.name')
                    ->label('Mentoré')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->mentee->email),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'secondary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    }),
                    
                Tables\Columns\TextColumn::make('objectives_count')
                    ->label('Objectifs')
                    ->counts('objectives')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('Sessions')
                    ->counts('sessions')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                    
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
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),
                    
                Tables\Filters\SelectFilter::make('mentor_id')
                    ->label('Mentor')
                    ->options(fn () => User::query()
                        ->where('role', 'mentor')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('mentor.name')
                            ->label('Mentor'),
                        Infolists\Components\TextEntry::make('mentee.name')
                            ->label('Mentoré'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'En attente',
                                'active' => 'Actif',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            }),
                    ])->columns(3),
                    
                Infolists\Components\Section::make('Dates')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('Date de début')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->label('Date de fin')
                            ->date('d/m/Y'),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->prose(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\ObjectivesRelationManager::class,
            //RelationManagers\SessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMentorshipRelations::route('/'),
            'create' => Pages\CreateMentorshipRelation::route('/create'),
            'edit' => Pages\EditMentorshipRelation::route('/{record}/edit'),
        ];
    }
}