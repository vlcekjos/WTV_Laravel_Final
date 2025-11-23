<x-app-layout>
    @section('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            /* Tmavý styl mapy */
            .leaflet-layer,
            .leaflet-control-zoom-in,
            .leaflet-control-zoom-out,
            .leaflet-control-attribution {
                filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
            }
            
            /* ZMĚNA ZDE: Fix pro mapové dlaždice A PŘIDÁNÍ STÍNU LOGU */
            img.leaflet-marker-icon {
                /*
                  invert(0%) - zajistí, že se logo nepřebarví do negativu kvůli tmavému režimu
                  drop-shadow() - přidá stín kopírující tvar půllitru.
                  Hodnoty: posun X, posun Y, rozostření, barva (černá s 60% průhledností)
                */
                filter: invert(0%) drop-shadow(0 4px 8px rgba(0, 0, 0, 0.6));
                transition: filter 0.3s ease; /* Jemný přechod při změně */
            }
            
            /* Volitelné: Zvýraznění markeru při najetí myší */
            img.leaflet-marker-icon:hover {
                 filter: invert(0%) drop-shadow(0 6px 12px rgba(234, 179, 8, 0.7)); /* Žlutý stín při hoveru */
            }

            /* Animace hvězdiček ve formuláři */
            .star-hover:hover { transform: scale(1.2); }

            /* Styl pro Tooltip (Bublina nad hospodou) */
            .custom-tooltip {
                background-color: rgba(0, 0, 0, 0.9) !important;
                border: 1px solid #EAB308 !important;
                color: #fff !important;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
                font-family: 'Figtree', sans-serif;
                padding: 0;
            }
            .leaflet-tooltip-top:before { border-top-color: #EAB308 !important; }
            .leaflet-tooltip-bottom:before { border-bottom-color: #EAB308 !important; }
        </style>
    @endsection

    <div id="map-component" 
         x-data="{ 
            selectedPub: null, 
            isWritingReview: false,
            isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
            currentUserId: {{ auth()->id() ?? 'null' }},
            reviewForm: { rating: 0, comment: '' },
            isLoading: false,
            
            resetForm() {
                this.isWritingReview = false;
                this.reviewForm = { rating: 0, comment: '' };
                this.isLoading = false;
            },

            setRating(val) {
                this.reviewForm.rating = val;
            },

            get averageRating() {
                if (!this.selectedPub || !this.selectedPub.reviews || this.selectedPub.reviews.length === 0) return 0;
                let sum = this.selectedPub.reviews.reduce((a, b) => a + b.rating, 0);
                return (sum / this.selectedPub.reviews.length).toFixed(1);
            },

            get myReview() {
                if (!this.isLoggedIn || !this.selectedPub || !this.selectedPub.reviews) return null;
                return this.selectedPub.reviews.find(r => r.user_id === this.currentUserId);
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('cs-CZ');
            },

            openReviewForm() {
                if (this.myReview) {
                    this.reviewForm.rating = this.myReview.rating;
                    this.reviewForm.comment = this.myReview.comment;
                } else {
                    this.reviewForm = { rating: 0, comment: '' };
                }
                this.isWritingReview = true;
            },

            async submitReview() {
                if (!this.isLoggedIn) {
                    if(confirm('Pro vložení recenze se musíte přihlásit. Přejít na přihlášení?')) {
                        window.location.href = '{{ route('login') }}';
                    }
                    return;
                }

                if (this.reviewForm.rating === 0) {
                    alert('Prosím vyberte počet hvězdiček.');
                    return;
                }

                this.isLoading = true;

                try {
                    const response = await fetch('{{ route('reviews.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            pub_id: this.selectedPub.id,
                            rating: this.reviewForm.rating,
                            comment: this.reviewForm.comment
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('Recenze byla úspěšně uložena!');
                        window.location.reload();
                    } else {
                        alert('Chyba: ' + (data.message || 'Něco se pokazilo.'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Došlo k chybě při komunikaci se serverem.');
                } finally {
                    this.isLoading = false;
                }
            }
         }" 
         @pub-selected.window="selectedPub = $event.detail; resetForm(); resizeMap()"
         class="relative flex flex-col h-[calc(100vh-64px)]"> 
        
        <div class="flex-1 flex overflow-hidden relative">
            
            <!-- LEVÝ PANEL -->
            <div x-show="selectedPub" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full opacity-0" 
                 x-transition:enter-end="translate-x-0 opacity-100"
                 class="w-full lg:w-2/5 bg-black/90 border-r border-zluta p-6 overflow-y-auto absolute lg:relative left-0 h-full z-20 shadow-2xl"
                 style="display: none;">
                
                <!-- 1. DETAIL HOSPODY -->
                <div x-show="!isWritingReview" x-transition:enter="transition ease-in duration-200 delay-100">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-3xl font-bold text-zluta" x-text="selectedPub?.name"></h2>
                        <button @click="selectedPub = null; resizeMap()" class="text-gray-400 hover:text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="flex items-center mb-4 text-zluta">
                        <template x-for="i in 5">
                            <svg class="w-6 h-6" :class="i <= Math.round(averageRating) ? 'fill-current' : 'text-gray-600 fill-current'" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </template>
                        <span class="ml-2 text-white" x-text="'(' + averageRating + ')'"></span>
                    </div>

                    <p class="text-gray-300 mb-8" x-text="selectedPub?.description"></p>

                    <button @click="openReviewForm()" class="w-full py-3 bg-zluta text-black font-bold rounded hover:bg-yellow-500 transition mb-4 uppercase tracking-wider">
                        <span x-text="myReview ? 'UPRAVIT MOU RECENZI' : 'PŘIDAT RECENZI'"></span>
                    </button>
                    
                    <template x-if="myReview">
                        <p class="text-center text-sm text-gray-400 mb-8 italic">
                            Tuto hospodu jste již hodnotili (<span x-text="myReview.rating"></span>/5).
                        </p>
                    </template>
                    <template x-if="!myReview">
                        <div class="mb-8"></div>
                    </template>

                    <div class="space-y-6">
                        <h3 class="text-xl text-white font-semibold border-b border-gray-700 pb-2">
                            Recenze uživatelů (<span x-text="selectedPub?.reviews ? selectedPub.reviews.length : 0"></span>)
                        </h3>
                        
                        <template x-if="!selectedPub?.reviews || selectedPub.reviews.length === 0">
                            <p class="text-gray-500 italic">Zatím žádné recenze. Buďte první!</p>
                        </template>

                        <template x-for="review in selectedPub?.reviews" :key="review.id">
                            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                                <div class="flex justify-between text-sm text-gray-400 mb-2">
                                    <div>
                                        <span class="font-bold text-white" x-text="review.user ? review.user.name : 'Neznámý uživatel'"></span>
                                        <template x-if="review.user_id === currentUserId">
                                            <span class="ml-2 text-xs bg-zluta text-black px-1 rounded">Já</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="flex text-zluta text-xs">
                                            <template x-for="i in 5">
                                                <span x-text="i <= review.rating ? '★' : '☆'"></span>
                                            </template>
                                        </div>
                                        <span class="text-xs text-gray-500" x-text="formatDate(review.created_at)"></span>
                                    </div>
                                </div>
                                <p class="text-gray-300 text-sm" x-text="review.comment"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2. FORMULÁŘ PRO RECENZI -->
                <div x-show="isWritingReview" x-transition:enter="transition ease-in duration-200">
                    <div class="mb-8 text-center border-b border-gray-800 pb-4">
                        <h2 class="text-2xl font-bold text-white mb-1" x-text="myReview ? 'Úprava recenze' : 'Nová recenze'"></h2>
                        <p class="text-zluta text-sm" x-text="selectedPub?.name"></p>
                    </div>

                    <div class="mb-8">
                        <label class="block text-gray-400 text-sm mb-3">Kolik hvězd udělíš?</label>
                        <div class="flex items-center justify-center space-x-2">
                            <template x-for="i in 5">
                                <button @click="setRating(i)" 
                                        class="focus:outline-none transition transform star-hover"
                                        :class="i <= reviewForm.rating ? 'text-zluta' : 'text-gray-600'">
                                    <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-gray-400 text-sm mb-2">Text recenze</label>
                        <textarea x-model="reviewForm.comment" rows="6" class="w-full bg-black border border-gray-700 text-white rounded p-3 focus:border-zluta focus:ring-1 focus:ring-zluta" placeholder="Napiš nám, jak ti chutnalo..."></textarea>
                    </div>

                    <div class="flex space-x-4">
                        <button @click="isWritingReview = false" class="w-1/2 py-3 bg-white text-black font-bold rounded hover:bg-gray-200 transition">Zrušit</button>
                        <button @click="submitReview()" :disabled="isLoading" class="w-1/2 py-3 bg-zluta text-black font-bold rounded hover:bg-yellow-500 transition shadow-[0_0_15px_rgba(234,179,8,0.3)] disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isLoading">Uložit</span>
                            <span x-show="isLoading">Odesílám...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MAPA -->
            <div :class="selectedPub ? 'w-full lg:w-3/5' : 'w-full'" class="w-full transition-all duration-500 ease-in-out relative bg-gray-900">
                <div id="map" class="w-full h-full z-10"></div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map').setView([49.7475, 13.3776], 14);
                setTimeout(() => { map.invalidateSize(); }, 200);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

                // --- DEFINICE IKONY ---
                var beerIcon = L.icon({
                    iconUrl: '{{ asset("images/logo.png") }}', // Cesta k logu
                    iconSize:     [32, 32], // Velikost ikony
                    iconAnchor:   [16, 32], // Bod ikony, který ukazuje na souřadnice (střed dole)
                    tooltipAnchor:[0, -32]  // Kde se zobrazí tooltip (nad ikonou)
                });

                const pubs = @json($pubs);

                pubs.forEach(pub => {
                    if (pub.latitude && pub.longitude) {
                        // Použití vlastní ikony
                        var marker = L.marker([pub.latitude, pub.longitude], { icon: beerIcon }).addTo(map);
                        
                        // Tooltip logika
                        let avgRating = 0;
                        if (pub.reviews && pub.reviews.length > 0) {
                            let sum = pub.reviews.reduce((a, b) => a + b.rating, 0);
                            avgRating = Math.round(sum / pub.reviews.length);
                        }
                        let stars = '★'.repeat(avgRating) + '☆'.repeat(5 - avgRating);
                        
                        marker.bindTooltip(`
                            <div class="p-2 text-center">
                                <div class="font-bold text-white text-sm mb-1">${pub.name}</div>
                                <div class="text-zluta text-xs tracking-widest">${stars}</div>
                            </div>
                        `, {
                            permanent: false,
                            direction: 'top',
                            className: 'custom-tooltip',
                            opacity: 1,
                            offset: [0, -5]
                        });

                        marker.on('click', function() {
                            window.dispatchEvent(new CustomEvent('pub-selected', { detail: pub }));
                            map.setView([pub.latitude, pub.longitude], 15);
                        });
                    }
                });

                window.resizeMap = function() { setTimeout(() => { map.invalidateSize(); }, 600); };
            });
        </script>
    @endsection
</x-app-layout>