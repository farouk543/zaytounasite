<?php

namespace App\Filament\Resources\Subjects\Schemas;

use App\Models\Branch;
use App\Models\Level;
use App\Models\Track;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Organisation')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('track_id_virtual')
                        ->label('Filière')
                        ->options(fn () => Track::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('level_id', null);
                            $set('branch_id', null);
                        })
                        ->dehydrated(false)
                        ->required(),

                    Forms\Components\Select::make('level_id')
                        ->label('Niveau')
                        ->options(fn ($get) => Level::query()
                            ->where('track_id', $get('track_id_virtual'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->required()
                        ->disabled(fn ($get) => blank($get('track_id_virtual')))
                        ->afterStateUpdated(function ($set) {
                            $set('branch_id', null);
                        }),

                    Forms\Components\Select::make('branch_id')
                        ->label('Branche')
                        ->options(fn ($get) => Branch::query()
                            ->where('level_id', $get('level_id'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->disabled(fn ($get) => blank($get('level_id')))
                        ->visible(fn ($get) => filled($get('level_id')) && Branch::query()
                            ->where('level_id', $get('level_id'))
                            ->where('is_active', true)
                            ->exists())
                        ->required(fn ($get) => filled($get('level_id')) && Branch::query()
                            ->where('level_id', $get('level_id'))
                            ->where('is_active', true)
                            ->exists())
                        ->helperText('Obligatoire seulement pour les niveaux avec branches.'),
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
                        ->helperText('Unique par branche ou par niveau simple.'),

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