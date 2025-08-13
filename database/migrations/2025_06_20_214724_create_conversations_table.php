<?php

use App\Enums\ConversationType;
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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_one_id')
                  ->constrained('users' , 'id')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('user_two_id')
                  ->nullable()
                  ->constrained('users' , 'id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->enum('type' , ConversationType::convertEnumToArray())
                  ->default(ConversationType::Self->value);

            $table->timestamp('last_message_at')
                  ->nullable();

            $table->timestamps();

            //index non-clustered
            $table->index('last_message_at');
            $table->index('user_one_id');
            $table->index('user_two_id');

            //note the smallest id always put in user one id , and the greatest put in user two id & in self conversation always put the user_two_id = null
            $table->unique(['type' , 'user_one_id' , 'user_two_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
