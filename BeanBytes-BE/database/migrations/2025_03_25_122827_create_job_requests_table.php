<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('job_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->enum('type', ['hiring', 'looking_for_job']);
            $table->enum('status', ['open', 'in_progress', 'closed'])
                  ->default('open');
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_requests');
    }
};
