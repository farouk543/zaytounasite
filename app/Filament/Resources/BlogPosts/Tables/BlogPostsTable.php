<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\BlogPost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(44)
                    ->extraImgAttributes(['style' => 'border-radius:8px; aspect-ratio:1.91/1; object-fit:cover;']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(['title', 'title_ar', 'title_en', 'focus_keyword'])
                    ->sortable()
                    ->limit(60)
                    ->description(fn (BlogPost $r) => $r->focus_keyword ? 'Mot-clé : '.$r->focus_keyword : null),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('langs')
                    ->label('Langues')
                    ->state(fn (BlogPost $r) => strtoupper(implode(' ', $r->availableLocales())) ?: '—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (BlogPost $r) => match (true) {
                        $r->status === 'draft' => 'Brouillon',
                        $r->published_at?->isFuture() => 'Programmé',
                        default => 'Publié',
                    })
                    ->color(fn (BlogPost $r) => match (true) {
                        $r->status === 'draft' => 'gray',
                        $r->published_at?->isFuture() => 'info',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('markets')
                    ->label('Pays ciblés')
                    ->state(fn (BlogPost $r) => collect($r->target_countries ?: [])
                        ->map(fn ($cc) => \App\Support\Markets::flagHtml($cc))->implode(' ') ?: 'Tous')
                    ->html()
                    ->tooltip(fn (BlogPost $r) => $r->targetCountriesLabel() ?: 'Tous les pays'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i', config('marketing.timezone'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Lectures')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('À la une')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['draft' => 'Brouillon', 'published' => 'Publié']),
                Tables\Filters\SelectFilter::make('target_country')
                    ->label('Pays ciblé')
                    ->options(collect(config('marketing.markets'))->map(fn ($m) => $m['flag'].' '.$m['name'])->all())
                    ->query(fn ($query, array $data) => $query->when($data['value'] ?? null,
                        fn ($q, $cc) => $q->where('target_countries', 'like', '%"'.$cc.'"%'))),
                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (BlogPost $r) => $r->url())
                    ->openUrlInNewTab(),
                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun article')
            ->emptyStateDescription('Écrivez votre premier article : un sujet que les parents recherchent sur Google.');
    }
}
