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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('governorate' , 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone_number' , 10)->nullable();
            $table->string('profile_image')->nullable();
            $table->string('student_speciality', 16)->nullable();
            $table->string('student_status' , 16)->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
