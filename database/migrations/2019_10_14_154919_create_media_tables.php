<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedInteger('parent_id')->nullable()
                ->references('id')->on('media_folders');
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('filename');
            $table->string('disk');
            $table->string('type');
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->text('alt')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('size');
            $table->unsignedInteger('folder_id')->nullable()
                ->references('id')->on('media_folders');
            $table->timestamps();
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->index();
            $table->morphs('mediable');
            $table->string('collection');
            $table->unsignedInteger('order_column')->nullable();
            $table->nullableTimestamps();

            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->onDelete('cascade');
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('media_tag', function (Blueprint $table) {
            $table->integer('media_id')
                ->references('id')->on('media')->onDelete('cascade');
            $table->integer('tag_id')
                ->references('id')->on('tags')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['media_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_folders');
        Schema::dropIfExists('media');
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_tag');
    }
}
