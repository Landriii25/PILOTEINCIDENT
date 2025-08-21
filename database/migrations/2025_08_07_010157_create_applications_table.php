<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->string('statut')->default('Actif'); // Actif / En maintenance / Retirée
            $table->text('description')->nullable();

            // Logo en stockage (pas d’URL directe en BDD)
            $table->string('logo_path')->nullable();
            $table->string('thumb_path')->nullable();

            // Lien service (nullable)
            $table->foreignId('service_id')->nullable()
                ->constrained('services')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
