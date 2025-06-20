<?php

use App\Enums\ProjectFormStatus;
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
        Schema::create('project_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('arabic_title');
            $table->string('english_title');
            $table->string('description');
            $table->string('project_scope');
            $table->string('targeted_sector');
            $table->string('sector_classification');
            $table->string('stakeholders');
            $table->string('supervisor_signature')->nullable();
            $table->string('filled_form_file_path')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->string('status' , 12)->default(ProjectFormStatus::Pending->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_forms');
    }
};
