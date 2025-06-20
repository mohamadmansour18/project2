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
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('interview_committees' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->json('interview_day');
            $table->timestamp('interview_time');
            $table->timestamp('interview_end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
