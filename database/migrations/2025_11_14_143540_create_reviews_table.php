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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Propojení na uživatele (Jetstream používá bigIncrements)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Propojení na hospodu
            $table->foreignId('pub_id')->constrained('pubs')->onDelete('cascade');

            // Hodnocení (1-5 hvězd)
            $table->unsignedTinyInteger('rating');

            // Text recenze
            $table->text('comment');

            $table->timestamps();

            // Unikátní klíč, aby jeden uživatel nemohl recenzovat
            // stejnou hospodu vícekrát (lze smazat, pokud to chceš povolit)
            $table->unique(['user_id', 'pub_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
