<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Livewire::setUpdateRoute(fn($handle) => Route::post('/admin/livewire/update', $handle));

// Route::get('xxx', fn() => 'as')->name('xxx.index');
