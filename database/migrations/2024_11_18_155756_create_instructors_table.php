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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->enum('state',['Activo','Inactivo']);
            //FK
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('training_center_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('knowledge_network_id')->nullable()->constrained()->onDelete('set null');
            $table->unique(['user_id','training_center_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
