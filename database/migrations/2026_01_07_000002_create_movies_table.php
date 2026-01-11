<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('thumbnail_file_id')->nullable();
            $table->bigInteger('channel_message_id')->nullable()->comment('For edit message in channel');
            $table->integer('total_parts');
            $table->bigInteger('created_by')->nullable()->comment('Admin telegram_user_id');
            $table->timestamps();

            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
