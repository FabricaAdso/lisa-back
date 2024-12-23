<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regionals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->timestamps();
        }); 
        DB::statement('SET NAMES utf8mb4');
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regionals');
    }
};
