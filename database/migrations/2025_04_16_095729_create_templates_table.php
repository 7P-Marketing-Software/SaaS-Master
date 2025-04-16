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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('application_name')->unique();
            $table->string('domain')->unique();
            $table->integer('front_replicas')->default(1);
            $table->integer('back_replicas')->default(1);
            $table->string('db_name')->nullable();
            $table->string('db_user')->nullable();
            $table->string('db_pass')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
