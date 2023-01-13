<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // $table->unsignedInteger('creator_id');
            $table->text('body');
            $table->foreignId('document_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('project_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            // $table->unsignedInteger('executor_id')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->dateTime('request_deadline_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->boolean('is_note')->default(true);
            $table->boolean('is_coexecutors')->default(false);
            $table->unsignedTinyInteger('state');

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
        Schema::dropIfExists('tasks');
    }
}
