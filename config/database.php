<?php

use Illuminate\Support\Str;

return [

    // O Laravel agora usará o MySQL como conexão principal
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            // BLINDAGEM: Se o .env falhar, ele tenta os dados do seu MySQL Railway
            'host' => env('DB_HOST', 'yamabiko.proxy.rlwy.net'), 
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'railway'), 
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'hXwHyEvmzTPGGvSaDqwZTeEwAgSJzGLT'), // Use a senha do MySQL aqui
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
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