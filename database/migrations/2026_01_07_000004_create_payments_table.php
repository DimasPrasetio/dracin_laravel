<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_user_id')->constrained('telegram_users')->onDelete('cascade');
            $table->enum('package', ['1day', '3days', '7days', '30days']);
            $table->integer('amount');
            $table->string('payment_method', 50)->nullable();
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->dateTime('expired_at')->nullable();

            // Tripay integration fields
            $table->string('tripay_reference', 50)->nullable()->unique();
            $table->string('tripay_merchant_ref', 100)->nullable();
            $table->string('tripay_payment_method', 50)->nullable();
            $table->string('tripay_payment_name', 100)->nullable();
            $table->text('tripay_pay_url')->nullable();
            $table->string('tripay_qr_string')->nullable();
            $table->string('tripay_checkout_url')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('telegram_user_id');
            $table->index('tripay_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
