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
        Schema::create("url_shorteneds", function (Blueprint $table) {
            $table->string("id")->primary();
            $table->string("original_url")->index();
            $table->text("qr_code")->nullable();
            $table->timestamp("expires_at")->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("url_shorteneds");
    }
};
