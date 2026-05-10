<?php

namespace App\Filament\Resources\SummerClubQuizzes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Questions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Question')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('question')
                        ->label('Question')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('media_type')
                        ->label('Type de média')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Vidéo',
                            'audio' => 'Audio',
                        ])
                        ->nullable()
                        ->live(),

                    Forms\Components\TextInput::make('media_url')
                        ->label('Lien média externe')
                        ->placeholder('https://...')
                        ->url()
                        ->nullable(),

                    Forms\Components\FileUpload::make('media_path')
                        ->label('Fichier média')
                        ->disk('public')
                        ->directory('summer-club/quiz-media')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'image/*',
                            'video/*',
                            'audio/*',
                        ])
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('option_a')
                        ->label('Option A')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('option_b')
                        ->label('Option B')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('option_c')
                        ->label('Option C')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('option_d')
                        ->label('Option D')
                        ->maxLength(255),

                    Forms\Components\Select::make('correct_option')
                        ->label('Bonne réponse')
                        ->options([
                            'a' => 'A',
                            'b' => 'B',
                            'c' => 'C',
                            'd' => 'D',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('points')
                        ->label('Points')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Textarea::make('explanation')
                        ->label('Explication')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->limit(70)
                    ->searchable(),

                Tables\Columns\TextColumn::make('correct_option')
                    ->label('Réponse')
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->badge(),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->sortable(),

                Tables\Columns\TextColumn::make('media_type')
                    ->label('Média')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'image' => 'Image',
                        'video' => 'Vidéo',
                        'audio' => 'Audio',
                        default => '-',
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Ajouter une question'),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
