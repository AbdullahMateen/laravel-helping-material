<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('group')->nullable()->default(null);
            $table->unsignedSmallInteger('category')->nullable()->default(null);

            $table->nullableMorphs('mediaable');

            $table->string('media_url', 250)->nullable()->default(null);
            $table->string('thumb_url', 250)->nullable()->default(null);

            $table->string('name', 80)->nullable()->default(null);
            $table->string('media_name', 80)->nullable()->default(null);
            $table->string('thumb_name', 80)->nullable()->default(null);

            $table->string('path', 250)->nullable()->default(null);
            $table->string('file_path', 250)->nullable()->default(null);
            $table->string('type', 80)->nullable()->default(null);
            $table->string('extension', 20)->nullable()->default(null);

            $table->unsignedInteger('media_size')->nullable()->default(null);
            $table->unsignedInteger('thumb_size')->nullable()->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
