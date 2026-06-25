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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type')->comment('staff ou client');
            $table->unsignedBigInteger('sender_id')->comment('ID do remetente (users ou clients)');
            $table->text('content_encrypted')->comment('Conteúdo da mensagem criptografado em repouso (LGPD)');
            $table->timestamps();

            $table->index('thread_id');
            $table->index(['sender_type', 'sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};