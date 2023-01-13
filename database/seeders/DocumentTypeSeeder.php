<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentTypes = [
            [
                'name' => 'miscdocument',
                'name_plural' => 'miscdocuments',
                'title' => 'Документ',
                'title_plural' => 'Документы',
            ],
            [
                'name' => 'decree',
                'name_plural' => 'decrees',
                'title' => 'Приказ',
                'title_plural' => 'Приказы',
            ],
            [
                'name' => 'order',
                'name_plural' => 'orders',
                'title' => 'Распоряжение',
                'title_plural' => 'Распоряжения',
            ],
            [
                'name' => 'memorandum',
                'name_plural' => 'memorandums',
                'title' => 'Служебная записка',
                'title_plural' => 'Служебные записки',
            ],
            [
                'name' => 'protocol',
                'name_plural' => 'protocols',
                'title' => 'Протокол',
                'title_plural' => 'Протоколы',
            ],
            [
                'name' => 'mail',
                'name_plural' => 'mails',
                'title' => 'Входящее письмо',
                'title_plural' => 'Входящие письма',
            ],
            [
                'name' => 'poa',
                'name_plural' => 'poas',
                'title' => 'Доверенность',
                'title_plural' => 'Доверенности',
            ],
            [
                'name' => 'outgoingMail',
                'name_plural' => 'outgoingMails',
                'title' => 'Исходящее письмо',
                'title_plural' => 'Исходящие письма',
            ],
            [
                'name' => 'ksDecree',
                'name_plural' => 'ksDecrees',
                'title' => 'Приказ КЗ',
                'title_plural' => 'Приказы КЗ',
            ],
        ];

        foreach($documentTypes as $documentType)
            DocumentType::create($documentType);
    }
}
