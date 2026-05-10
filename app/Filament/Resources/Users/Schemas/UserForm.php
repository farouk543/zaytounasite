<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Compte')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(190),

                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create'),

                    Forms\Components\Select::make('roles')
                        ->label('Rôle')
                        ->multiple()
                        ->relationship('roles', 'name')     // ✅ Filament gère options + sync + validation
                        ->preload()
                        ->searchable()
                        ->default(['student'])              // ✅ par défaut, un compte créé = student
                        ->helperText("Par défaut: student pour les inscriptions publiques.")
                        ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                ]),
        ]);
    }
}