<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name
            $table->string('table_name')->unique(); // Database table name
            $table->string('model_name')->nullable(); // Model class name
            $table->string('icon')->default('bi-table'); // Bootstrap icon
            $table->integer('order')->default(0); // Menu order
            $table->boolean('is_active')->default(true);
            $table->json('fields')->nullable(); // Field definitions
            $table->json('relationships')->nullable(); // Relationship definitions
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
