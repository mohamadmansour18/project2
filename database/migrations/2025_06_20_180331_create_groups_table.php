<?php

use App\Enums\GroupSpecialityNeeded;
use App\Enums\GroupType;
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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('speciality_needed')->nullable();
            $table->json('framework_needed')->nullable();
            $table->enum('type' , GroupType::convertEnumToArray())->default(GroupType::Public->value);
            $table->string('qr_code');
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('number_of_members');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
