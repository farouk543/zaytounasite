<?php

namespace App\Filament\Resources\Levels\Schemas;

use App\Models\Track;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Organisation')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('track_id')
                        ->label('Track')
                        ->options(fn () => Track::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Informations')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom (FR)')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($get, $set, ?string $state) {
                            if (blank($get('slug')) && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(120)
                        ->helperText('Unique par Track'),

                    Forms\Components\TextInput::make('name_ar')
                        ->label('Nom (AR)')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }
}