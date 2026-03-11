<?php

return [
    'health' => [
        /**
         * Dentro de cada adapter a ser verificado é necessario a
         * criação da função getURL() que retorna a url do adapter.
         */
        'adapters' => [
            'path' => app_path('Adapters'),
            'namespace' => 'App\Adapters',
            'ignore' => [
                //Inserir o nome do arquivo.php que deve ser ignorado.
            ],
        ],
        'databases' => [
            /**
             * Inserir o nome das conexões de banco de dados que devem ser verificadas.
             */
            'names' => [
                'mysql',
                'xavier_legacy',
            ],
            /**
             * Inserir o nome das conexões de redis que devem ser verificadas.
             */
            'redisNames' => [
                'default',
                'cache',
            ],
        ],
        'queue' => [
            'types' => [
                /**
                 * Inserir as configurações da fila SQS que devem ser verificadas.
                 */
                'sqs' => [
                    'region' => env('AWS_DEFAULT_REGION'),
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                    'endpoint' => env('AWS_ENDPOINT'),
                    'queue' => env('SQS_QUEUE'),
                    'prefix' => env('SQS_PREFIX'),
                    'warningThreshold' => 100,
                    'failedThreshold' => 500,
                ],
                /**
                 * Inserir as configurações da fila database que devem ser verificadas.
                 */
                'database' => [
                    'queue' => 'default',
                    'warningThreshold' => 10,
                    'failedThreshold' => 55,
                ],
            ],
        ]
    ]
];
