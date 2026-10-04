<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locacao extends Model
{
    use HasFactory;

    protected $table = 'locacoes';

    protected $fillable = [
        'cliente_id',
        'carro_id',
        'data_inicio_periodo',
        'data_final_previsto_periodo',
        'data_final_realizado_periodo',
        'valor_diaria',
        'km_inicial',
        'km_final',
    ];

    protected $casts = [
        'data_inicio_periodo' => 'datetime',
        'data_final_previsto_periodo' => 'datetime',
        'data_final_realizado_periodo' => 'datetime',
        'valor_diaria' => 'float',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function carro()
    {
        return $this->belongsTo(Carro::class);
    }
}
