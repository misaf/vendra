<?php

declare(strict_types=1);

namespace App\Filament\User\Pages;

use App\Tables\Columns\CreatedAtTextColumn;
use App\Tables\Columns\DeletedAtTextColumn;
use App\Tables\Columns\UpdatedAtTextColumn;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Misaf\Affiliate\Actions\AddAffiliateCommissionToBalance;
use Misaf\Affiliate\Models\Affiliate as ModelsAffiliate;
use Misaf\Affiliate\Models\AffiliateUser;
use Misaf\Tenant\Models\Tenant;
use Misaf\VendraUser\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class Affiliate extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Affiliate::class;

    protected string $view = 'filament.user.pages.affiliate';

    public function getTitle(): string|Htmlable
    {
        return __('navigation.affiliate');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.affiliate');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return filament()->auth()->user()->hasRole('reseller');
    }

    public static function canAccess(): bool
    {
        return filament()->auth()->user()->hasRole('reseller');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                AffiliateUser::query()
                    ->whereRelation('affiliate', 'user_id', filament()->auth()->user()->getAuthIdentifier()),
            )
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('user.username')
                    ->label(__('model.user'))
                    ->searchable(),
                TextColumn::make('commission_earned')
                    ->label(__('affiliate.commission_earned'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0)
                    ->sortable(),
                CreatedAtTextColumn::make('created_at'),
                UpdatedAtTextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: false),
                DeletedAtTextColumn::make('deleted_at'),
            ])
            ->description(function () {
                $commissionPercent = filament()->auth()->user()->latestAffiliate->commission_percent ?? 0;

                return sprintf(__('affiliate.commission_percent') . ': %s%%', $commissionPercent);
            })
            ->headerActions([
                Action::make('process_affiliate_commission')
                    ->action(function ($livewire): void {
                        $affiliates = ModelsAffiliate::query()
                            ->where('user_id', filament()->auth()->user()->getAuthIdentifier())
                            ->where('is_processing', false)
                            ->get();

                        if ( ! $affiliates) {
                            return;
                        }

                        foreach ($affiliates as $affiliate) {
                            $isProcessingUpdated = $affiliate->update(['is_processing' => true]);

                            if ($isProcessingUpdated <= 0) {
                                return;
                            }

                            (new AddAffiliateCommissionToBalance())
                                ->onQueue('process-affiliate-commission')
                                ->execute($affiliate);
                        }

                        $livewire->dispatch('refresh');
                    })
                    ->icon('heroicon-o-banknotes')
                    ->label(__('دریافت کمیسیون'))
                    ->requiresConfirmation(),
                Action::make('process_affiliate_commi2ssion')
                    ->modalContent(function () {
                        $currentTenantSlug = Tenant::current()->slug;

                        [$red, $green, $blue] = [255, 0, 0];

                        // Generate the QR code
                        $qrCode = QrCode::errorCorrection('L')
                            ->eye('square')
                            ->color($red, $green, $blue)
                            ->margin(1)
                            ->size(150)
                            ->generate(route('filament.user.auth.register', [
                                'affiliate' => filament()->auth()->user()->latestAffiliate->slug,
                            ]));

                        // Generate the affiliate link
                        $affiliateSlug = ModelsAffiliate::query()
                            ->where('user_id', filament()->auth()->user()->getAuthIdentifier())
                            ->value('slug');

                        $affiliateLink = route('filament.user.auth.register', [
                            'affiliate' => $affiliateSlug,
                        ]);

                        // Return both the QR code and the affiliate link
                        return new HtmlString("
                            <div style='text-align: center;'>
                                <div>{$qrCode}</div>
                                <p><a href='{$affiliateLink}' target='_blank'>لینک {$affiliateLink}</a></p>
                            </div>
                        ");
                    })
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false)
                    ->icon('heroicon-o-qr-code')
                    ->label(__('لینک کسب درآمد')),
            ]);
    }

    private function getAuthenticatedUser(): ?User
    {
        return filament()->auth()->user();
    }
}
