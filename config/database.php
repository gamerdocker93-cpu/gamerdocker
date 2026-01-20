<?php

use Illuminate\Support\Str;

return [

    // Alinhado com DB_CONNECTION=pgsql
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        // Bloco Postgres - O Coração do seu Deploy no Railway
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'postgres.railway.internal'), // Fallback para o host interno
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'railway'), // Fallback para o nome padrão do Railway
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'hXwHyEvmzTPGGvSaDqwZTeEwAgSJzGLT'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // Mantive o MySQL por segurança, mas o Laravel ignorará ele
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'ViperPro'), '_').'_database_'),
        ],
        // ... restante do bloco redis se mantém igual
    ],
];
