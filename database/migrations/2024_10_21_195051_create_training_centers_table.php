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
        Schema::create('training_centers', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique();
            $table->string('name');

            // //Llave Foranea Municipio
            // $table->unsignedBigInteger('municipality_id');
            // $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_centers');
    }
};
