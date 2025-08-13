<?php

use App\Enums\ProjectForm2Status;
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
        Schema::create('project_form2s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('arabic_project_title');
            $table->text('user_segment');
            $table->text('development_procedure');
            $table->text('libraries_and_tools');
            $table->string('roadmap_file');
            $table->string('work_plan_file');
            $table->string('filled_form_file_path')->nullable();
            $table->enum('status' , ProjectForm2Status::convertEnumToArray())->default(ProjectForm2Status::Pending->value);
            $table->timestamp('submission_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_form2s');
    }
};
