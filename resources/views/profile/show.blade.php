<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zluta leading-tight">
            {{ __('Můj Profil') }}
        </h2>
    </x-slot>

    <div x-data="{ 
        activeTab: localStorage.getItem('activeProfileTab') || 'settings',
        
        // --- 1. MOJE RECENZE (Načteno hned - je toho málo) ---
        myReviews: {{ Js::from(auth()->user()->reviews()->with('pub')->latest()->get()) }},
        searchMyReviews: '',
        sortMyReviews: 'newest',
        
        // --- 2. ADMIN DATA (Načítáme až na kliknutí) ---
        adminReviews: [],
        adminUsers: [],
        adminPubs: [],
        
        isLoading: false,
        loadedTabs: { admin_reviews: false, admin_users: false, admin_pubs: false },

        // Filtry pro admina
        adminFilters: { rating: '', pub: '', user: '', searchUser: '', searchPub: '' },

        // Formuláře a Modaly
        editingReview: null,
        editForm: { rating: 0, comment: '', pub_id: null },
        
        isPubModalOpen: false,
        isEditingPub: false,
        editingPubId: null,
        pubForm: { name: '', description: '', latitude: '', longitude: '', street: '', city: 'Plzeň' },
        
        viewingUser: null,
        isSaving: false,

        // --- PŘEPÍNÁNÍ TABŮ ---
        switchTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('activeProfileTab', tab);

            // Pokud je to admin tab a data ještě nemáme, stáhneme je
            if (['admin_reviews', 'admin_users', 'admin_pubs'].includes(tab) && !this.loadedTabs[tab]) {
                this.fetchAdminData(tab);
            }
        },

        // --- FETCH DATA (AJAX) ---
        async fetchAdminData(type) {
            this.isLoading = true;
            try {
                // Voláme API endpointy (viz AdminController níže)
                let url = '';
                if (type === 'admin_reviews') url = '{{ route('api.admin.reviews') }}';
                if (type === 'admin_users')   url = '{{ route('api.admin.users') }}';
                if (type === 'admin_pubs')    url = '{{ route('api.admin.pubs') }}';

                const response = await fetch(url);
                const data = await response.json();

                if (type === 'admin_reviews') this.adminReviews = data;
                if (type === 'admin_users')   this.adminUsers = data;
                if (type === 'admin_pubs')    this.adminPubs = data;

                this.loadedTabs[type] = true;
            } catch (e) {
                console.error(e);
                alert('Chyba při načítání dat.');
            } finally {
                this.isLoading = false;
            }
        },

        // --- UNIVERZÁLNÍ DELETE FUNKCE ---
        async deleteItem(id, type) {
            if (!confirm('Opravdu smazat?')) return;
            
            let url = '';
            let list = '';
            
            if (type === 'review') { url = '/reviews/' + id; list = 'myReviews'; } // Pro moje recenze
            if (type === 'admin_review') { url = '/reviews/' + id; list = 'adminReviews'; }
            if (type === 'pub') { url = '/admin/pubs/' + id; list = 'adminPubs'; }
            if (type === 'user') { url = '/admin/users/' + id; list = 'adminUsers'; }

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (res.ok) {
                    // Odstranění z pole v JS (bez reloadu stránky)
                    if (list === 'myReviews') this.myReviews = this.myReviews.filter(i => i.id !== id);
                    if (list === 'adminReviews') this.adminReviews = this.adminReviews.filter(i => i.id !== id);
                    if (list === 'adminPubs') this.adminPubs = this.adminPubs.filter(i => i.id !== id);
                    if (list === 'adminUsers') this.adminUsers = this.adminUsers.filter(i => i.id !== id);
                } else {
                    alert('Chyba při mazání.');
                }
            } catch(e) { alert('Chyba komunikace.'); }
        },

        // --- ADMIN: PŘEPNUTÍ ROLE ---
        async toggleRole(user) {
            if (!confirm('Změnit oprávnění uživatele ' + user.name + '?')) return;
            try {
                const res = await fetch('/admin/users/' + user.id + '/toggle-role', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (res.ok) {
                    // Aktualizujeme data v JS
                    user.is_admin = !user.is_admin;
                }
            } catch(e) { alert('Chyba.'); }
        },

        // --- UI LOGIKA ---
        get filteredMyReviews() {
            let result = this.myReviews;
            if (this.searchMyReviews) {
                const lower = this.searchMyReviews.toLowerCase();
                result = result.filter(r => r.pub && r.pub.name.toLowerCase().includes(lower));
            }
            if (this.sortMyReviews === 'newest') result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            if (this.sortMyReviews === 'oldest') result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            if (this.sortMyReviews === 'highest') result.sort((a, b) => b.rating - a.rating);
            if (this.sortMyReviews === 'lowest') result.sort((a, b) => a.rating - b.rating);
            return result;
        },

        formatDate(isoString) {
            if(!isoString) return '';
            const d = new Date(isoString);
            return d.toLocaleDateString('cs-CZ') + ' ' + d.toLocaleTimeString('cs-CZ', {hour: '2-digit', minute:'2-digit'});
        },

        // --- EDITACE RECENZE ---
        openEditModal(review) {
            this.editingReview = review;
            this.editForm = { rating: review.rating, comment: review.comment, pub_id: review.pub_id };
        },
        async saveReview() {
            this.isSaving = true;
            try {
                const response = await fetch('{{ route('reviews.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.editForm)
                });
                if (response.ok) { 
                    alert('Uloženo!'); window.location.reload(); 
                } 
            } catch (e) { alert('Chyba.'); } finally { this.isSaving = false; }
        },

        // --- SPRÁVA HOSPOD (ADMIN) ---
        openCreatePubModal() {
            this.isEditingPub = false; this.editingPubId = null;
            this.pubForm = { name: '', description: '', latitude: '', longitude: '', street: '', city: 'Plzeň' };
            this.isPubModalOpen = true;
        },
        openEditPubModal(pub) {
            this.isEditingPub = true; this.editingPubId = pub.id;
            this.pubForm = { ...pub }; // Zkopíruje data
            this.isPubModalOpen = true;
        },
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
                if (response.ok) { 
                    alert('Uloženo!'); 
                    this.isPubModalOpen = false;
                    this.fetchAdminData('admin_pubs'); // Obnoví seznam
                }
            } catch (e) { alert('Chyba.'); } finally { this.isSaving = false; }
        }
    }">
        
        <!-- NAVIGACE TABŮ -->
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

        <!-- 1. NASTAVENÍ (Standardní Blade) -->
        <div x-show="activeTab === 'settings'" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation()) @livewire('profile.update-profile-information-form') <x-section-border /> @endif
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords())) <div class="mt-10 sm:mt-0">@livewire('profile.update-password-form')</div> <x-section-border /> @endif
            <div class="mt-10 sm:mt-0">@livewire('profile.logout-other-browser-sessions-form')</div>
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures()) <x-section-border /> <div class="mt-10 sm:mt-0">@livewire('profile.delete-user-form')</div> @endif
        </div>

        <!-- 2. MOJE RECENZE -->
        <div x-show="activeTab === 'reviews'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title><x-slot name="title">Moje recenze</x-slot><x-slot name="description">Historie vašich hodnocení.</x-slot></x-section-title>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="text" x-model="searchMyReviews" placeholder="Hledat..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 text-sm">
                <select x-model="sortMyReviews" class="bg-gray-900 border border-gray-700 text-white rounded p-2 text-sm">
                    <option value="newest">Nejnovější</option><option value="highest">Nejlepší</option>
                </select>
            </div>

            <div class="mt-6 space-y-3">
                <template x-for="review in filteredMyReviews" :key="review.id">
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-start transition hover:bg-gray-800">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="text-lg font-bold text-zluta" x-text="review.pub ? review.pub.name : 'Neznámá'"></h4>
                                <div class="text-xs text-gray-500" x-text="formatDate(review.created_at)"></div>
                            </div>
                            <div class="text-sm text-gray-300 italic" x-text="review.comment"></div>
                        </div>
                        <div class="flex flex-col items-end gap-2 ml-4">
                            <div class="text-zluta font-bold" x-text="review.rating + '/5'"></div>
                            <div class="flex gap-2">
                                <button @click="openEditModal(review)" class="text-gray-400 hover:text-white border border-gray-600 px-2 py-1 rounded text-xs">Upravit</button>
                                <button @click="deleteItem(review.id, 'review')" class="text-red-500 hover:text-white border border-red-500 hover:bg-red-600 px-2 py-1 rounded text-xs">Smazat</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. ADMIN: VŠECHNY RECENZE -->
        <div x-show="activeTab === 'admin_reviews'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title><x-slot name="title">Admin: Recenze</x-slot><x-slot name="description">Přehled všech recenzí v systému.</x-slot></x-section-title>
            
            <div x-show="isLoading" class="text-zluta py-4 animate-pulse">Načítám data...</div>

            <div class="mt-4 mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                 <select x-model="adminFilters.rating" class="bg-gray-900 border border-gray-700 text-white rounded p-2"><option value="">Všechna hodnocení</option><option value="5">5</option><option value="1">1</option></select>
            </div>

            <div class="space-y-4">
                <template x-for="review in adminReviews" :key="review.id">
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-start"
                         x-show="adminFilters.rating === '' || review.rating == adminFilters.rating">
                        <div>
                            <div class="text-zluta font-bold" x-text="review.pub ? review.pub.name : 'Neznámý podnik'"></div>
                            <div class="text-sm text-gray-400">Autor: <span class="text-white" x-text="review.user ? review.user.name : 'Smazaný'"></span></div>
                            <div class="mt-1 text-white italic" x-text="review.comment"></div>
                            <div class="text-xs text-gray-500 mt-1" x-text="formatDate(review.created_at) + ' | ' + review.rating + '/5'"></div>
                        </div>
                        <button @click="deleteItem(review.id, 'admin_review')" class="text-red-500 border border-red-500 px-2 py-1 rounded hover:bg-red-500 hover:text-black transition text-xs font-bold">SMAZAT</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- 4. ADMIN: UŽIVATELÉ -->
        <div x-show="activeTab === 'admin_users'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <x-section-title><x-slot name="title">Uživatelé</x-slot><x-slot name="description">Správa uživatelských účtů.</x-slot></x-section-title>
            
            <div x-show="isLoading" class="text-zluta py-4 animate-pulse">Načítám data...</div>
            <input type="text" x-model="adminFilters.searchUser" placeholder="Hledat uživatele..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 mb-4">

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-400">
                    <thead class="bg-gray-800 text-zluta uppercase"><tr><th class="px-4 py-3">ID</th><th class="px-4 py-3">Jméno</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3 text-right">Akce</th></tr></thead>
                    <tbody class="divide-y divide-gray-700">
                        <template x-for="user in adminUsers" :key="user.id">
                            <tr class="hover:bg-gray-800" x-show="adminFilters.searchUser === '' || user.name.toLowerCase().includes(adminFilters.searchUser.toLowerCase())">
                                <td class="px-4 py-3" x-text="user.id"></td>
                                <td class="px-4 py-3 font-bold text-white" x-text="user.name"></td>
                                <td class="px-4 py-3" x-text="user.email"></td>
                                <td class="px-4 py-3">
                                    <span x-show="user.is_admin" class="text-red-500 font-bold">ADMIN</span>
                                    <span x-show="!user.is_admin">User</span>
                                </td>
                                <td class="px-4 py-3 text-right flex justify-end gap-2">
                                    <button @click="viewingUser = user" class="text-blue-400 border border-blue-400 px-2 py-1 rounded hover:bg-blue-600 hover:text-white">Detail</button>
                                    
                                    <template x-if="user.id !== {{ auth()->id() }}">
                                        <div class="flex gap-2">
                                            <button @click="toggleRole(user)" class="border px-2 py-1 rounded hover:text-white" :class="user.is_admin ? 'border-orange-500 text-orange-500 hover:bg-orange-500' : 'border-green-500 text-green-500 hover:bg-green-500'">
                                                <span x-text="user.is_admin ? '▼ User' : '▲ Admin'"></span>
                                            </button>
                                            <button @click="deleteItem(user.id, 'user')" class="text-red-500 border border-red-500 px-2 py-1 rounded hover:bg-red-500 hover:text-white">X</button>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. ADMIN: HOSPODY -->
        <div x-show="activeTab === 'admin_pubs'" style="display: none;" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <x-section-title><x-slot name="title">Hospody</x-slot><x-slot name="description">Databáze podniků.</x-slot></x-section-title>
                <button @click="openCreatePubModal()" class="bg-zluta text-black font-bold px-4 py-2 rounded hover:bg-yellow-500 shadow-lg">+ PŘIDAT</button>
            </div>
            
            <div x-show="isLoading" class="text-zluta py-4 animate-pulse">Načítám data...</div>
            <input type="text" x-model="adminFilters.searchPub" placeholder="Hledat podnik..." class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 mb-4">

            <div class="space-y-4">
                <template x-for="pub in adminPubs" :key="pub.id">
                    <div class="bg-gray-900 border border-gray-700 p-4 rounded flex justify-between items-center hover:bg-gray-800"
                         x-show="adminFilters.searchPub === '' || pub.name.toLowerCase().includes(adminFilters.searchPub.toLowerCase())">
                        <div>
                            <div class="text-white font-bold text-lg" x-text="pub.name"></div>
                            <div class="text-gray-500 text-sm" x-text="(pub.street || '') + ', ' + (pub.city || '')"></div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click="openEditPubModal(pub)" class="text-blue-400 border border-blue-400 px-3 py-1 rounded hover:bg-blue-600 hover:text-white">UPRAVIT</button>
                            <button @click="deleteItem(pub.id, 'pub')" class="text-red-500 border border-red-500 px-3 py-1 rounded hover:bg-red-600 hover:text-white">SMAZAT</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODALY (Editace, Detail Usera, Pub Form) -->
        <div x-show="editingReview" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 bg-black/80 flex items-center justify-center" style="display: none;">
            <div class="bg-black border border-zluta rounded-lg p-6 w-full max-w-lg shadow-2xl">
                <h3 class="text-lg font-medium text-zluta mb-4">Upravit recenzi</h3>
                <div class="flex justify-center space-x-2 mb-4">
                    <template x-for="i in 5"><button @click="editForm.rating = i" class="text-2xl" :class="i <= editForm.rating ? 'text-zluta' : 'text-gray-600'">★</button></template>
                </div>
                <textarea x-model="editForm.comment" rows="4" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-3 mb-4"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="editingReview = null" class="px-4 py-2 bg-gray-700 text-white rounded">Zrušit</button>
                    <button @click="saveReview()" class="px-4 py-2 bg-zluta text-black font-bold rounded">Uložit</button>
                </div>
            </div>
        </div>
        
        <!-- Zde případně další modaly pro PubForm a UserDetail (zkráceno pro přehlednost, princip je stejný) -->
        <div x-show="isPubModalOpen" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 bg-black/80 flex items-center justify-center" style="display: none;">
             <div class="bg-black border border-zluta rounded-lg p-6 w-full max-w-lg shadow-2xl">
                <h3 class="text-lg font-medium text-zluta mb-4" x-text="isEditingPub ? 'Upravit podnik' : 'Nový podnik'"></h3>
                <div class="space-y-3">
                    <input type="text" x-model="pubForm.name" placeholder="Název" class="w-full bg-gray-900 border-gray-700 text-white rounded">
                    <input type="text" x-model="pubForm.street" placeholder="Ulice" class="w-full bg-gray-900 border-gray-700 text-white rounded">
                    <div class="flex gap-2">
                        <input type="text" x-model="pubForm.latitude" placeholder="Lat" class="w-full bg-gray-900 border-gray-700 text-white rounded">
                        <input type="text" x-model="pubForm.longitude" placeholder="Lng" class="w-full bg-gray-900 border-gray-700 text-white rounded">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="isPubModalOpen = false" class="px-4 py-2 bg-gray-700 text-white rounded">Zrušit</button>
                    <button @click="savePub()" class="px-4 py-2 bg-zluta text-black font-bold rounded">Uložit</button>
                </div>
             </div>
        </div>

    </div>
</x-app-layout>