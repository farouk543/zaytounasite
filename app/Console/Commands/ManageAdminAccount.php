<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Crée ou met à jour un compte administrateur sans jamais écrire le mot de passe dans le code.
 *
 *   php artisan zaytouna:admin contact@exemple.com --replace=admin@zaytouna.local
 *
 * Le mot de passe est demandé de façon masquée (ou lu dans ADMIN_PASSWORD en non interactif).
 * --replace : renomme l'ancien compte admin (garde son historique) au lieu d'en créer un nouveau.
 * --demote-others : retire le rôle admin à tous les autres comptes.
 */
class ManageAdminAccount extends Command
{
    protected $signature = 'zaytouna:admin
        {email : Adresse e-mail du compte administrateur}
        {--name=Admin : Nom affiché}
        {--replace= : Ancienne adresse admin à remplacer (le compte est renommé)}
        {--demote-others : Retirer le rôle admin aux autres comptes}
        {--reset-2fa : Réinitialiser la double authentification (perte du téléphone)}';

    protected $description = 'Crée ou met à jour le compte administrateur (mot de passe demandé, jamais stocké en clair)';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        $password = $this->readPassword();
        if ($password === null) {
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $replace = $this->option('replace') ? strtolower(trim($this->option('replace'))) : null;

        if (! $user && $replace && ($old = User::where('email', $replace)->first())) {
            $user = $old;
            $this->line("Le compte {$replace} devient {$email}.");
        }

        $user ??= new User;
        $user->forceFill([
            'name' => $user->name ?: $this->option('name'),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if ($this->option('reset-2fa')) {
            $user->forceFill(['app_authentication_secret' => null, 'app_authentication_recovery_codes' => null]);
        }

        $user->save();

        Role::findOrCreate('admin');
        $user->assignRole('admin');

        if ($this->option('demote-others')) {
            User::role('admin')->whereKeyNot($user->getKey())->get()
                ->each(fn (User $other) => $other->removeRole('admin'))
                ->each(fn (User $other) => $this->line("Rôle admin retiré : {$other->email}"));
        }

        $this->info("Compte administrateur prêt : {$email}");
        $this->line('À la prochaine connexion sur /admin, la double authentification sera demandée (application Google Authenticator, Microsoft Authenticator…).');

        return self::SUCCESS;
    }

    private function readPassword(): ?string
    {
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($password === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Mot de passe manquant : lancez la commande en interactif ou définissez ADMIN_PASSWORD.');

                return null;
            }

            $password = (string) $this->secret('Mot de passe (12 caractères min., majuscules, minuscules, chiffres et symboles)');
            if ($password !== (string) $this->secret('Confirmez le mot de passe')) {
                $this->error('Les deux mots de passe ne correspondent pas.');

                return null;
            }
        }

        $validator = Validator::make(['password' => $password], [
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        return $password;
    }
}
