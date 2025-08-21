<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incident_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')
                ->constrained('incidents')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // type: created / updated / assigned / resolved / closed / reopened ...
            $table->string('type')->index();

            // détails (JSON ou texte)
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_activities');
    }
};
