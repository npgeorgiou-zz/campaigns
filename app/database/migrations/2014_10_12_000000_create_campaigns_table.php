<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration {
    public function up() {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 50);
            $table->string('name', 50);
            $table->string('source', 50);
            $table->string('channel', 50);
            $table->string('target_url', 50);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('campaigns');
    }
}
