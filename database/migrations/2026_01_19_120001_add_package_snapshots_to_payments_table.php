<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'package_name')) {
                $table->string('package_name', 150)->nullable()->after('package');
            }
            if (!Schema::hasColumn('payments', 'package_duration_days')) {
                $table->unsignedInteger('package_duration_days')->nullable()->after('package_name');
            }
            if (!Schema::hasColumn('payments', 'package_price')) {
                $table->unsignedInteger('package_price')->nullable()->after('package_duration_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'package_price')) {
                $table->dropColumn('package_price');
            }
            if (Schema::hasColumn('payments', 'package_duration_days')) {
                $table->dropColumn('package_duration_days');
            }
            if (Schema::hasColumn('payments', 'package_name')) {
                $table->dropColumn('package_name');
            }
        });
    }
};
