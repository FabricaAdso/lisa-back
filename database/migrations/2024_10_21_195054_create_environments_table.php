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
        Schema::create('environments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity');

            //Llave Foranea Sede
            $table->unsignedBigInteger('headquarters_id');
            $table->foreign('headquarters_id')->references('id')->on('headquarters')->onDelete('cascade');

            //Llave Foranea Area
            $table->unsignedBigInteger('environment_area_id');
            $table->foreign('environment_area_id')->references('id')->on('environment_areas')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environments');
    }
};
