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
        Schema::create('saas_settings', function (Blueprint $table) {
            $table->id();
            $table->string('applciation_name')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_maintenance')->default(false);
            $table->boolean('interactions')->default(true);
            $table->boolean('chatBot')->default(true);
            $table->boolean('questions_community')->default(true);
            $table->boolean('quotes')->default(true);
            $table->boolean('blog')->default(true);
            $table->enum('video_setting', ['server', 'link', 'youtube', 'all'])->default('all');
            $table->boolean('gamafications')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_settings');
    }
};
