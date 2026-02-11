<?php

declare(strict_types=1);

namespace App\Filament\User\Clusters\Billings\Withdrawal\Pages;

use App\Filament\User\Clusters\Billings\Withdrawal\WithdrawalCluster\WithdrawalCluster;
use App\Livewire\User\Payment\Shetab\Form\ToCard;
use App\Livewire\User\Payment\Shetab\Form\ToSheba;
use Filament\Clusters\Cluster;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Misaf\VendraTransaction\Facades\TransactionService;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class Shetab extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.user.pages.billings.shetab.withdrawal';

    protected static ?string $slug = 'shetab';

    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = WithdrawalCluster::class;

    protected function getForms(): array
    {
        return [
            'editProfileForm',
        ];
    }

    public function editProfileForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('واریز به شماره کارت'))
                            ->schema([
                                Livewire::make(ToCard::class),
                            ]),
                        Tab::make(__('واریز به شماره شبا'))
                            ->schema([
                                Livewire::make(ToSheba::class),
                            ]),
                    ])
                    ->contained(),
            ])
            // ->model($this->getUser())
            ->statePath('profileData');
    }

    public static function getBreadcrumb(): string
    {
        return __('shetab::navigation.shetab');
    }

    public static function getModelLabel(): string
    {
        return __('shetab::navigation.shetab');
    }

    public static function getNavigationLabel(): string
    {
        return __('shetab::navigation.shetab');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shetab::navigation.shetab');
    }

    public static function getNavigationSort(): ?int
    {
        $position = TransactionGateway::whereJsonContainsLocale('slug', app()->getLocale(), 'shetab', '=')
            ->value('position');

        return is_numeric($position) ? (int) $position : 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return TransactionService::hasActiveTransactionGateway('shetab');
    }

    public static function canAccess(): bool
    {
        return TransactionService::hasActiveTransactionGateway('shetab');
    }

    protected function fillForms(): void
    {
        $this->editProfileForm->fill();
    }
}
