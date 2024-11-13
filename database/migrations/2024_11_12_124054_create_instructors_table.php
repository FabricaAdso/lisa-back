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
            $table->date('start_date');
            $table->date('end_date')->nullable(); 

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users'); 

            $table->unsignedBigInteger('course_id'); 
            $table->foreign('course_id')->references('id')->on('courses'); 
            
            $table->unsignedBigInteger('training_center_id');
            $table->foreign('training_center_id')->references('id')->on('training_centers');


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
