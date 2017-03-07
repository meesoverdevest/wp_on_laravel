<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWpPostCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wp_post_category', function(Blueprint $table){
            $table->engine = 'InnoDB';
            $table->unsignedInteger('post_id')->index();
            $table->unsignedInteger('category_id')->index();
            $table->primary(['post_id','category_id']);
        });

        Schema::table('wp_post_category', function($table){
            $table->foreign('post_id')
                ->references('id')
                ->on('wp_posts')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('wp_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_category');
    }
}

