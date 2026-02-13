<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameProvider extends Model
{
    protected $table = 'game_providers';

    protected $fillable = [
        'code',
        'name',
        'enabled',
        'base_url',
        'credentials_json',
        'meta',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'meta' => 'array',
        
        // 🔐 Segurança: Transforma em array e criptografa no banco automaticamente
        'credentials_json' => 'encrypted:array',
    ];

    /**
     * Auxiliar para pegar chaves específicas das credenciais
     */
    public function creds(string $key = null, $default = null)
    {
        $arr = $this->credentials_json ?: [];
        if ($key === null) return $arr;
        return $arr[$key] ?? $default;
    }
}
