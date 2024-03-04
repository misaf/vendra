<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Blog;

use App\Filament\Admin\Resources\Blog\BlogPostResource\Pages;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

final class BlogPostResource extends Resource
{
    use Translatable;

    protected static ?string $model = BlogPost::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'blogs/posts/posts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('blog_post_category_id')
                    ->columnSpanFull()
                    ->getOptionLabelFromRecordUsing(fn(BlogPostCategory $record, $livewire) => $record->getTranslation('name', $livewire->activeLocale))
                    ->label(__('model.blog_post_category'))
                    ->native(false)
                    ->preload()
                    ->relationship('blogPostCategory', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get('slug') ?? '') !== Str::slug($old)) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    })
                    ->autofocus()
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.name'))
                    ->live(onBlur: true)
                    ->required()
                    ->unique(ignoreRecord: true, column: fn($livewire) => 'name->' . $livewire->activeLocale, modifyRuleUsing: fn(Unique $rule) => $rule->whereNull('deleted_at'))
                    ->translatable(),

                Forms\Components\TextInput::make('slug')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.slug')),

                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull()
                    ->label(__('form.description'))
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->translatable(),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('form.image')),

                Forms\Components\Toggle::make('status')
                    ->columnSpanFull()
                    ->label(__('form.status'))
                    ->rules('required')
                    ->default(true),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.blog_management');
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function getModelLabel(): string
    {
        return __('navigation.blog_post');
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::rememberForever('blog_post_row_count', fn() => (string) Number::format(static::getModel()::count()));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.blog_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.blog_post');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPost::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'view'   => Pages\ViewBlogPost::route('/{record}'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.blog_post');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->circular()
                    ->conversion('thumb-table')
                    ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                    ->label(__('form.image'))
                    ->stacked()
                    ->defaultImageUrl(url('coin-payment/images/default.png')),

                Tables\Columns\TextColumn::make('blogPostCategory.name')
                    ->label(__('model.blog_post_category'))
                    ->searchable()
                    ->sortable()
                    ->summarize(Sum::make()),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('status')
                    ->label(__('form.status')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('form.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('form.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'desc')
            ->reorderable('position')
            ->paginatedWhileReordering();
        // ->poll()
    }

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return Notification::make()
            ->title('Saved successfully')
            ->getBroadcastMessage();
    }
}
