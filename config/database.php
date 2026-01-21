    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'mysql.railway.internal'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'railway'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'oWcauyfgQnWcvkmHnVfTGpgofqAOljkn'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],
        // ... pode manter o bloco pgsql padrão abaixo, o Laravel vai ignorá-lo.
    ],

        // Deixamos o pgsql vazio/padrão, pois não será usado
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'database' => 'forge',
            // ... restante padrão
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'ViperPro'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];