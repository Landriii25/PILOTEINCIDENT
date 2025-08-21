<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            // Code unique (ex: INC2508-0001)
            $table->string('code')->unique();

            $table->string('titre')->nullable();
            $table->text('description')->nullable();

            // Liens
            $table->foreignId('application_id')->nullable()
                ->constrained('applications')->nullOnDelete();

            $table->foreignId('service_id')->nullable()
                ->constrained('services')->nullOnDelete();

            // Créateur (demandeur)
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Technicien assigné
            $table->foreignId('technicien_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Métier
            $table->string('priorite')->default('Moyenne'); // Critique/Haute/Moyenne/Basse
            $table->string('statut')->default('Ouvert');    // Ouvert/En cours/Résolu/Clos

            // SLA & dates de suivi
            $table->timestamp('due_at')->nullable();       // date limite SLA
            $table->timestamp('taken_at')->nullable();     // date de prise en charge par le tech
            $table->timestamp('resolved_at')->nullable();  // date résolution

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
