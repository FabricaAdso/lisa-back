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
    Schema::create('notifies', function (Blueprint $table) {
        $table->string('id')->primary(); // Definir la columna 'id' como primary key
        $table->text('payload')->nullable();
        $table->integer('last_activity')->nullable();
        $table->integer('user_id')->unsigned()->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamps(); // `created_at` y `updated_at`
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifies');
    }
};
