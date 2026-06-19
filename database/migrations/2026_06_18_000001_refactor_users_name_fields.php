<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->after('id');
            $table->string('last_name', 100)->after('first_name');
            $table->string('display_name', 255)->nullable()->after('last_name');
            $table->index('first_name');
            $table->index('last_name');
        });

        // Migrate existing data: split name into first_name and last_name
        // Compatible with both PostgreSQL and SQLite
        DB::statement("UPDATE users SET first_name = SUBSTR(name, 1, CASE WHEN INSTR(name, ' ') > 0 THEN INSTR(name, ' ') - 1 ELSE LENGTH(name) END), last_name = CASE WHEN INSTR(name, ' ') > 0 THEN SUBSTR(name, INSTR(name, ' ') + 1) ELSE name END WHERE name IS NOT NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('email');
        });

        // Compatible with both PostgreSQL and SQLite
        DB::statement("UPDATE users SET name = first_name || ' ' || last_name");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'display_name']);
        });
    }
};