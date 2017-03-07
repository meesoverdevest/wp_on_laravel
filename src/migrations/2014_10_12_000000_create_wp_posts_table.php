<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWpPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wp_posts', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('wp_id');
            $table->string('user_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->longText('featured_image')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured')->nullable();
            $table->longText('excerpt')->nullable();
            $table->string('format')->nullable();
            $table->string('status')->nullable();
            $table->string('publishes_at')->nullable();

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
        Schema::drop('posts');
    }
}

