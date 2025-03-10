<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('interactionable');
            $table->enum('type', ['like', 'bookmark']);
            $table->timestamps();

            $table->unique(
                ['user_id', 'interactionable_id', 'interactionable_type', 'type'],
                'interaction_unique'
            );

        });
    }

    public function down()
    {
        Schema::dropIfExists('interactions');
    }
};
