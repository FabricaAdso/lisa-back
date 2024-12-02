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
        Schema::create('aprobations', function (Blueprint $table) {
            $table->id();
            $table->enum('state',['Pendiente', 'Aprobada', 'Rechazada', 'Vencida'])->nullable();
            $table->string('motive')->nullable();
            //FK
            $table->foreignId('justification_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprobations');
    }
};