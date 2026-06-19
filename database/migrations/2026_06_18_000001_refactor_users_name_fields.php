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
        DB::statement("UPDATE users SET first_name = SPLIT_PART(name, ' ', 1), last_name = SUBSTRING(name FROM POSITION(' ' IN name) + 1) WHERE name LIKE '% %'");
        // For single word names, set last_name same as first_name
        DB::statement("UPDATE users SET first_name = name, last_name = name WHERE name NOT LIKE '% %' OR name IS NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('email');
        });

        DB::statement("UPDATE users SET name = CONCAT(first_name, ' ', last_name)");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'display_name']);
        });
    }
};