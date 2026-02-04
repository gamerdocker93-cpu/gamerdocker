<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinConfigs extends Model
{
    use HasFactory;

    protected $appends = ['prizesArray'];
    protected $table = 'ggds_spin_config';

    protected $fillable = [
        'prizes'
    ];

    /**
     * @return mixed
     */
    public function getPrizesArrayAttribute(): mixed
    {
        $prizes = $this->attributes['prizes'] ?? '[]';
        return json_decode($prizes ?: '[]', true);
    }
}