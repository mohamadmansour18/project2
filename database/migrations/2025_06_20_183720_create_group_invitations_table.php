<?php

use App\Enums\GroupInvitationStatus;
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
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('invited_user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('invited_by_user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status' , GroupInvitationStatus::convertEnumToArray())->default(GroupInvitationStatus::Pending->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
    }
};
