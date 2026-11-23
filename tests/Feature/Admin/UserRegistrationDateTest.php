<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('shows the registration date, in Tunis time, on the users list', function () {
    $user = User::factory()->create(['created_at' => '2026-11-23 09:30:00']); // UTC → 10:30 à Tunis

    Livewire::test(ListUsers::class)
        ->assertTableColumnVisible('created_at')
        ->assertSee("Date d'inscription")
        ->assertSee('23/11/2026 10:30')
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords([$user]);
});

it('shows the registration date on the registrant page', function () {
    $user = User::factory()->create(['created_at' => '2026-11-23 09:30:00']);

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->assertOk()
        ->assertSee("Date d'inscription")
        ->assertSee('23/11/2026 à 10:30');
});
