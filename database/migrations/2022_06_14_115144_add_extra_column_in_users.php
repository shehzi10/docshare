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
        Schema::table('users', function (Blueprint $table) {
            $table->text('device_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string( 'phone_number')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->boolean('is_notify')->default(1);
            $table->enum('status', ['active', 'deActive'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
           $table->dropColumn('device_id');
           $table->dropColumn('stripe_customer_id');
           $table->dropColumn('phone_number');
           $table->dropColumn('profile_pic');
           $table->dropColumn('dob');
           $table->dropColumn('address');
           $table->dropColumn('country');
           $table->dropColumn('city');
           $table->dropColumn('state');
           $table->dropColumn('zip');
           $table->dropColumn('lat');
           $table->dropColumn('lng');
           $table->dropColumn('is_notify');
           $table->dropColumn('status');
        });
    }
};
