<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('document_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->foreignId('history_type_id')
                ->constrained('history_types');
            
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
        Schema::dropIfExists('histories');
    }
}
