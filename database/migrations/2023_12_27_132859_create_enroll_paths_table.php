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
        Schema::create('enroll_paths', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enroll_id')->unsigned()->index();
            $table->foreign('enroll_id')->references('id')->on('enrolls')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('course_id')->unsigned()->index();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->bigInteger('modul_id')->unsigned()->index();
            $table->foreign('modul_id')->references('id')->on('moduls')->onDelete('cascade');
            $table->boolean('is_complete');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enroll_paths');
    }
};
