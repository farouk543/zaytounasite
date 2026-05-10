<section class="za-profile-section space-y-6">
    <header class="za-profile-section-head">
        <h2 class="za-profile-section-title">
            {{ __('ui.profile.delete_account') }}
        </h2>

        <p class="za-profile-section-text">
            {{ __('ui.profile.delete_account_text') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('ui.profile.delete_account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 za-profile-delete-modal">
            @csrf
            @method('delete')

            <h2 class="za-profile-section-title">
                {{ __('ui.profile.confirm_delete_title') }}
            </h2>

            <p class="mt-2 za-profile-section-text">
                {{ __('ui.profile.confirm_delete_text') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" :value="__('ui.profile.password')" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    :placeholder="__('ui.profile.password')"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('ui.profile.cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('ui.profile.delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>