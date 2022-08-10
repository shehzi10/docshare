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
            $table->foreignId('chatlist_id')->index('fk_chatlist-id');
            $table->morphs('sent_from');
            $table->morphs('sent_to');
            $table->string('type')->default('text');
            $table->string('media')->nullable();
            $table->string('audio')->nullable();
            $table->string('image')->nullable();
            $table->text('message')->nullable();
            $table->boolean('read')->default(0);
            $table->timestamps();
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
