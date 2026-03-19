<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->text('message_user')->nullable();
            $table->text('message_bot')->nullable();
            $table->string('source')->default('web'); // web, whatsapp
            $table->string('ip_address')->nullable();
            $table->string('user_identifier')->nullable(); // Para el número de WhatsApp o similar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_logs');
    }
};
