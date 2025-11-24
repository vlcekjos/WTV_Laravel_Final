<?php

namespace App\Console\Commands;

use App\Models\Pub;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportPubsFromOsm extends Command
{
    /**
     * Název a parametry příkazu.
     * Použití v terminálu: php artisan osm:fetch 5000
     * (5000 je volitelný rádius v metrech, defaultně 5km)
     */
    protected $signature = 'osm:fetch {radius=5000}';

    /**
     * Popis příkazu pro nápovědu.
     */
    protected $description = 'Stáhne hospody z OpenStreetMap (Plzeň) a uloží je do DB pomocí upsert.';

    /**
     * Hlavní logika příkazu.
     */
    public function handle()
    {
        $radius = $this->argument('radius');
        $this->info("⏳ Připojuji se k Overpass API (Rádius: {$radius}m kolem Plzně)...");

        // 1. Konfigurace středu hledání (Náměstí Republiky, Plzeň)
        $lat = 49.7475;
        $lon = 13.3776;

        // 2. Sestavení Overpass QL dotazu
        // Hledáme uzly (node) s tagem amenity = pub, bar nebo biergarten
        $query = <<<QL
        [out:json][timeout:25];
        (
          node["amenity"="pub"](around:$radius, $lat, $lon);
          node["amenity"="bar"](around:$radius, $lat, $lon);
          node["amenity"="biergarten"](around:$radius, $lat, $lon);
        );
        out body;
        QL;

        try {
            // 3. Odeslání POST requestu na API
            $response = Http::asForm()->post('https://overpass-api.de/api/interpreter', [
                'data' => $query
            ]);

            if ($response->failed()) {
                $this->error('❌ Chyba komunikace s API: ' . $response->status());
                return 1;
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            if (empty($elements)) {
                $this->warn('⚠️ Nebyla nalezena žádná data. Zkus zvětšit rádius.');
                return 0;
            }

            $count = count($elements);
            $this->info("✅ Staženo {$count} míst. Zpracovávám a ukládám do DB...");

            // 4. Příprava dat pro hromadné vložení (Batch Insert)
            $batch = [];
            $timestamp = now();

            // Progress bar pro hezký výpis v terminálu
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($elements as $node) {
                $tags = $node['tags'] ?? [];

                // Přeskočíme místa bez názvu (v mapě by byly k ničemu)
                if (empty($tags['name'])) {
                    $bar->advance();
                    continue;
                }

                // Sestavení popisu z různých tagů, pokud existují
                $descriptionParts = [];
                if (!empty($tags['brewery'])) $descriptionParts[] = "Pivo: " . $tags['brewery'];
                if (!empty($tags['outdoor_seating'])) $descriptionParts[] = "Zahrádka: " . ($tags['outdoor_seating'] == 'yes' ? 'Ano' : 'Ne');
                if (!empty($tags['opening_hours'])) $descriptionParts[] = "Otevřeno: " . $tags['opening_hours'];
                
                // Pokud je místo označené jako 'bar', přidáme to do popisu
                if (isset($tags['amenity']) && $tags['amenity'] === 'bar') {
                    array_unshift($descriptionParts, "[BAR]");
                }

                // Příprava jednoho řádku do DB
                $batch[] = [
                    'overpass_node_id' => $node['id'], // Klíč pro identifikaci
                    'name'      => $tags['name'],
                    'latitude'  => $node['lat'],
                    'longitude' => $node['lon'],
                    'street'    => $tags['addr:street'] ?? null,
                    'city'      => $tags['addr:city'] ?? 'Plzeň',
                    'postcode'  => $tags['addr:postcode'] ?? null,
                    'website'   => $tags['website'] ?? ($tags['contact:website'] ?? null),
                    'phone'     => $tags['phone'] ?? ($tags['contact:phone'] ?? null),
                    'description' => implode(" | ", $descriptionParts),
                    'image'     => null, // OSM obrázky neposílá
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // 5. Uložení do DB po dávkách (Chunks)
            // Upsert zajistí, že existující hospody se aktualizují a nové se vytvoří
            $chunks = array_chunk($batch, 500);
            
            $this->info("Ukládám data do databáze...");
            
            foreach ($chunks as $chunk) {
                Pub::upsert(
                    $chunk, 
                    ['overpass_node_id'], // Unikátní sloupec, podle kterého poznáme duplicitu
                    // Sloupce, které chceme aktualizovat, pokud záznam už existuje:
                    ['name', 'latitude', 'longitude', 'street', 'city', 'postcode', 'website', 'phone', 'description', 'updated_at']
                );
            }

            $this->info("🎉 Hotovo! Databáze je aktuální.");
            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Kritická chyba: ' . $e->getMessage());
            return 1;
        }
    }
}