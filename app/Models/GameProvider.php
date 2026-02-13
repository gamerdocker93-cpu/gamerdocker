<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class GameProvider extends Model
{
    protected $table = 'game_providers';

    protected $fillable = [
        'code',
        'name',
        'enabled',
        'base_url',
        'credentials_json',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * credentials_json:
     * - Armazena criptografado (string) no banco
     * - Expõe como array no PHP
     * - Compatível com legado: se estiver como JSON puro, também lê.
     */
    protected function credentialsJson(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return [];
                }

                // 1) Tenta decrypt (formato novo)
                try {
                    $plain = Crypt::decryptString($value);
                    $decoded = json_decode($plain, true);
                    return is_array($decoded) ? $decoded : [];
                } catch (\Throwable $e) {
                    // 2) Se não for criptografado, tenta tratar como JSON puro (legado)
                    $decoded = json_decode($value, true);
                    return is_array($decoded) ? $decoded : [];
                }
            },
            set: function ($value) {
                // Normaliza para array
                if ($value === null || $value === '') {
                    $value = [];
                }

                if (is_string($value)) {
                    $maybe = json_decode($value, true);
                    $value = is_array($maybe) ? $maybe : [];
                }

                if (!is_array($value)) {
                    $value = (array) $value;
                }

                // Garante JSON consistente + criptografa
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return Crypt::encryptString($json ?: '[]');
            }
        );
    }

    public function isEnabled(): bool
    {
        return (bool) $this->enabled;
    }

    public function credentials(): array
    {
        return $this->credentials_json ?? [];
    }

    public function credential(string $key, $default = null)
    {
        $creds = $this->credentials();
        return $creds[$key] ?? $default;
    }

    public function setCredential(string $key, $value): self
    {
        $creds = $this->credentials();
        $creds[$key] = $value;
        $this->credentials_json = $creds;
        return $this;
    }

    public function baseUrl(): string
    {
        return rtrim((string) ($this->base_url ?? ''), '/');
    }
}
