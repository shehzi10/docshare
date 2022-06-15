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
        Schema::create('user_friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('requested_user_id');
            $table->boolean('is_followed')->default('1');
            $table->boolean('followed_back')->default('0');
            $table->enum('status', ['approved', 'rejected', 'pending'])->default('pending');
            $table->dateTime('accepted_date')->nullable();
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
        Schema::dropIfExists('user_friends');
    }
};
