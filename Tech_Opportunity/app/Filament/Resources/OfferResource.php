<?php
// app/Filament/Resources/OfferResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferResource\Pages;
use App\Filament\Resources\OfferResource\RelationManagers;
use App\Models\Offer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    
    protected static ?string $navigationLabel = 'Offres d\'emploi';
    
    protected static ?string $navigationGroup = 'Offres';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre du poste')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Développeur Laravel Senior')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Type d\'offre')
                            ->options([
                                'job' => 'Emploi',
                                'internship' => 'Stage',
                                'freelance' => 'Freelance',
                                'project' => 'Projet',
                            ])
                            ->required()
                            ->default('job'),
                            
                        Forms\Components\Select::make('contract_type')
                            ->label('Type de contrat')
                            ->options([
                                'cdi' => 'CDI',
                                'cdd' => 'CDD',
                                'stage' => 'Stage',
                                'freelance' => 'Freelance',
                                'other' => 'Autre',
                            ]),
                            
                        Forms\Components\Select::make('work_mode')
                            ->label('Mode de travail')
                            ->options([
                                'remote' => 'Télétravail',
                                'onsite' => 'Sur site',
                                'hybrid' => 'Hybride',
                            ])
                            ->required()
                            ->default('onsite'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Entreprise et localisation')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Nom de l\'entreprise')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('location')
                            ->label('Localisation')
                            ->placeholder('Ex: Paris, France')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('salary_range')
                            ->label('Fourchette salariale')
                            ->placeholder('Ex: 30k-40k €/an ou 500$/jour')
                            ->maxLength(255),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Description du poste')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                                'redo',
                                'strike',
                                'undo',
                            ]),
                            
                        Forms\Components\Textarea::make('required_skills')
                            ->label('Compétences requises')
                            ->rows(3)
                            ->placeholder('Ex: Laravel, Vue.js, MySQL, Docker')
                            ->helperText('Séparez les compétences par des virgules'),
                            
                        Forms\Components\Textarea::make('responsibilities')
                            ->label('Responsabilités')
                            ->rows(3)
                            ->placeholder('Liste des responsabilités du poste'),
                            
                        Forms\Components\Textarea::make('benefits')
                            ->label('Avantages')
                            ->rows(3)
                            ->placeholder('Ex: Tickets restaurant, mutuelle, télétravail'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Processus de candidature')
                    ->schema([
                        Forms\Components\Select::make('validation_type')
                            ->label('Type de validation')
                            ->options([
                                'direct_meet' => 'Rendez-vous direct (lien Meet)',
                                'test' => 'Test uniquement',
                                'test_then_meet' => 'Test puis Rendez-vous',
                            ])
                            ->required()
                            ->default('direct_meet')
                            ->reactive()
                            ->helperText('Comment voulez-vous évaluer les candidats?'),
                            
                        Forms\Components\TextInput::make('meet_link')
                            ->label('Lien de visioconférence')
                            ->url()
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx')
                            ->visible(fn ($get) => in_array($get('validation_type'), ['direct_meet', 'test_then_meet']))
                            ->helperText('Google Meet, Zoom, Teams, etc.'),
                            
                        Forms\Components\Toggle::make('has_test')
                            ->label('Cette offre inclut un test')
                            ->reactive()
                            ->visible(fn ($get) => in_array($get('validation_type'), ['test', 'test_then_meet']))
                            ->default(fn ($get) => in_array($get('validation_type'), ['test', 'test_then_meet'])),
                            
                        Forms\Components\TextInput::make('test_duration_minutes')
                            ->label('Durée du test (minutes)')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(180)
                            ->default(30)
                            ->visible(fn ($get) => $get('has_test'))
                            ->required(fn ($get) => $get('has_test')),
                            
                        Forms\Components\TextInput::make('test_passing_score')
                            ->label('Score minimum pour réussir (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(60)
                            ->suffix('%')
                            ->visible(fn ($get) => $get('has_test'))
                            ->required(fn ($get) => $get('has_test')),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Publication')
                    ->schema([
                        Forms\Components\DatePicker::make('application_deadline')
                            ->label('Date limite de candidature')
                            ->native(false)
                            ->minDate(now())
                            ->helperText('Laissez vide pour une offre sans date limite'),
                            
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'published' => 'Publiée',
                                'closed' => 'Fermée',
                                'cancelled' => 'Annulée',
                            ])
                            ->required()
                            ->default('published'),
                    ])->columns(2),
                    
                Forms\Components\Hidden::make('posted_by')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('poster.name')
                    ->label('Publié par')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'job',
                        'success' => 'internship',
                        'warning' => 'freelance',
                        'info' => 'project',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'job' => 'Emploi',
                        'internship' => 'Stage',
                        'freelance' => 'Freelance',
                        'project' => 'Projet',
                    }),
                    
                Tables\Columns\BadgeColumn::make('validation_type')
                    ->label('Validation')
                    ->colors([
                        'success' => 'direct_meet',
                        'warning' => 'test',
                        'info' => 'test_then_meet',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'direct_meet' => 'Meet',
                        'test' => 'Test',
                        'test_then_meet' => 'Test + Meet',
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'danger' => 'closed',
                        'warning' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'published' => 'Publiée',
                        'closed' => 'Fermée',
                        'cancelled' => 'Annulée',
                    }),
                    
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Candidatures')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues')
                    ->badge()
                    ->color('secondary')
                    ->alignCenter()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('application_deadline')
                    ->label('Date limite')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->hasDeadlinePassed() ? 'danger' : 'success')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'published' => 'Publiée',
                        'closed' => 'Fermée',
                        'cancelled' => 'Annulée',
                    ]),
                    
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'job' => 'Emploi',
                        'internship' => 'Stage',
                        'freelance' => 'Freelance',
                        'project' => 'Projet',
                    ]),
                    
                Tables\Filters\SelectFilter::make('validation_type')
                    ->label('Type de validation')
                    ->options([
                        'direct_meet' => 'Meet direct',
                        'test' => 'Test',
                        'test_then_meet' => 'Test + Meet',
                    ]),
                    
                Tables\Filters\SelectFilter::make('posted_by')
                    ->label('Publié par')
                    ->relationship('poster', 'name')
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\TestQuestionsRelationManager::class,
            //RelationManagers\ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }
    
    /* public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'published')->count();
    } */
}