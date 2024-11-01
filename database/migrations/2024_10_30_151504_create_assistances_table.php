<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void {
        Schema::create('assistances', function (Blueprint $table) {
            $table->id();
            $table->enum('assistance', ['ASISTIO', 'FALTA', 'FALTA_JUSTIFICADA']); 

            $table->unsignedBigInteger('participant_id');
            $table->foreign('participant_id')->references('id')->on('participants'); 

            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('sessions'); 

            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('assistances');
    }
    
};
