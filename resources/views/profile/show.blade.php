<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zluta leading-tight">
            {{ __('Můj Profil') }}
        </h2>
    </x-slot>

    <div x-data="{ 
        activeTab: 'settings', 
        editingReview: null,
        editForm: { rating: 0, comment: '', pub_id: null },
        isSaving: false,

        openEditModal(review) {
            this.editingReview = review;
            this.editForm.rating = review.rating;
            this.editForm.comment = review.comment;
            this.editForm.pub_id = review.pub_id;
        },

        closeEditModal() { this.editingReview = null; },
        setEditRating(val) { this.editForm.rating = val; },

        async saveReview() {
            this.isSaving = true;
            try {
                const response = await fetch('{{ route('reviews.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.editForm)
                });
                if (response.ok) { alert('Upraveno!'); window.location.reload(); } 
                else { alert('Chyba.'); }
            } catch (e) { alert('Chyba komunikace.'); } 
            finally { this.isSaving = false; }
        }
    }">
        
        <!-- ZÁLOŽKY -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Na mobilech umožníme scrollování záložek do boku -->
            <div class="border-b border-gray-700 flex space-x-8 overflow-x-auto">
                
                <!-- Běžný uživatel -->
                <button @click="activeTab = 'settings'" 
                        :class="activeTab === 'settings' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Nastavení účtu
                </button>

                <button @click="activeTab = 'reviews'" 
                        :class="activeTab === 'reviews' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Moje recenze
                </button>

                <!-- Admin Sekce -->
                @if(auth()->user()->isAdmin())
                    <div class="border-l border-gray-700 mx-4 h-6 self-center"></div> <!-- Oddělovač -->

                    <button @click="activeTab = 'admin_reviews'" 
                            :class="activeTab === 'admin_reviews' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                        Správa recenzí
                    </button>

                    <button @click="activeTab = 'admin_users'" 
                            :class="activeTab === 'admin_users' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                        Správa uživatelů
                    </button>

                    <button @click="activeTab = 'admin_pubs'" 
                            :class="activeTab === 'admin_pubs' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                        Správa podniků
                    </button>
                @endif
            </div>
        </div>

        <!-- 1. NASTAVENÍ ÚČTU -->
        <div x-show="activeTab === 'settings'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">@livewire('profile.update-password-form')</div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">@livewire('profile.two-factor-authentication-form')</div>
                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">@livewire('profile.logout-other-browser-sessions-form')</div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />
                <div class="mt-10 sm:mt-0">@livewire('profile.delete-user-form')</div>
            @endif
        </div>

        <!-- 2. MOJE RECENZE (Běžný uživatel) -->
        <div x-show="activeTab === 'reviews'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" style="display: none;">
            <x-section-title>
                <x-slot name="title">Moje recenze</x-slot>
                <x-slot name="description">Historie vámi napsaných recenzí.</x-slot>
            </x-section-title>
            <div class="mt-6 space-y-6">
                @forelse(auth()->user()->reviews()->with('pub')->latest()->get() as $review)
                    <!-- Karta recenze -->
                    <div class="bg-black/75 border border-zluta shadow sm:rounded-lg p-6">
                        <div class="flex justify-between">
                            <h4 class="text-xl font-bold text-white">{{ $review->pub ? $review->pub->name : 'Neznámá' }}</h4>
                            <div class="flex items-center gap-2">
                                <div class="text-zluta">{{ str_repeat('★', $review->rating) }}</div>
                                <!-- Edit -->
                                <button @click="openEditModal({{ $review }})" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                <!-- Delete -->
                                <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Smazat?');">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                </form>
                            </div>
                        </div>
                        <p class="mt-2 text-gray-300 italic">"{{ $review->comment }}"</p>
                    </div>
                @empty
                    <div class="text-gray-400 text-center">Žádné recenze.</div>
                @endforelse
            </div>
        </div>

        <!-- 3. ADMIN: SPRÁVA RECENZÍ (Všechny recenze) -->
        @if(auth()->user()->isAdmin())
        <div x-show="activeTab === 'admin_reviews'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" style="display: none;">
            <x-section-title>
                <x-slot name="title">Všechny recenze</x-slot>
                <x-slot name="description">Administrátorský přehled všech recenzí v systému.</x-slot>
            </x-section-title>
            <div class="mt-6 space-y-4">
                <!-- Načteme úplně všechny recenze -->
                @foreach(\App\Models\Review::with(['user', 'pub'])->latest()->get() as $review)
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-start">
                        <div>
                            <div class="text-zluta font-bold">{{ $review->pub->name ?? 'Neznámá' }}</div>
                            <div class="text-sm text-gray-400">Autor: <span class="text-white">{{ $review->user->name ?? 'Smazaný' }}</span></div>
                            <div class="mt-1 text-white italic">"{{ $review->comment }}"</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('d.m.Y H:i') }} | Hodnocení: {{ $review->rating }}/5</div>
                        </div>
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('ADMIN: Opravdu smazat cizí recenzi?');">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 font-bold text-sm border border-red-500 px-3 py-1 rounded hover:bg-red-500 hover:text-black transition">
                                SMAZAT
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 4. ADMIN: SPRÁVA UŽIVATELŮ -->
        <div x-show="activeTab === 'admin_users'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" style="display: none;">
            <x-section-title>
                <x-slot name="title">Uživatelé</x-slot>
                <x-slot name="description">Seznam registrovaných uživatelů.</x-slot>
            </x-section-title>
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-400">
                    <thead class="bg-gray-800 text-zluta uppercase">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Jméno</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3 text-right">Akce</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach(\App\Models\User::all() as $user)
                            <tr class="hover:bg-gray-800">
                                <td class="px-4 py-3">{{ $user->id }}</td>
                                <td class="px-4 py-3 font-bold text-white">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    @if($user->isAdmin()) <span class="text-red-500 font-bold">Admin</span>
                                    @else User @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Smazat uživatele {{ $user->name }} a všechna jeho data?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 hover:underline">Smazat</button>
                                        </form>
                                    @else
                                        <span class="text-gray-600 italic">(Ty)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. ADMIN: SPRÁVA PODNIKŮ -->
        <div x-show="activeTab === 'admin_pubs'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" style="display: none;">
            <x-section-title>
                <x-slot name="title">Hospody</x-slot>
                <x-slot name="description">Seznam podniků v databázi.</x-slot>
            </x-section-title>
            <div class="mt-6 space-y-4">
                @foreach(\App\Models\Pub::all() as $pub)
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-center">
                        <div>
                            <div class="text-white font-bold text-lg">{{ $pub->name }}</div>
                            <div class="text-gray-500 text-sm">{{ $pub->street }}, {{ $pub->city }}</div>
                            <div class="text-gray-600 text-xs mt-1">Lat: {{ $pub->latitude }}, Lng: {{ $pub->longitude }}</div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Mazání podniku -->
                            <form method="POST" action="{{ route('admin.pubs.destroy', $pub) }}" onsubmit="return confirm('Smazat hospodu {{ $pub->name }}? Smažou se i všechny její recenze!');">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-white border border-red-500 hover:bg-red-600 px-3 py-1 rounded transition">
                                    SMAZAT
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- MODÁLNÍ OKNO PRO EDITACI (stejné jako předtím) -->
        <div x-show="editingReview" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: none;">
            <div class="fixed inset-0 transform transition-all" x-show="editingReview" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <div class="mb-6 bg-black border border-zluta rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg sm:mx-auto" x-show="editingReview">
                <div class="px-6 py-4">
                    <div class="text-lg font-medium text-zluta">Upravit recenzi</div>
                    <div class="mt-4">
                        <div class="flex justify-center space-x-2 mb-4">
                            <template x-for="i in 5">
                                <button @click="setEditRating(i)" class="focus:outline-none transition transform hover:scale-110" :class="i <= editForm.rating ? 'text-zluta' : 'text-gray-600'">
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                            </template>
                        </div>
                        <textarea x-model="editForm.comment" rows="5" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-3 focus:border-zluta focus:ring-1 focus:ring-zluta"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-900/50 text-right">
                    <button @click="closeEditModal()" class="mr-2 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Zrušit</button>
                    <button @click="saveReview()" :disabled="isSaving" class="px-4 py-2 bg-zluta text-black font-bold rounded hover:bg-yellow-500 disabled:opacity-50">
                        <span x-show="!isSaving">Uložit změny</span>
                        <span x-show="isSaving">Ukládám...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>