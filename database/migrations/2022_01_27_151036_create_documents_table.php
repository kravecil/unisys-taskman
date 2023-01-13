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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            /** тип документа */
            $table->unsignedTinyInteger('type_id');

            /** номер документа */
            $table->string('number', 20)->nullable();

            /** вид документа */
            $table->string('kind')->nullable();

            /** дата документа */
            $table->datetime('issued_at')->nullable();

            /** дата отправки письма */
            $table->datetime('sent_at')->nullable();

            /** вид отправки */
            $table->string('sent_by')->nullable();

            /** исходящее письмо? */
            // $table->boolean('is_outgoing')->nullable();

            /** приказ КЗ (кадры зарплатный) */
            // $table->boolean('is_kadr_salary')->nullable();

            /** тело документа (описание, содержание) */
            $table->text('body')->nullable();

            /** информация об отправителе входящего письма */
            $table->string('partner')->nullable();

            /** исполнитель / на кого доверенность */
            $table->string('executor')->nullable();

            /** кто подписал */
            $table->string('signer')->nullable();

            // $table->foreignId('company_id')->nullable();
            
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
        Schema::dropIfExists('documents');
    }
};
