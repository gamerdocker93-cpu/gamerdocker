<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
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
     * - Salva criptografado (string) no banco
     * - Expõe como array no PHP
     * - Lê legado: se estiver como JSON puro, também lê
     * - Se receber string já criptografada válida, preserva/normaliza
     */
    protected function credentialsJson(): Attribute
    {
        return Attribute::make(
            get: function ($value): array {
                if ($value === null || $value === '') {
                    return [];
                }

                // 1) formato novo: criptografado
                try {
                    $plain = Crypt::decryptString($value);
                    $decoded = json_decode($plain, true);
                    return is_array($decoded) ? $decoded : [];
                } catch (\Throwable $e) {
                    // 2) legado: JSON puro no banco
                    $decoded = json_decode($value, true);
                    return is_array($decoded) ? $decoded : [];
                }
            },
            set: function ($value): string {
                // Normaliza para array
                $arr = [];

                if ($value === null || $value === '') {
                    $arr = [];
                } elseif (is_array($value)) {
                    $arr = $value;
                } elseif (is_string($value)) {
                    // Se vier JSON string do form
                    $decoded = json_decode($value, true);

                    if (is_array($decoded)) {
                        $arr = $decoded;
                    } else {
                        // Se vier string já criptografada válida (ex: seed/legacy)
                        try {
                            $plain = Crypt::decryptString($value);
                            $decoded2 = json_decode($plain, true);
                            $arr = is_array($decoded2) ? $decoded2 : [];
                        } catch (\Throwable $e) {
                            // string inválida -> evita quebrar e salva vazio
                            $arr = [];
                        }
                    }
                } else {
                    $arr = (array) $value;
                }

                // Garante JSON consistente + criptografa
                $json = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
                return Crypt::encryptString($json);
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