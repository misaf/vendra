<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Widgets\Widget;
use Misaf\VendraTransaction\Filament\Widgets\TransactionTypeChartWidget;

final class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?int $navigationSort = -2;

    protected static string $routePath = '/dashboard';

    public function getMaxContentWidth(): string
    {
        return 'screen-2xl';
    }

    public function getWidgetsContentComponent(): Component
    {
        return Grid::make([
            'lg' => 3,
        ])->schema([
            Group::make(fn (): array => $this->getResponsiveWidgetsSchemaComponents())
                ->columns([
                    'md' => 3,
                ])
                ->columnSpan([
                    'lg' => 2,
                ]),

            Livewire::make(TransactionTypeChartWidget::class)
                ->key(TransactionTypeChartWidget::class)
                ->liberatedFromContainerGrid()
                ->extraAttributes([
                    'class' => 'max-md:order-last',
                ])
                ->columnSpan([
                    'lg' => 1,
                ]),
        ]);
    }

    /**
     * @return array<Component|Action|ActionGroup>
     */
    private function getResponsiveWidgetsSchemaComponents(): array
    {
        $components = $this->getWidgetsSchemaComponents($this->getWidgets());
        $componentCount = count($components);

        foreach ($components as $index => $component) {
            if (! $component instanceof Livewire) {
                continue;
            }

            $widget = resolve($component->getComponent());

            if (! $widget instanceof Widget) {
                continue;
            }

            $component
                ->liberatedFromContainerGrid(false)
                ->columnSpan($widget->getColumnSpan())
                ->columnStart($widget->getColumnStart())
                ->columnOrder([
                    'default' => $componentCount - $index,
                    'md' => $index + 1,
                ]);
        }

        return $components;
    }
}
