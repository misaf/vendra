<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Misaf\VendraSupport\Filament\Forms\Components\ActiveToggle;
use Misaf\VendraSupport\Filament\Tables\Columns\ActiveToggleColumn;

final class SupportTestActiveToggleComponent extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([ActiveToggle::make()->default(true)])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SupportTestActiveRecord::query())
            ->columns([ActiveToggleColumn::make()]);
    }

    public function render(): string
    {
        return '<div>{{ $this->form }} {{ $this->table }}</div>';
    }
}
