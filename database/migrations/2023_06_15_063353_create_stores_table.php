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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('user_id')->unsigned();
            $table->string('name');
            $table->string('description', 2500)->nullable();
            $table->string('address');
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->timestamp('disabled_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('no action');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
