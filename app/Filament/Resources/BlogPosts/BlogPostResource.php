<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\BlogPosts\Schemas\BlogPostForm;
use App\Filament\Resources\BlogPosts\Tables\BlogPostsTable;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $slug = 'blog/articles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing & Blog';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Articles du blog';

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'Articles du blog';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $drafts = BlogPost::where('status', 'draft')->count();

        return $drafts > 0 ? (string) $drafts : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Brouillons';
    }

    public static function form(Schema $schema): Schema
    {
        return BlogPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogPostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
