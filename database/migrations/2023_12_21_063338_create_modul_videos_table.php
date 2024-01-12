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
        Schema::create('modul_videos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('modul_id')->unsigned()->index();
            $table->foreign('modul_id')->references('id')->on('moduls')->onDelete('cascade');
            $table->string('title');
            $table->string('filename');
            $table->string('thumbnail');
            $table->integer('duration');
            $table->integer('priority')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modul_videos');
    }
};
