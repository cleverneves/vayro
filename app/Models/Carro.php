<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carro extends Model
{
    use HasFactory;

    protected $fillable = ['modelo_id', 'placa', 'disponivel', 'km'];

    protected $casts = [
        'disponivel' => 'boolean',
    ];

    public function modelo()
    {
        return $this->belongsTo(Modelo::class);
    }

    public function locacoes()
    {
        return $this->hasMany(Locacao::class);
    }
}
