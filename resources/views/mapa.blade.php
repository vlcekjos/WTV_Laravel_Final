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
            img.leaflet-marker-icon {
                filter: invert(0%); 
            }
        </style>
    @endsection

    <!-- 
      DŮLEŽITÉ ZMĚNY V ALPINE:
      1. Přidáno @pub-selected.window - posloucháme událost z mapy
      2. resizeMap() voláme po změně dat, aby se mapa roztáhla
    -->
    <div id="map-component" 
         x-data="{ selectedPub: null }" 
         @pub-selected.window="selectedPub = $event.detail; resizeMap()"
         class="relative flex flex-col h-[calc(100vh-64px)]"> 
        
        <div class="flex-1 flex overflow-hidden relative">
            
            <!-- 1. ČÁST: DETAIL HOSPODY (VLEVO) -->
            <div x-show="selectedPub" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full opacity-0" 
                 x-transition:enter-end="translate-x-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0 opacity-100"
                 x-transition:leave-end="-translate-x-full opacity-0"
                 class="w-full lg:w-2/5 bg-black/90 border-r border-zluta p-6 overflow-y-auto absolute lg:relative left-0 h-full z-20 shadow-2xl"
                 style="display: none;">
                
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-3xl font-bold text-zluta" x-text="selectedPub?.name"></h2>
                    <!-- Křížek pro zavření -->
                    <button @click="selectedPub = null; resizeMap()" class="text-gray-400 hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Hodnocení (Ukázka) -->
                <div class="flex items-center mb-4 text-zluta">
                    <template x-for="i in 5">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    </template>
                    <span class="ml-2 text-white">(4.8)</span>
                </div>

                <p class="text-gray-300 mb-8" x-text="selectedPub?.description"></p>

                <button class="w-full py-3 bg-zluta text-black font-bold rounded hover:bg-yellow-500 transition mb-8">
                    Přidat recenzi
                </button>

                <div class="space-y-6">
                    <h3 class="text-xl text-white font-semibold border-b border-gray-700 pb-2">Recenze</h3>
                    <div class="bg-gray-800 p-4 rounded border border-gray-700">
                        <div class="flex justify-between text-sm text-gray-400 mb-2">
                            <span>Pepa Zdepa</span>
                            <span class="text-zluta">★★★★★</span>
                        </div>
                        <p class="text-gray-300 text-sm">Skvělé pivo a atmosféra!</p>
                    </div>
                </div>
            </div>


            <!-- 2. ČÁST: MAPA (VPRAVO) -->
            <div :class="selectedPub ? 'w-full lg:w-3/5' : 'w-full'" class="w-full transition-all duration-500 ease-in-out relative bg-gray-900">
                <div id="map" class="w-full h-full z-10"></div>
            </div>

        </div>
    </div>

    @section('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Inicializace mapy
                var map = L.map('map').setView([49.7475, 13.3776], 14);

                // Fix velikosti při načtení
                setTimeout(() => { map.invalidateSize(); }, 200);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const pubs = [
                    { id: 1, name: "Hospoda Na Spilce", lat: 49.7475, lng: 13.3876, description: "Tradiční plzeňská restaurace přímo v pivovaru." },
                    { id: 2, name: "U Mansfelda", lat: 49.7460, lng: 13.3750, description: "Skvělá kuchyně a tanková Plzeň." },
                    { id: 3, name: "Lokál Pod Divadlem", lat: 49.7468, lng: 13.3725, description: "Čerstvé pivo a domácí jídlo." }
                ];

                pubs.forEach(pub => {
                    var marker = L.marker([pub.lat, pub.lng]).addTo(map);
                    
                    marker.on('click', function() {
                        // OPRAVA: Místo hledání komponenty posíláme standardní událost
                        window.dispatchEvent(new CustomEvent('pub-selected', { detail: pub }));
                        
                        // Vycentrovat a zoomovat mapu na hospodu
                        map.setView([pub.lat, pub.lng], 15);
                    });
                });

                // Globální funkce pro resize mapy (volá ji Alpine)
                // Musí počkat na CSS transition (500ms), proto dáváme 600ms
                window.resizeMap = function() {
                    setTimeout(() => { map.invalidateSize(); }, 600);
                };
            });
        </script>
    @endsection
</x-app-layout>