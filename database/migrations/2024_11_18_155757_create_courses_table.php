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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('shift')->nullable();
            $table->enum('state', ['Terminada_por_fecha','En_ejecucion','Terminada','Termindad_por_unificacion']);//estado
            $table->enum('stage', ['PRACTICA','LECTIVA'])->nullable();//etapa
            //FK
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('environment_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
