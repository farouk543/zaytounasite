<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label(fn () => $this->getRecord()->isPublished() ? 'Voir sur le site' : 'Aperçu')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => $this->getRecord()->url())
                ->openUrlInNewTab(),
            DeleteAction::make()->label('Supprimer'),
        ];
    }
}
