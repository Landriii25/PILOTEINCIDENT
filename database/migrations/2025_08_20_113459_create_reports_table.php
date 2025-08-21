<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Lien à l’incident
            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->cascadeOnDelete();

            // Référence = code de l’incident (stockée pour lecture rapide)
            $table->string('ref');

            // Auteur (technicien), optionnel
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Contenu du rapport
            $table->text('description');
            $table->text('constats');
            $table->text('causes');
            $table->text('actions');
            $table->text('impacts');
            $table->text('recommendation')->nullable();

            // ⚠ DATETIME nullable pour compatibilité MySQL/MariaDB
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            // Durée calculée (en minutes)
            $table->unsignedInteger('duration_minutes')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
