<?php

namespace App\Filament\Resources\SummerClubSubscriptionRequests\Schemas;

use App\Models\SummerClubSubscriptionRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SummerClubSubscriptionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Demande')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Utilisateur associé')
                        ->options(fn () => User::query()->orderBy('name')->pluck('email', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\TextInput::make('status')
                        ->label('Statut')
                        ->disabled(),

                    Forms\Components\TextInput::make('parent_name')
                        ->label('Nom parent')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('student_name')
                        ->label('Nom élève')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->maxLength(40),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('pack_name')
                        ->label('Pack')
                        ->disabled(),

                    Forms\Components\TextInput::make('price')
                        ->label('Prix')
                        ->disabled(),

                    Forms\Components\CheckboxList::make('selected_subjects')
                        ->label('Matières')
                        ->options(SummerClubSubscriptionRequest::subjectOptions())
                        ->columns(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notes admin')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
