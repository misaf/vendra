<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionTextarea;
use Misaf\VendraSupport\Filament\Forms\Components\IsDefaultToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;

final class SupportTestSluggableFormComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public string $activeLocale = 'en';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SluggableNameInput::make(),
                SlugInput::make()->uniqueWithinTenant(perLocale: true),
                DescriptionTextarea::make()->maxLength(10),
                IsDefaultToggle::make(),
            ])
            ->model(SupportTestSluggableRecord::class)
            ->statePath('data');
    }

    public function save(): void
    {
        $this->form->getState();
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
