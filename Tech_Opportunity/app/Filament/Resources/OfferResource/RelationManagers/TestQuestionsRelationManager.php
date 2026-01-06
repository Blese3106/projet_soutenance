<?php

namespace App\Filament\Resources\OfferResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TestQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'testQuestions';

    protected static ?string $title = 'Questions du test';
    
    protected static ?string $recordTitleAttribute = 'question';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\Textarea::make('question')
                            ->label('Question')
                            ->required()
                            ->rows(3)
                            ->placeholder('Posez votre question ici...')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Type de question')
                            ->options([
                                'mcq' => 'Choix unique (QCM)',
                                'multiple_choice' => 'Choix multiples',
                                'text' => 'Réponse textuelle',
                                'code' => 'Code à écrire',
                            ])
                            ->required()
                            ->default('mcq')
                            ->reactive()
                            ->helperText('Sélectionnez le type de réponse attendue'),
                            
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Nombre de points pour cette question'),
                            
                        Forms\Components\TextInput::make('order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('0 = premier, 1 = deuxième, etc.'),
                    ])->columns(3),
                    
                // Options pour QCM et Choix multiples
                Forms\Components\Section::make('Options de réponse')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->schema([
                                Forms\Components\TextInput::make('option')
                                    ->label('Option')
                                    ->required()
                                    ->placeholder('Ex: Laravel'),
                            ])
                            ->minItems(2)
                            ->maxItems(6)
                            ->defaultItems(4)
                            ->addActionLabel('Ajouter une option')
                            ->columnSpanFull()
                            ->helperText('Ajoutez les options de réponse possibles'),
                    ])
                    ->visible(fn ($get) => in_array($get('type'), ['mcq', 'multiple_choice']))
                    ->collapsed(false),
                    
                // Réponses correctes pour QCM
                Forms\Components\Section::make('Réponse correcte')
                    ->schema([
                        Forms\Components\Select::make('correct_answers')
                            ->label('Réponse correcte')
                            ->options(function ($get) {
                                $options = $get('options') ?? [];
                                return collect($options)->pluck('option', 'option')->toArray();
                            })
                            ->required()
                            ->helperText('Sélectionnez la bonne réponse parmi les options'),
                    ])
                    ->visible(fn ($get) => $get('type') === 'mcq')
                    ->collapsed(false),
                    
                // Réponses correctes pour Choix multiples
                Forms\Components\Section::make('Réponses correctes')
                    ->schema([
                        Forms\Components\CheckboxList::make('correct_answers')
                            ->label('Réponses correctes')
                            ->options(function ($get) {
                                $options = $get('options') ?? [];
                                return collect($options)->pluck('option', 'option')->toArray();
                            })
                            ->required()
                            ->helperText('Sélectionnez toutes les bonnes réponses'),
                    ])
                    ->visible(fn ($get) => $get('type') === 'multiple_choice')
                    ->collapsed(false),
                    
                // Réponse attendue pour type texte
                Forms\Components\Section::make('Réponse attendue')
                    ->schema([
                        Forms\Components\TextInput::make('expected_answer')
                            ->label('Réponse attendue')
                            ->required()
                            ->placeholder('La réponse exacte attendue')
                            ->helperText('La comparaison sera insensible à la casse'),
                    ])
                    ->visible(fn ($get) => $get('type') === 'text')
                    ->collapsed(false),
                    
                // Instructions pour code
                Forms\Components\Section::make('Instructions')
                    ->schema([
                        Forms\Components\Textarea::make('expected_answer')
                            ->label('Instructions et critères d\'évaluation')
                            ->rows(4)
                            ->placeholder('Décrivez ce qui est attendu et comment évaluer le code')
                            ->helperText('Cette question nécessitera une évaluation manuelle'),
                    ])
                    ->visible(fn ($get) => $get('type') === 'code')
                    ->collapsed(false),
                    
                Forms\Components\Section::make('Explication (optionnel)')
                    ->schema([
                        Forms\Components\Textarea::make('explanation')
                            ->label('Explication de la réponse')
                            ->rows(3)
                            ->placeholder('Expliquez pourquoi c\'est la bonne réponse (affiché après le test)')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(50)
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->wrap()
                    ->limit(100)
                    ->weight('bold'),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'mcq',
                        'success' => 'multiple_choice',
                        'warning' => 'text',
                        'danger' => 'code',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'mcq' => 'QCM',
                        'multiple_choice' => 'Choix multiples',
                        'text' => 'Texte',
                        'code' => 'Code',
                    }),
                    
                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('options')
                    ->label('Options')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' options' : '-')
                    ->color('secondary')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'mcq' => 'QCM',
                        'multiple_choice' => 'Choix multiples',
                        'text' => 'Texte',
                        'code' => 'Code',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Transformer les options du Repeater en array simple
                        if (isset($data['options']) && is_array($data['options'])) {
                            $data['options'] = array_column($data['options'], 'option');
                        }
                        
                        // S'assurer que correct_answers est un array
                        if (isset($data['correct_answers'])) {
                            if (!is_array($data['correct_answers'])) {
                                $data['correct_answers'] = [$data['correct_answers']];
                            }
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Transformer les options pour le Repeater
                        if (isset($data['options']) && is_array($data['options'])) {
                            $data['options'] = array_map(fn($opt) => ['option' => $opt], $data['options']);
                        }
                        return $data;
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Préparer les données pour l'édition
                        if (isset($data['options']) && is_array($data['options'])) {
                            $data['options'] = array_map(fn($opt) => ['option' => $opt], $data['options']);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->paginated([10, 25, 50]);
    }
}