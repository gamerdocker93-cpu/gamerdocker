<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinConfigs extends Model
{
    use HasFactory;

    protected $table = 'ggds_spin_config';

    // Se a tabela não tiver created_at/updated_at, troque para false.
    // Mas sua migration tem timestamps(), então mantém true.
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array', // Laravel já vira JSON <-> array
    ];

    protected $fillable = [
        'is_active',
        'config',
    ];

    // Mantém o mesmo "campo virtual" que o frontend deve estar usando
    protected $appends = ['prizesArray'];

    /**
     * Retorna prizes[] dentro do JSON config.
     */
    public function getPrizesArrayAttribute(): array
    {
        $cfg = $this->config ?? [];
        $prizes = $cfg['prizes'] ?? [];
        return is_array($prizes) ? $prizes : [];
    }

    /**
     * Opcional: permite setar $model->prizes = [...] e salvar no config.
     */
    public function setPrizesAttribute($value): void
    {
        $cfg = $this->config ?? [];

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $cfg['prizes'] = is_array($decoded) ? $decoded : [];
        } elseif (is_array($value)) {
            $cfg['prizes'] = $value;
        } else {
            $cfg['prizes'] = [];
        }

        $this->attributes['config'] = json_encode($cfg);
    }
}