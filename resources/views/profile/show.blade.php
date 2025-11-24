<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zluta leading-tight">
            {{ __('Můj Profil') }}
        </h2>
    </x-slot>

    <div x-data="{ 
        activeTab: localStorage.getItem('activeProfileTab') || 'settings',
        
        // --- DATA PRO MOJE RECENZE (Načteno z DB do JS pro filtrování) ---
        myReviews: {{ Js::from(auth()->user()->reviews()->with('pub')->latest()->get()) }},
        searchMyReviews: '',
        sortMyReviews: 'newest', // newest, oldest, highest, lowest

        // Editace
        editingReview: null,
        editForm: { rating: 0, comment: '', pub_id: null },
        
        // Admin data
        viewingUser: null,
        searchUser: '',
        searchPub: '',

        // Hospody
        isPubModalOpen: false,
        isEditingPub: false,
        editingPubId: null,
        pubForm: { name: '', description: '', latitude: '', longitude: '', street: '', city: 'Plzeň' },

        isSaving: false,

        switchTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('activeProfileTab', tab);
        },

        // --- COMPUTED LOGIKA PRO FILTROVÁNÍ RECENZÍ ---
        get filteredMyReviews() {
            let result = this.myReviews;

            // 1. Hledání
            if (this.searchMyReviews) {
                const lower = this.searchMyReviews.toLowerCase();
                result = result.filter(r => r.pub && r.pub.name.toLowerCase().includes(lower));
            }

            // 2. Řazení
            if (this.sortMyReviews === 'newest') {
                result = result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else if (this.sortMyReviews === 'oldest') {
                result = result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            } else if (this.sortMyReviews === 'highest') {
                result = result.sort((a, b) => b.rating - a.rating);
            } else if (this.sortMyReviews === 'lowest') {
                result = result.sort((a, b) => a.rating - b.rating);
            }

            return result;
        },

        // Format data helper
        formatDate(isoString) {
            const d = new Date(isoString);
            return d.toLocaleDateString('cs-CZ') + ' ' + d.toLocaleTimeString('cs-CZ', {hour: '2-digit', minute:'2-digit'});
        },

        // --- METODY PRO RECENZE ---
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
        },

        // --- OSTATNÍ METODY ---
        openUserDetail(user) { this.viewingUser = user; },
        closeUserDetail() { this.viewingUser = null; },

        openCreatePubModal() {
            this.isEditingPub = false;
            this.editingPubId = null;
            this.pubForm = { name: '', description: '', latitude: '', longitude: '', street: '', city: 'Plzeň' };
            this.isPubModalOpen = true;
        },
        openEditPubModal(pub) {
            this.isEditingPub = true;
            this.editingPubId = pub.id;
            this.pubForm = { name: pub.name, description: pub.description, latitude: pub.latitude, longitude: pub.longitude, street: pub.street, city: pub.city };
            this.isPubModalOpen = true;
        },
        closePubModal() { this.isPubModalOpen = false; },

        async savePub() {
            this.isSaving = true;
            const url = this.isEditingPub ? '/admin/pubs/' + this.editingPubId : '{{ route('admin.pubs.store') }}';
            const method = this.isEditingPub ? 'PUT' : 'POST';
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.pubForm)
                });
                if (response.ok) { alert(this.isEditingPub ? 'Hospoda upravena!' : 'Hospoda přidána!'); window.location.reload(); } 
                else { alert('Chyba při ukládání.'); }
            } catch (e) { alert('Chyba komunikace.'); } 
            finally { this.isSaving = false; }
        }
    }">
        
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-700 flex space-x-8 overflow-x-auto">
                <button @click="switchTab('settings')" :class="activeTab === 'settings' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">Nastavení účtu</button>
                <button @click="switchTab('reviews')" :class="activeTab === 'reviews' ? 'border-zluta text-zluta' : 'border-transparent text-gray-400 hover:text-gray-200'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">Moje recenze</button>

                @if(auth()->user()->isAdmin())
                    <div class="border-l border-gray-700 mx-4 h-6 self-center"></div>
                    <button @click="switchTab('admin_reviews')" :class="activeTab === 'admin_reviews' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">Správa recenzí</button>
                    <button @click="switchTab('admin_users')" :class="activeTab === 'admin_users' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">Správa uživatelů</button>
                    <button @click="switchTab('admin_pubs')" :class="activeTab === 'admin_pubs' ? 'border-red-500 text-red-500' : 'border-transparent text-gray-400 hover:text-red-400'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">Správa podniků</button>
                @endif
            </div>
        </div>

        <div x-show="activeTab === 'settings'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation()) @livewire('profile.update-profile-information-form') <x-section-border /> @endif
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords())) <div class="mt-10 sm:mt-0">@livewire('profile.update-password-form')</div> <x-section-border /> @endif
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()) <div class="mt-10 sm:mt-0">@livewire('profile.two-factor-authentication-form')</div> <x-section-border /> @endif
            <div class="mt-10 sm:mt-0">@livewire('profile.logout-other-browser-sessions-form')</div>
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures()) <x-section-border /> <div class="mt-10 sm:mt-0">@livewire('profile.delete-user-form')</div> @endif
        </div>

        <div x-show="activeTab === 'reviews'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title>
                <x-slot name="title">Moje recenze</x-slot>
                <x-slot name="description">Historie vašich recenzí.</x-slot>
            </x-section-title>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="text" x-model="searchMyReviews" placeholder="Hledat podle názvu hospody..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 text-sm">
                <select x-model="sortMyReviews" class="bg-gray-900 border border-gray-700 text-white rounded p-2 text-sm">
                    <option value="newest">Nejnovější</option>
                    <option value="oldest">Nejstarší</option>
                    <option value="highest">Nejlepší hodnocení</option>
                    <option value="lowest">Nejhorší hodnocení</option>
                </select>
            </div>

            <div class="mt-6 space-y-3">
                <template x-for="review in filteredMyReviews" :key="review.id">
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-start transition hover:bg-gray-800 hover:border-gray-500 group">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="text-lg font-bold text-zluta" x-text="review.pub ? review.pub.name : 'Neznámá'"></h4>
                                <div class="text-xs text-gray-500" x-text="formatDate(review.created_at)"></div>
                            </div>
                            <div class="text-sm text-gray-300 italic leading-snug" x-text="'\u0022' + review.comment + '\u0022'"></div>
                        </div>
                        
                        <div class="flex flex-col items-end gap-2 ml-4">
                            <div class="flex text-zluta text-sm">
                                <template x-for="i in 5">
                                    <span x-text="i <= review.rating ? '★' : '☆'"></span>
                                </template>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal(review)" class="text-gray-400 hover:text-white bg-gray-800 border border-gray-600 hover:bg-gray-700 p-1 rounded transition" title="Upravit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <button @click="if(confirm('Smazat?')) { 
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '/reviews/' + review.id;
                                    const csrf = document.createElement('input');
                                    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                                    const method = document.createElement('input');
                                    method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
                                    form.appendChild(csrf); form.appendChild(method);
                                    document.body.appendChild(form);
                                    form.submit();
                                }" class="text-red-500 hover:text-red-400 bg-gray-800 border border-gray-600 hover:bg-gray-700 p-1 rounded transition" title="Smazat">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template x-if="filteredMyReviews.length === 0">
                    <div class="text-gray-400 text-center py-4">Žádné recenze neodpovídají filtru.</div>
                </template>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
        <div x-show="activeTab === 'admin_reviews'" style="display: none;" x-data="{ filterRating: '', filterPub: '', filterUser: '' }" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title><x-slot name="title">Všechny recenze</x-slot><x-slot name="description">Administrátorský přehled recenzí.</x-slot></x-section-title>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <select x-model="filterRating" class="bg-gray-900 border border-gray-700 text-white rounded p-2"><option value="">Všechna hodnocení</option><option value="5">5 Hvězd</option><option value="4">4 Hvězdy</option><option value="3">3 Hvězdy</option><option value="2">2 Hvězdy</option><option value="1">1 Hvězda</option></select>
                <select x-model="filterPub" class="bg-gray-900 border border-gray-700 text-white rounded p-2"><option value="">Všechny podniky</option>@foreach(\App\Models\Pub::all() as $pub)<option value="{{ $pub->id }}">{{ $pub->name }}</option>@endforeach</select>
                <select x-model="filterUser" class="bg-gray-900 border border-gray-700 text-white rounded p-2"><option value="">Všichni uživatelé</option>@foreach(\App\Models\User::all() as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
            </div>
            <div class="space-y-4">
                @foreach(\App\Models\Review::with(['user', 'pub'])->latest()->get() as $review)
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-start transition hover:bg-gray-800 hover:border-gray-500"
                         x-show="(filterRating === '' || '{{ $review->rating }}' == filterRating) && (filterPub === '' || '{{ $review->pub_id }}' == filterPub) && (filterUser === '' || '{{ $review->user_id }}' == filterUser)">
                        <div>
                            <div class="text-zluta font-bold">{{ $review->pub->name ?? 'Neznámá' }}</div>
                            <div class="text-sm text-gray-400">Autor: <span class="text-white">{{ $review->user->name ?? 'Smazaný' }}</span></div>
                            <div class="mt-1 text-white italic">"{{ $review->comment }}"</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('d.m.Y H:i') }} | {{ $review->rating }}/5</div>
                        </div>
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('ADMIN: Opravdu smazat?');"> @csrf @method('DELETE') <button class="text-red-500 hover:text-red-700 font-bold text-sm border border-red-500 px-3 py-1 rounded hover:bg-red-500 hover:text-black transition">SMAZAT</button> </form>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="activeTab === 'admin_users'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title><x-slot name="title">Uživatelé</x-slot><x-slot name="description">Seznam uživatelů.</x-slot></x-section-title>
            <div class="mt-4 mb-4">
                <input type="text" x-model="searchUser" placeholder="Hledat uživatele..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2">
            </div>
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-400">
                    <thead class="bg-gray-800 text-zluta uppercase"><tr><th class="px-4 py-3">ID</th><th class="px-4 py-3">Jméno</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3 text-right">Akce</th></tr></thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach(\App\Models\User::all() as $user)
                            <tr class="transition hover:bg-gray-800" x-show="searchUser === '' || '{{ strtolower($user->name) }}'.includes(searchUser.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(searchUser.toLowerCase())">
                                <td class="px-4 py-3">{{ $user->id }}</td>
                                <td class="px-4 py-3 font-bold text-white">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">@if($user->isAdmin()) <span class="text-red-500 font-bold">Admin</span> @else User @endif</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openUserDetail({{ $user }})" class="bg-gray-700 text-blue-400 hover:bg-blue-600 hover:text-white p-2 rounded transition" title="Detail"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></button>
                                        
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-role', $user) }}" onsubmit="return confirm('Opravdu změnit oprávnění tohoto uživatele?');"> 
                                                @csrf @method('PUT') 
                                                @if($user->isAdmin())
                                                    <button class="bg-gray-700 text-orange-500 hover:bg-orange-500 hover:text-white p-2 rounded transition" title="Odebrat Admin práva">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" opacity="0.5"/> 
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 4.5-15 15" class="text-red-500 font-bold" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button class="bg-gray-700 text-green-500 hover:bg-green-600 hover:text-white p-2 rounded transition" title="Udělit Admin práva">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </form>
                                        @else 
                                            <span class="text-gray-500 italic px-2 text-xs">Ty</span> 
                                        @endif

                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Smazat?');"> @csrf @method('DELETE') <button class="bg-gray-700 text-red-500 hover:bg-red-500 hover:text-white p-2 rounded transition" title="Smazat uživatele"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button> </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="activeTab === 'admin_pubs'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <x-section-title><x-slot name="title">Hospody</x-slot><x-slot name="description">Seznam podniků.</x-slot></x-section-title>
                <button @click="openCreatePubModal()" class="bg-zluta text-black font-bold px-4 py-2 rounded hover:bg-yellow-500 shadow-lg transition transform hover:scale-105">+ PŘIDAT PODNIK</button>
            </div>
            <div class="mt-4 mb-4">
                <input type="text" x-model="searchPub" placeholder="Hledat podnik..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2">
            </div>
            <div class="mt-6 space-y-4">
                @foreach(\App\Models\Pub::all() as $pub)
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-center transition hover:bg-gray-800 hover:border-gray-500"
                         x-show="searchPub === '' || '{{ strtolower($pub->name) }}'.includes(searchPub.toLowerCase()) || '{{ strtolower($pub->street) }}'.includes(searchPub.toLowerCase()) || '{{ strtolower($pub->city) }}'.includes(searchPub.toLowerCase())">
                        <div>
                            <div class="text-white font-bold text-lg">{{ $pub->name }}</div>
                            <div class="text-gray-500 text-sm">{{ $pub->street }}, {{ $pub->city }}</div>
                            <div class="text-gray-600 text-xs mt-1">GPS: {{ $pub->latitude }}, {{ $pub->longitude }}</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click="openEditPubModal({{ $pub }})" class="text-blue-400 hover:text-white border border-blue-400 hover:bg-blue-600 px-3 py-1 rounded transition">UPRAVIT</button>
                            <form method="POST" action="{{ route('admin.pubs.destroy', $pub) }}" onsubmit="return confirm('Smazat hospodu {{ $pub->name }}?');"> @csrf @method('DELETE') <button class="text-red-500 hover:text-white border border-red-500 hover:bg-red-600 px-3 py-1 rounded transition">SMAZAT</button> </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div x-show="editingReview" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" style="display: none;">
            <div class="fixed inset-0 transform transition-all" x-show="editingReview" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"><div class="absolute inset-0 bg-gray-900 opacity-75"></div></div>
            <div class="mb-6 bg-black border border-zluta rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg sm:mx-auto" x-show="editingReview">
                <div class="px-6 py-4"><div class="text-lg font-medium text-zluta">Upravit recenzi</div><div class="mt-4"><div class="flex justify-center space-x-2 mb-4"><template x-for="i in 5"><button @click="setEditRating(i)" class="focus:outline-none transition transform hover:scale-110" :class="i <= editForm.rating ? 'text-zluta' : 'text-gray-600'"><svg class="w-8 h-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></button></template></div><textarea x-model="editForm.comment" rows="5" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-3 focus:border-zluta"></textarea></div></div>
                <div class="px-6 py-4 bg-gray-900/50 text-right"><button @click="closeEditModal()" class="mr-2 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Zrušit</button><button @click="saveReview()" :disabled="isSaving" class="px-4 py-2 bg-zluta text-black font-bold rounded hover:bg-yellow-500">Uložit</button></div>
            </div>
        </div>

        <div x-show="viewingUser" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" style="display: none;">
            <div class="fixed inset-0 transform transition-all" x-show="viewingUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"><div class="absolute inset-0 bg-gray-900 opacity-75"></div></div>
            <div class="mb-6 bg-black border border-zluta rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-md sm:mx-auto" x-show="viewingUser">
                <div class="px-6 py-4"><h3 class="text-lg font-medium text-zluta mb-4">Detail uživatele</h3><div class="space-y-3 text-sm text-gray-300"><p><strong>ID:</strong> <span x-text="viewingUser?.id"></span></p><p><strong>Jméno:</strong> <span x-text="viewingUser?.name" class="text-white font-bold"></span></p><p><strong>Email:</strong> <span x-text="viewingUser?.email"></span></p><p><strong>Role:</strong> <span x-text="viewingUser?.is_admin ? 'ADMIN' : 'Uživatel'" :class="viewingUser?.is_admin ? 'text-red-500 font-bold' : ''"></span></p><p><strong>Vytvořen:</strong> <span x-text="new Date(viewingUser?.created_at).toLocaleDateString('cs-CZ')"></span></p></div></div>
                <div class="px-6 py-4 bg-gray-900/50 text-right"><button @click="closeUserDetail()" class="px-4 py-2 bg-zluta text-black font-bold rounded hover:bg-yellow-500">Zavřít</button></div>
            </div>
        </div>

        <div x-show="isPubModalOpen" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" style="display: none;">
            <div class="fixed inset-0 transform transition-all" x-show="isPubModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"><div class="absolute inset-0 bg-gray-900 opacity-75"></div></div>
            <div class="mb-6 bg-black border border-zluta rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg sm:mx-auto" x-show="isPubModalOpen">
                <div class="px-6 py-4">
                    <h3 class="text-lg font-medium text-zluta mb-4" x-text="isEditingPub ? 'Upravit podnik' : 'Nový podnik'"></h3>
                    <div class="space-y-4">
                        <div><label class="text-gray-400 text-xs block">Název</label><input type="text" x-model="pubForm.name" class="w-full bg-gray-900 border-gray-700 text-white rounded"></div>
                        <div><label class="text-gray-400 text-xs block">Popis</label><textarea x-model="pubForm.description" class="w-full bg-gray-900 border-gray-700 text-white rounded"></textarea></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><label class="text-gray-400 text-xs block">Lat</label><input type="text" x-model="pubForm.latitude" class="w-full bg-gray-900 border-gray-700 text-white rounded"></div>
                            <div><label class="text-gray-400 text-xs block">Lng</label><input type="text" x-model="pubForm.longitude" class="w-full bg-gray-900 border-gray-700 text-white rounded"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><label class="text-gray-400 text-xs block">Ulice</label><input type="text" x-model="pubForm.street" class="w-full bg-gray-900 border-gray-700 text-white rounded"></div>
                            <div><label class="text-gray-400 text-xs block">Město</label><input type="text" x-model="pubForm.city" class="w-full bg-gray-900 border-gray-700 text-white rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-900/50 text-right">
                    <button @click="closePubModal()" class="mr-2 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Zrušit</button>
                    <button @click="savePub()" :disabled="isSaving" class="px-4 py-2 bg-zluta text-black font-bold rounded hover:bg-yellow-500">Uložit</button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>