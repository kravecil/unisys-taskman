<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_documents', function (Blueprint $table) {
            $table->id();

            $table->integer('number');
            $table->unsignedInteger('registrar_id');
            $table->unsignedInteger('signer_id')->nullable();
            $table->unsignedInteger('executor_id')->nullable();
            $table->string('receiver')->nullable();
            $table->text('description')->nullable();

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
        Schema::dropIfExists('outgoing_documents');
    }
};
