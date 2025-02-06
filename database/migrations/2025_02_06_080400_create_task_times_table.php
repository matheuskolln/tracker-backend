<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTimesTable extends Migration
{
    public function up()
    {
        Schema::create('task_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('time_spent');  // in minutes
            $table->enum('status', ['started', 'stopped', 'manual']);  // Track timer status
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_times');
    }
}
