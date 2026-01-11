<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->dateTime('vip_until')->nullable();
            $table->timestamps();

            $table->index('telegram_user_id');
            $table->index('vip_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};
