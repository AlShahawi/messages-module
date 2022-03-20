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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->references('id')
                ->on('conversations')
                ->restrictOnDelete();
            $table->foreignId('sender_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
            $table->text('content')->fulltext();
            $table->dateTime('read_at')->nullable();
            $table->timestamp('created_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
