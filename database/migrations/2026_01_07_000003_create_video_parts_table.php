<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->string('video_id', 50)->unique()->comment('Deep link ID: MKV...');
            $table->string('file_id')->comment('Telegram file_id');
            $table->integer('part_number');
            $table->boolean('is_vip')->default(true);
            $table->integer('duration')->nullable()->comment('Video duration in seconds');
            $table->bigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->timestamps();

            $table->unique(['movie_id', 'part_number']);
            $table->index('video_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_parts');
    }
};
