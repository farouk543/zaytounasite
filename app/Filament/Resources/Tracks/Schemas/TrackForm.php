<?php

namespace App\Filament\Resources\Tracks\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class TrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
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
                        ->helperText('Unique. Ex: tunisia, qatar...'),

                    Forms\Components\TextInput::make('name_ar')
                        ->label('Nom (AR)')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0)
                        ->helperText('0, 1, 2… pour trier sur le site'),
                ]),
        ]);
    }
}