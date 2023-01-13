<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\HistoryType;

class HistoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [ 'create',     'Поручение создано'       ],
            [ 'complete',   'Поручение отправлено на проверку' ],
            [ 'back',     'Поручение возвращено'    ],
            [ 'close',      'Поручение закрыто'       ],
            [ 'comment',    'Добавлен комментарий к поручению'  ],
            [ 'creators',    'Изменён список постановщиков'  ],
            [ 'executors',    'Изменён список исполнителей'  ],
            [ 'coexecutors',    'Изменён список соисполнителей'  ],
            [ 'controllers',    'Изменён список наблюдателей'  ],
            [ 'deadline_request',    'Запрос на изменение срока поручения' ],
            [ 'deadline_updated',    'Запрос на изменение срока одобрен'  ],
            [ 'request_deadline_declined',    'Запрос на изменение срока отклонён'  ],

            [ 'document_created',    'Документ создан'  ],
            [ 'document_mailing_users_modified',    'Изменён список рассылки в документе'  ],
        ];
        
        foreach($types as $type) {
            HistoryType::create([
                'name' => $type[0],
                'title' => $type[1],
            ]);
        };
    }
}
