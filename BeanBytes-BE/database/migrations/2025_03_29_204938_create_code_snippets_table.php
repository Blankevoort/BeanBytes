<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_snippets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('language');
            $table->string('license')->nullable();
            $table->string('file_path');
            $table->boolean('is_free')->default(false);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('code_snippets');
    }
};
