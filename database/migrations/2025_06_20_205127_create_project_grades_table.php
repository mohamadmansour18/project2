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
        Schema::create('project_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('interview_committees' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedTinyInteger('presentation_grade');
            $table->unsignedTinyInteger('project_grade');
            $table->unsignedTinyInteger('total_grade');
            $table->boolean('is_edited')->default(0);
            $table->unsignedTinyInteger('previous_total_grade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_gardes');
    }
};
