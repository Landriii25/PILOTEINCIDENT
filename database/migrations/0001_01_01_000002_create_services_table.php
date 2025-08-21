<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('nom');
            $table->string('description')->nullable();

            // On crée la colonne, SANS FK ici
            $table->unsignedBigInteger('chef_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
