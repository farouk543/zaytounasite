<?php

namespace App\Providers;

use App\Notifications\NewUserRegistered;
use App\Support\AdminNotifier;
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

            // Le contrôleur d'inscription peut déjà avoir attribué le rôle : on notifie quand même
            if ($user->roles()->count() === 0) {
                Role::findOrCreate('student', 'web');
                $user->assignRole('student');
            }

            // Notifier tous les admins (cloche Filament + e-mail)
            Role::findOrCreate('admin', 'web');
            AdminNotifier::send(new NewUserRegistered($user));
        });
    }
}
