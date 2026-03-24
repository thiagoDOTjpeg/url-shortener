<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('url_clicks', function (Blueprint $table) {
            $table->string('browser', 50)->nullable()->index();
            $table->string('os', 50)->nullable()->index();
            $table->string('device_type', 50)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_clicks', function (Blueprint $table) {
            $table->dropColumn('browser');
            $table->dropColumn('os');
            $table->dropColumn('device_type');
        });
    }
};
