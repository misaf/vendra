<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Affiliates\Resources\Affiliates;

use App\Filament\Admin\Clusters\Affiliates\AffiliatesCluster;
use App\Filament\Admin\Clusters\Affiliates\Resources\Affiliates\Pages\CreateAffiliate;
use App\Filament\Admin\Clusters\Affiliates\Resources\Affiliates\Pages\EditAffiliate;
use App\Filament\Admin\Clusters\Affiliates\Resources\Affiliates\Pages\ListAffiliates;
use App\Filament\Admin\Clusters\Affiliates\Resources\Affiliates\Pages\ViewAffiliate;
use App\Filament\Admin\Clusters\Affiliates\Resources\AffiliateUsers\RelationManagers\AffiliateUserRelationManager;
use App\Forms\Components\DescriptionTextarea;
use App\Forms\Components\SlugTextInput;
use App\Forms\Components\StatusToggle;
use App\Tables\Columns\CreatedAtTextColumn;
use App\Tables\Columns\DeletedAtTextColumn;
use App\Tables\Columns\ModelLinkColumn;
use App\Tables\Columns\StatusToggleColumn;
use App\Tables\Columns\UpdatedAtTextColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\Affiliate\Actions\AddAffiliateCommissionToBalance;
use Misaf\Affiliate\Facades\AffiliateService;
use Misaf\Affiliate\Models\Affiliate;
use Misaf\Tenant\Models\Tenant;

final class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'affiliates';

    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = AffiliatesCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('navigation.affiliate');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.affiliate');
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.affiliate_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.affiliate');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.affiliate');
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListAffiliates::route('/'),
            'create' => CreateAffiliate::route('/create'),
            'view'   => ViewAffiliate::route('/{record}'),
            'edit'   => EditAffiliate::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<class-string<RelationManager> | RelationGroup | RelationManagerConfiguration>
     */
    public static function getRelations(): array
    {
        return [
            AffiliateUserRelationManager::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->columnSpanFull()
                    ->label(__('form.username'))
                    ->native(false)
                    ->preload()
                    ->relationship('user', 'username')
                    ->required()
                    ->searchable(),

                TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get('slug') ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('form.name'))
                    ->live(onBlur: true)
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule): void {
                            $rule->where('tenant_id', Tenant::current()->id)
                                ->withoutTrashed();
                        },
                    ),

                SlugTextInput::make('slug')
                    ->default(function (): string {
                        return AffiliateService::generateSlug();
                    }),
                TextInput::make('commission_percent')
                    ->afterStateUpdated(function (Livewire $livewire): void {
                        $livewire->validateOnly('data.commission_percent');
                    })
                    ->columnSpanFull()
                    ->default(20)
                    ->extraAttributes(['dir' => 'ltr'])
                    ->label(__('affiliate.commission_percent'))
                    ->live(onBlur: true)
                    ->maxValue(50)
                    ->minValue(1)
                    ->numeric(),
                DescriptionTextarea::make('description'),
                StatusToggle::make('status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex(),

                ModelLinkColumn::make('user.username')
                    ->alignCenter()
                    ->label(__('form.username'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->alignCenter()
                    ->copyable()
                    ->copyableState(function (string $state): string {
                        return route('filament.user.auth.register', ['affiliate' => $state]);
                    })
                    ->copyMessage(__('Link copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(function (string $state): string {
                        return route('filament.user.auth.register', ['affiliate' => $state]);
                    })
                    ->label(__('form.link'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('commission_percent')
                    ->alignCenter()
                    ->label(__('affiliate.commission_percent'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0)
                    ->sortable()
                    ->suffix('%')
                    ->action(
                        Action::make('updateCommissionPercent')
                            ->schema([
                                TextInput::make('commission_percent')
                                    ->afterStateUpdated(function (Livewire $livewire): void {
                                        $livewire->validateOnly('data.commission_percent');
                                    })
                                    ->columnSpanFull()
                                    ->default(function (string $operation) {
                                        if ('create' === $operation) {
                                            return 20;
                                        }
                                    })
                                    ->extraAttributes(['dir' => 'ltr'])
                                    ->label(__('affiliate.commission_percent'))
                                    ->live(onBlur: true)
                                    ->maxValue(50)
                                    ->minValue(1)
                                    ->numeric(),
                            ])
                            ->action(function (array $data, Affiliate $record): void {
                                $record->commission_percent = $data['commission_percent'];
                                $record->save();
                            })
                            ->label(__('بروزرسانی کمیسیون')),
                    ),
                IconColumn::make('is_processing')
                    ->alignCenter()
                    ->boolean()
                    ->label(__('affiliate.is_processing'))
                    ->sortable(),
                StatusToggleColumn::make('status'),
                CreatedAtTextColumn::make('created_at'),
                UpdatedAtTextColumn::make('updated_at'),
                DeletedAtTextColumn::make('deleted_at'),
            ])
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('slug'),
                            NumberConstraint::make('commission_percent'),
                            DateConstraint::make('created_at')
                                ->label(__('form.created_at')),
                            DateConstraint::make('updated_at')
                                ->label(__('form.updated_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    Action::make('process_affiliate_commission')
                        ->action(function (Affiliate $record): void {
                            $isProcessingUpdated = $record->update(['is_processing' => true]);

                            if ( ! $isProcessingUpdated) {
                                return;
                            }

                            (new AddAffiliateCommissionToBalance())
                                ->onQueue('process-affiliate-commission')
                                ->execute($record);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->label(__('دریافت کمیسیون'))
                        ->requiresConfirmation()
                        ->disabled(function (Affiliate $record): bool {
                            return $record->is_processing;
                        }),

                    ViewAction::make(),

                    EditAction::make(),

                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
