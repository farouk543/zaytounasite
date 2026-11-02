<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom (FR)')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($get, $set, ?string $state) {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            Forms\Components\TextInput::make('name_ar')->label('Nom (AR)')->maxLength(120)->extraInputAttributes(['dir' => 'rtl']),
            Forms\Components\TextInput::make('name_en')->label('Nom (EN)')->maxLength(120),

            Forms\Components\TextInput::make('slug')
                ->label('Adresse (URL)')
                ->prefix('/blog/categorie/')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->columnSpan(2),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),

            Forms\Components\Textarea::make('description')->label('Description (FR)')->rows(2)
                ->helperText('Affichée en haut de la page catégorie et dans Google.'),
            Forms\Components\Textarea::make('description_ar')->label('Description (AR)')->rows(2)->extraInputAttributes(['dir' => 'rtl']),
            Forms\Components\Textarea::make('description_en')->label('Description (EN)')->rows(2),

            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}
