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
        Schema::create('grade_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('project_grades' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('student_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_exceptions');
    }
};
