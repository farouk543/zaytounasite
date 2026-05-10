<?php

namespace App\Providers;

use App\Notifications\NewUserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Registered::class, function ($event) {
            $user = $event->user;

            if (! $user || ! method_exists($user, 'assignRole')) {
                return;
            }

            if (method_exists($user, 'roles') && $user->roles()->count() > 0) {
                return;
            }

            Role::findOrCreate('student', 'web');
            $user->assignRole('student');

            // Notifier tous les admins
            Role::findOrCreate('admin', 'web');
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewUserRegistered($user));
            }
        });
    }
}
