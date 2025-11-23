<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zluta leading-tight">
            {{ __('Můj Profil') }}
        </h2>
    </x-slot>

    <div x-data="{ activeTab: 'settings' }">
        
        <!-- ZÁLOŽKY -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-700 flex space-x-8">
                <button @click="activeTab = 'settings'" 
                        :class="activeTab === 'settings' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Nastavení účtu
                </button>

                <button @click="activeTab = 'reviews'" 
                        :class="activeTab === 'reviews' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Moje recenze
                </button>

                @if(auth()->user()->role === 'admin')
                    <button class="text-red-500 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm disabled cursor-not-allowed opacity-50" title="Admin sekce (Coming Soon)">
                        Admin Panel
                    </button>
                @endif
            </div>
        </div>

        <!-- OBSAH: NASTAVENÍ -->
        <div x-show="activeTab === 'settings'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>
                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>

        <!-- OBSAH: MOJE RECENZE -->
        <div x-show="activeTab === 'reviews'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" style="display: none;">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">Historie recenzí</x-slot>
                    <x-slot name="description">Zde vidíte všechny recenze, které jste napsali.</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2 space-y-6">
                    @forelse(auth()->user()->reviews()->with('pub')->latest()->get() as $review)
                        <div class="bg-black/75 border border-zluta shadow sm:rounded-lg p-6 transition hover:bg-gray-900">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-xl font-bold text-white">
                                        {{ $review->pub ? $review->pub->name : 'Neznámá hospoda' }}
                                    </h4>
                                    <p class="text-sm text-gray-400">{{ $review->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <!-- Hvězdičky -->
                                    <div class="text-zluta text-lg">
                                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                    </div>

                                    <!-- TLAČÍTKO SMAZAT (Koš) -->
                                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Opravdu chcete smazat tuto recenzi? Tato akce je nevratná.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-500 hover:text-red-500 transition duration-200 p-1 rounded hover:bg-gray-800" title="Smazat recenzi">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <p class="mt-4 text-gray-300 bg-gray-900/50 p-3 rounded border border-gray-700 italic">
                                "{{ $review->comment }}"
                            </p>
                        </div>
                    @empty
                        <div class="bg-black/75 border border-gray-700 shadow sm:rounded-lg p-6 text-center text-gray-400">
                            Zatím jste nenapsali žádnou recenzi.
                            <a href="{{ route('mapa') }}" class="text-zluta hover:underline block mt-2">Jít na mapu a ohodnotit hospodu</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>