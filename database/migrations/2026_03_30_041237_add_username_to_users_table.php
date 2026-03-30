<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For HANA, use raw SQL
        DB::statement('ALTER TABLE "users" ADD ("username" NVARCHAR(255))');
        DB::statement('CREATE UNIQUE INDEX "users_username_unique" ON "users" ("username")');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX "users_username_unique"');
        DB::statement('ALTER TABLE "users" DROP ("username")');
    }
};
