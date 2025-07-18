<?php

use App\Enums\GroupMemberRole;
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
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('role', GroupMemberRole::convertEnumToArray());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
