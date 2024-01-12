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
        Schema::create('thread_replies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('thread_id')->unsigned()->index();
            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('body');
            $table->string('image')->nullable();
            $table->bigInteger('upvote_count');
            $table->bigInteger('downvote_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thread_replies');
    }
};
