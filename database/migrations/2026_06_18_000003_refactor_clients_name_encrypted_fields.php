<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make name column nullable since we now use first_name/last_name/display_name
        DB::statement('ALTER TABLE clients ALTER COLUMN name DROP NOT NULL');

        Schema::table('clients', function (Blueprint $table) {
            // Split name into first_name, last_name, display_name
            $table->string('first_name', 100)->nullable()->after('tenant_id');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('display_name', 255)->nullable()->after('last_name');
            $table->index('first_name');
            $table->index('last_name');

            // Add encrypted columns for sensitive data (doc)
            $table->text('doc_encrypted')->nullable()->after('doc');
            $table->string('doc_hash', 64)->nullable()->after('doc_encrypted');
            $table->index('doc_hash');

            // Add encrypted columns for sensitive data (phone)
            $table->text('phone1_encrypted')->nullable()->after('phone1');
            $table->string('phone1_hash', 64)->nullable()->after('phone1_encrypted');
            $table->text('phone2_encrypted')->nullable()->after('phone2');
            $table->string('phone2_hash', 64)->nullable()->after('phone2_encrypted');
            $table->index('phone1_hash');
            $table->index('phone2_hash');

            // Add soft deletes for LGPD compliance
            $table->softDeletes();
        });

        // Migrate existing data: split name into first_name and last_name
        DB::statement("UPDATE clients SET first_name = SPLIT_PART(name, ' ', 1), last_name = SUBSTRING(name FROM POSITION(' ' IN name) + 1) WHERE name LIKE '% %'");
        DB::statement("UPDATE clients SET first_name = name, last_name = name WHERE name NOT LIKE '% %' OR name IS NULL");
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'display_name',
                'doc_encrypted',
                'doc_hash',
                'phone1_encrypted',
                'phone1_hash',
                'phone2_encrypted',
                'phone2_hash',
            ]);
            $table->dropSoftDeletes();
        });
    }
};