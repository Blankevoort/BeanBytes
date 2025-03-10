<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->morphs('assetable');
            $table->string('path');
            $table->enum('type', ['image', 'video']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
