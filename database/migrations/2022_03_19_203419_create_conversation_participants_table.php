<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->references('id')
                ->on('conversations')
                ->restrictOnDelete();
            $table->foreignId('participant_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_participants');
    }
};
