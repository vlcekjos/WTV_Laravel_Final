<x-app-layout>
    @section('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

        <style>
            /* Tmavý styl mapy */
            .leaflet-layer,
            .leaflet-control-zoom-in,
            .leaflet-control-zoom-out,
            .leaflet-control-attribution {
                filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
            }
            
            /* Ikony a stíny */
            img.leaflet-marker-icon {
                filter: invert(0%) drop-shadow(0 4px 8px rgba(0, 0, 0, 0.6));
                transition: filter 0.3s ease;
            }
            img.leaflet-marker-icon:hover {
                 filter: invert(0%) drop-shadow(0 6px 12px rgba(234, 179, 8, 0.7));
            }

            .star-hover:hover { transform: scale(1.2); }

            /* Tooltipy */
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

            /* NOVÉ: Styl pro Clustery (shluky) v tmavém režimu */
            .marker-cluster-small, .marker-cluster-medium, .marker-cluster-large {
                background-color: rgba(234, 179, 8, 0.6) !important; /* Žlutá průhledná */
            }
            .marker-cluster div {
                background-color: rgba(0, 0, 0, 0.8) !important; /* Černý střed */
                color: #EAB308 !important; /* Žluté číslo */
                font-weight: bold;
            }
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
            
            <div x-show="selectedPub" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full opacity-0" 
                 x-transition:enter-end="translate-x-0 opacity-100"
                 class="w-full lg:w-2/5 bg-black/90 border-r border-zluta p-6 overflow-y-auto absolute lg:relative left-0 h-full z-20 shadow-2xl"
                 style="display: none;">
                
                <div x-show="!isWritingReview">
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
                    
                    <h3 class="text-xl text-white font-semibold border-b border-gray-700 pb-2 mt-6">
                        Recenze uživatelů (<span x-text="selectedPub?.reviews ? selectedPub.reviews.length : 0"></span>)
                    </h3>
                    <div class="space-y-4 mt-4">
                         <template x-for="review in selectedPub?.reviews" :key="review.id">
                            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                                <div class="flex justify-between text-sm text-gray-400 mb-2">
                                    <span class="font-bold text-white" x-text="review.user ? review.user.name : 'Neznámý'"></span>
                                    <span class="text-zluta" x-text="review.rating + '/5'"></span>
                                </div>
                                <p class="text-gray-300 text-sm" x-text="review.comment"></p>
                            </div>
                         </template>
                    </div>
                </div>

                <div x-show="isWritingReview">
                     <h2 class="text-2xl font-bold text-white mb-4">Vaše recenze</h2>
                     <div class="flex mb-4">
                        <template x-for="i in 5">
                            <button @click="setRating(i)" class="text-2xl" :class="i <= reviewForm.rating ? 'text-zluta' : 'text-gray-600'">★</button>
                        </template>
                     </div>
                     <textarea x-model="reviewForm.comment" class="w-full bg-black border border-gray-700 text-white rounded p-3" rows="4"></textarea>
                     <div class="flex space-x-4 mt-4">
                        <button @click="isWritingReview = false" class="w-1/2 py-2 bg-white text-black rounded">Zrušit</button>
                        <button @click="submitReview()" class="w-1/2 py-2 bg-zluta text-black rounded">Odeslat</button>
                     </div>
                </div>
            </div>

            <div :class="selectedPub ? 'w-full lg:w-3/5' : 'w-full'" class="w-full transition-all duration-500 ease-in-out relative bg-gray-900">
                <div id="map" class="w-full h-full z-10"></div>
                
                <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-black/50 z-50">
                    <div class="text-zluta text-xl font-bold animate-pulse">Načítám hospody...</div>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // 1. Inicializace mapy (Plzeň)
                var map = L.map('map').setView([49.7475, 13.3776], 14);
                
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { 
                    maxZoom: 19, 
                    attribution: '&copy; OpenStreetMap' 
                }).addTo(map);

                // 2. Definice ikony
                var beerIcon = L.icon({
                    iconUrl: '{{ asset("images/logo.png") }}', 
                    iconSize:     [32, 32], 
                    iconAnchor:   [16, 32], 
                    tooltipAnchor:[0, -32]  
                });

                // 3. Marker Cluster Group (Optimalizace výkonu)
                var markers = L.markerClusterGroup({
                    showCoverageOnHover: false, // Nevykreslovat modrý obrys shluku
                    maxClusterRadius: 50 // Menší rádius = více rozdrobené clustery
                });

                // 4. Funkce pro stažení dat (AJAX)
                fetch('{{ route("api.pubs") }}') // Voláme novou API routu
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(pub => {
                            if (pub.latitude && pub.longitude) {
                                
                                // Vytvoření markeru
                                var marker = L.marker([pub.latitude, pub.longitude], { icon: beerIcon });
                                
                                // Výpočet hvězdiček (nyní z dat z API)
                                let avgRating = 0;
                                if (pub.reviews && pub.reviews.length > 0) {
                                    let sum = pub.reviews.reduce((a, b) => a + b.rating, 0);
                                    avgRating = Math.round(sum / pub.reviews.length);
                                }
                                let stars = '★'.repeat(avgRating) + '☆'.repeat(5 - avgRating);
                                
                                // Bind Tooltip
                                marker.bindTooltip(`
                                    <div class="p-2 text-center">
                                        <div class="font-bold text-white text-sm mb-1">${pub.name}</div>
                                        <div class="text-zluta text-xs tracking-widest">${stars}</div>
                                    </div>
                                `, {
                                    permanent: false,
                                    direction: 'top',
                                    className: 'custom-tooltip',
                                    offset: [0, -5]
                                });

                                // Click event - pošle data do Alpine.js
                                marker.on('click', function() {
                                    window.dispatchEvent(new CustomEvent('pub-selected', { detail: pub }));
                                    // Jemný posun mapy
                                    map.setView([pub.latitude, pub.longitude], 16);
                                });

                                // PŘIDÁNÍ DO CLUSTERU (místo přímo do mapy)
                                markers.addLayer(marker);
                            }
                        });

                        // Přidání celé skupiny clusterů do mapy
                        map.addLayer(markers);
                        
                        // Schování loading indikátoru
                        document.getElementById('map-loading').style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Chyba při načítání mapy:', error);
                        document.getElementById('map-loading').innerHTML = '<span class="text-red-500">Chyba načítání dat.</span>';
                    });

                // Fix velikosti mapy
                window.resizeMap = function() { 
                    setTimeout(() => { map.invalidateSize(); }, 600); 
                };
                setTimeout(() => { map.invalidateSize(); }, 200);
            });
        </script>
    @endsection
</x-app-layout>