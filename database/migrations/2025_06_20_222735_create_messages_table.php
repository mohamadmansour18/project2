<?php

use App\Enums\MessageType;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('faq_id')->nullable()->constrained('f_a_q_s' , 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('message_type' , MessageType::convertEnumToArray())->default(MessageType::Text->value);
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
