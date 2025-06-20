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
        Schema::create('form_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade')->onDelete('cascade');
            $table->foreignId('project_form_id')->constrained('project_forms' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_signatures');
    }
};
