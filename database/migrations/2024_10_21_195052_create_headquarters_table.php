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
        Schema::create('headquarters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('adress');
            $table->time('opening_time');
            $table->time('closing_time');


            //LLave Foranea Municipio
            $table->unsignedBigInteger('municipality_id');
            $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('cascade');

            //LLave Foranea Centro Formacion
            $table->unsignedBigInteger('training_center_id');
            $table->foreign('training_center_id')->references('id')->on('training_centers')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('headquarters');
    }
};
