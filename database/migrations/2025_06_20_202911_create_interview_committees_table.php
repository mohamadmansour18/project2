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
        Schema::create('interview_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('member_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->json('days')->nullable();
            $table->timestamp('start_interview_time')->nullable();
            $table->timestamp('end_interview_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_commitees');
    }
};
