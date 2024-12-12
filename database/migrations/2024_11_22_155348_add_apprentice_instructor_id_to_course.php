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
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('course_leader_id')->nullable()->constrained('instructors')->onDelete('set null');
            $table->foreignId('representative_id')->nullable()->constrained('apprentices')->onDelete('set null');
            $table->foreignId('co_representative_id')->nullable()->constrained('apprentices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //eliminar las foreign keys en caso de revertir la migracion
            $table->dropForeign(['course_leader_id']);
            $table->dropForeign(['representative_id']);
            $table->dropForeign(['co_representative_id']);
        });
    }
};
