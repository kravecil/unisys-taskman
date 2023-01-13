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
        Schema::create('document_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('user_id');
                
            $table->datetime('read_at')->nullable();

            $table->unsignedTinyInteger('approval_order')->nullable();
            $table->text('approval_note')->nullable();
            $table->boolean('is_approved')->nullable();

            $table->boolean('is_mailing')->default(false);
            $table->boolean('is_issuer')->default(false);
            $table->boolean('is_sender')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_user');
    }
};
