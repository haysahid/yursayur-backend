<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_tags', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('no action');
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tags');
    }
};
