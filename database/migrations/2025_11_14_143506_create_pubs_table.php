<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Přesné souřadnice pro mapu
            // (10, 8) pro latitude (-90 až +90)
            // (11, 8) pro longitude (-180 až +180)
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Informace o adrese
            $table->string('street')->nullable();
            $table->string('city')->default('Plzeň');
            $table->string('postcode', 10)->nullable();

            // Cesta k obrázku (např. 'images/pubs/plzenska.jpg')
            $table->string('image')->nullable();

            // Další data z Overpass
            $table->string('website')->nullable();
            $table->string('phone')->nullable();

            // Důležité pro párování s Overpass
            $table->bigInteger('overpass_node_id')->nullable()->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pubs');
    }
};
