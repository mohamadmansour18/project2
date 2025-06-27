<?php

use App\Enums\ProfileGovernorate;
use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
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
            $table->enum('governorate' , ProfileGovernorate::convertEnumToArray())->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone_number' , 12)->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('student_speciality', ProfileStudentSpeciality::convertEnumToArray())->nullable();
            $table->enum('student_status' , ProfileStudentStatus::convertEnumToArray())->nullable();
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
