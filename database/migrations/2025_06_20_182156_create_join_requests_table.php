<?php

use App\Enums\JoinRequestStatus;
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
        Schema::create('join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status' , JoinRequestStatus::convertEnumToArray())->default(JoinRequestStatus::Pending->value);
            $table->timestamp('reviewed_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('join_requests');
    }
};
