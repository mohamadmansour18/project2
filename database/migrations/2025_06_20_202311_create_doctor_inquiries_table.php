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
        Schema::create('doctor_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->boolean('is_answered')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_inquiries');
    }
};
