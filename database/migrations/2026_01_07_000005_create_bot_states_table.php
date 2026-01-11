<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_states', function (Blueprint $table) {
            $table->bigInteger('telegram_user_id')->primary();
            $table->string('state', 50);
            $table->json('data')->nullable()->comment('Temporary wizard data');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_states');
    }
};
